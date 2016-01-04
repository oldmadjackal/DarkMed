<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_Doctors_list.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $sys_doc_count    ;
  global  $sys_doc_owner    ;
  global  $sys_doc_fio      ;
  global  $sys_doc_spec     ;
  global  $sys_doc_remark   ;
  global  $sys_doc_portrait ;
  global  $sys_user_type    ;
  global  $a_specialities   ;

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение параметров

                          $session=$_GET ["Session"] ;
  if(!isset($session ))   $session=$_POST["Session"] ;

                         $filter_s=$_GET ["FilterS"] ;
  if(!isset($filter_s))  $filter_s=$_POST["FilterS"] ;

                         $filter_n=$_GET ["FilterN"] ;
  if(!isset($filter_n))  $filter_n=$_POST["FilterN"] ;

    FileLog("START", "Session:".$session) ;
    FileLog("",      "FilterS:".$filter_s) ;
    FileLog("",      "FilterN:".$filter_n) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                      $options="Anonimous" ;
                         $user="" ;
  }

                                                      $sys_user_type="Client" ;
  if(       $options=="Anonimous"                  )  $sys_user_type="Anonimous" ;
  if(strpos($options, "UserType=Doctor;"  )!==false)  $sys_user_type="Doctor" ;
  if(strpos($options, "UserType=Executor;")!==false)  $sys_user_type="Executor" ;

//--------------------------- Формирование списка специальностей

                     $sql="Select code, name".
			  "  From ref_doctor_specialities".
			  " Where language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_DOCTOR_SPECIALITIES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника специальностей") ;
                         return ;
  }
  else
  {  
       echo "   a_specialities['Dummy']=\"\" ;\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_specialities['".$fields[0]."']='".$fields[1]."' ;\n" ;
               $a_specialities[   $fields[0]   ]=   $fields[1] ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение данных врачей

           $user_=$db->real_escape_string($user) ;

                     $sql ="Select owner, CONCAT_WS(' ', d.name_f, d.name_i, d.name_o), speciality, remark, portrait".
                           "  From doctor_page_main d".
                           " Where d.confirmed='Y'" ;

   if(!isset($filter_s))  $sql.=" and  d.owner in (select distinct m.receiver from messages m where m.sender='$user_')" ;

   if( isset($filter_s)        && 
             $filter_s!="Dummy"  ) 
   {
                     $filter_s_=$db->real_escape_string($filter_s) ;
                          $sql.=" and  d.speciality like '%$filter_s_,%'" ;
   }

   if( isset($filter_n)   && 
             $filter_n!=""  ) 
   {
                     $filter_n_=$db->real_escape_string($filter_n) ;
                          $sql.=" and  upper(CONCAT_WS(' ', d.name_f, d.name_i, d.name_o)) like upper('%$filter_n_%')" ;
   }

     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
  }

          $sys_doc_count=$res->num_rows ;

      echo  "  doctors_cnt=".$res->num_rows." ;	\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

          $sys_doc_owner   [$i]= $fields[0] ;
          $sys_doc_fio     [$i]= $fields[1] ;
          $sys_doc_spec    [$i]= $fields[2] ;
          $sys_doc_remark  [$i]= $fields[3] ;
          $sys_doc_portrait[$i]= $fields[4] ;
     }

     $res->close() ;

//--------------------------- Отображение условий поиска

  if(!isset($filter_s))  $filter_s="" ;
  if(!isset($filter_n))  $filter_n="" ;

        echo     "  FormSpeciality('".$filter_s."') ;				\n" ;
        echo     "  document.getElementById('FilterN').value='".$filter_n."' ;	\n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение дополнительных блоков описания

function ShowDoctors() {

  global  $sys_doc_count    ;
  global  $sys_doc_owner    ;
  global  $sys_doc_fio      ;
  global  $sys_doc_spec     ;
  global  $sys_doc_remark   ;
  global  $sys_doc_portrait ;
  global  $a_specialities   ;


  for($i=0 ; $i<$sys_doc_count ; $i++)
  {
        $row =               $i ;
        $user=$sys_doc_owner[$i] ;
        $text=str_replace("@@", "<br>", $sys_doc_remark[$i]) ;
        $spec=$sys_doc_spec[$i] ;

    foreach($a_specialities as $code => $name)  $spec=str_replace($code, $name, $spec) ;
                                                $spec=substr($spec, 0, strlen($spec)-1) ;
                                                $spec=str_replace(",", ", ", $spec) ;

       echo  "  <tr class='table'  id='Row_".$row."' onclick=SelectDoctor('".$row."')>		\n" ;
       echo  "    <td class='table' width='20%'>						\n" ;

    if($sys_doc_portrait[$i]!="") 
    {
       echo "<div class='fieldC'>								\n" ;
       echo "<img src='".$sys_doc_portrait[$i]."' width=200>					\n" ;
       echo "</div>										\n" ;
       echo "<br>										\n" ;
    }
       echo  "      <input type='hidden' id='Login_"  .$row."' value='".$user."'>		\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='table'>								\n" ;
       echo  "      <div><b>".$sys_doc_fio[$i]."</b></div>					\n" ;
       echo  "      <div>".$spec."</div>							\n" ;
       echo  "      <div><i>".$text."</i></div>							\n" ;
       echo  "    </td>										\n" ;

       echo  "  </tr>										\n" ;

  }

}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;\n" ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;			\n" ;
    echo  "i_error.innerHTML  ='Данные успешно сохранены!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Mobile Doctors List</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_error ;
    var  a_specialities ;
    var  doctors_cnt ;
    var  doctor_id ;

  function FirstField() 
  {

	i_error=document.getElementById("Error") ;

	a_specialities=new Array() ; 

<?php
            ProcessDB() ;
?>

     parent.frames['details'].location.replace('mob_doctors_list_footer.php') ;

         return true ;
  }

  function SendFields() 
  {
     var  i_filterN ;
     var  i_filterS ;
     var  error_text ;


	error_text=""

	i_filterN=document.getElementById("FilterN") ;
	i_filterS=document.getElementById("FilterS") ;

     if(i_filterN.value==""     &&
        i_filterS.value=="Dummy"  )  error_text="Задайте хотя бы одно условие поиска" ;

	i_error.style.color="red" ;
	i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;
  } 

  function SelectDoctor(p_row) 
  {
     for(i=0 ; i<doctors_cnt ; i++)
       if(i!=p_row)  document.getElementById("Row_"+i).hidden=true ;
       else          doctor_id=document.getElementById("Login_"+i).value ;

	document.getElementById("Search" ).hidden=true ;     
	document.getElementById("Actions").hidden=false ;     
  }

  function ResetDoctors() 
  {
     for(i=0 ; i<doctors_cnt ; i++)
         document.getElementById("Row_"+i).hidden=false ;

	document.getElementById("Search" ).hidden=false ;     
	document.getElementById("Actions").hidden=true ;     
  }

  function GoToView()
  {
    window.open("doctor_view.php"+"?Owner="+doctor_id) ;
  } 

  function GoToMail(p_user)
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["section"].location.assign("mob_chat.php?Session="+v_session+"&Sender="+doctor_id) ;
  } 

  function GoToAccess(p_user)
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["section"].location.assign("mob_doctor_access.php?Session="+v_session+"&Doctor="+doctor_id) ;
  } 

  function FormSpeciality(p_selected)
  {
     var  i_specialities ;
     var  i_select_new ;
     var  selected ;

       i_specialities       = document.getElementById("Speciality") ;
       i_select_new         = document.createElement("select") ;
       i_select_new.id      ="FilterS" ;
       i_select_new.name    ="FilterS" ;

    for(var elem in a_specialities)
    {
                             selected=false ;
       if(p_selected==elem)  selected=true ;

                            i_select_new.length++ ;
       i_select_new.options[i_select_new.length-1].text    =a_specialities[elem] ;
       i_select_new.options[i_select_new.length-1].value   =               elem ;
       i_select_new.options[i_select_new.length-1].selected=           selected ;
    }

       i_specialities.appendChild(i_select_new) ;	

    return ;         
  } 

<?php
  require("common.inc") ;
?>


//-->
</script>

</head>

<body onload="FirstField();">

<noscript>
</noscript>

<div class="inputF">

  <table width="90%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td width="10%"> 
        <input type="button" value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" value="!" hidden onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b>ВРАЧИ И СПЕЦИАЛИСТЫ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <p class="error" id="Error"></p>

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form">

  <table width="100%"  id="Search">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field">Специальность: </td>
      <td><span class="fieldL" id="Speciality"></span></td>
    </tr>
    <tr>
      <td class="field">ФИО: </td>
      <td> <input type="text" size=20 name="FilterN" id="FilterN"> </td>
    </tr>
    <tr>
      <td></td>
      <td> 
        <input type="submit" value="Искать">
      </td>
    </tr>
    </tbody>
  </table>

  <br>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Doctors">

<?php
            ShowDoctors() ;
?>

    </tbody>
  </table>

  <table width="100%" hidden id="Actions">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="fieldC"> <br> <input type="button" value="Переписка" onclick=GoToMail()></td>
    <tr>
    </tr>
      <td class="fieldC"> <br> <input type="button" value="Детали" onclick=GoToView()> </td>
    <tr>
    </tr>
      <td class="fieldC"> <br> <input type="button" class="G_bttn" value="Приглашение" onclick=GoToAccess()> </td>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="button" class="R_bttn" value="Выбрать другого специалиста" onclick=ResetDoctors()> </td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
