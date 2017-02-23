<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctors_list.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $glb_options_a    ;

  global  $sys_doc_count    ;
  global  $sys_doc_owner    ;
  global  $sys_doc_fio      ;
  global  $sys_doc_spec     ;
  global  $sys_doc_remark   ;
  global  $sys_doc_portrait ;
  global  $sys_doc_contact  ;
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
  if(!isset($session        ))   $session=$_POST["Session"] ;

  if( isset($_POST["FilterS"]))  $filter_s=$_POST["FilterS"] ;
  if( isset($_GET ["FilterS"]))  $filter_s=$_GET ["FilterS"] ;
  if(!isset($filter_s        ))  $filter_s="" ;

  if( isset($_POST["FilterN"]))  $filter_n=$_POST["FilterN"] ;
  if( isset($_GET ["FilterN"]))  $filter_n=$_GET ["FilterN"] ;
  if(!isset($filter_n        ))  $filter_n="" ;

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
                      $glb_options_a["user"]="Anonimous" ;
                                        $user ="" ;
  }

     $sys_user_type=$glb_options_a["user"] ;

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

           $sql ="Select distinct owner, CONCAT_WS(' ', d.name_f, d.name_i, d.name_o), speciality, remark, portrait, m.done".
                  " From doctor_page_main d inner join users    u on d.owner=u.login".
                  "                    left outer join messages m on m.receiver=u.login and m.sender='$user_' and type='CLIENT_ACCESS_INVITE'".
                  " Where d.confirmed='Y'" ;

   if(!isset($glb_options_a["tester"]))  $sql.=" and  u.options not like '%Tester;%'";

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

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

          $sys_doc_owner   [$i]= $fields[0] ;
          $sys_doc_fio     [$i]= $fields[1] ;
          $sys_doc_spec    [$i]= $fields[2] ;
          $sys_doc_remark  [$i]= $fields[3] ;
          $sys_doc_portrait[$i]= $fields[4] ;
          $sys_doc_contact [$i]= $fields[5] ;
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
  global  $sys_doc_contact ;
  global  $a_specialities   ;
  global  $sys_user_type    ;


  for($i=0 ; $i<$sys_doc_count ; $i++)
  {
        $row =               $i ;
        $user=$sys_doc_owner[$i] ;
        $text=str_replace("@@", "<br>", $sys_doc_remark[$i]) ;
        $spec=$sys_doc_spec[$i] ;

    foreach($a_specialities as $code => $name)  $spec=str_replace($code, $name, $spec) ;
                                                $spec=substr($spec, 0, strlen($spec)-1) ;
                                                $spec=str_replace(",", ", ", $spec) ;

       echo  "  <tr class='Table_LT' id='Row_".$row."'>						\n" ;
       echo  "    <td class='Table_LT' width='10%'>						\n" ;

    if($sys_doc_portrait[$i]!="") 
    {
       echo "<div class='Normal_CT'>								\n" ;
       echo "<img src='".$sys_doc_portrait[$i]."' height=100>					\n" ;
       echo "</div>										\n" ;
       echo "<br>										\n" ;
    }
       echo  "      <input type='hidden' id='Login_"  .$row."' value='".$user."'>		\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='Table_LT'>								\n" ;
       echo  "      <div><b>".$sys_doc_fio[$i]."</b></div>					\n" ;

    if($sys_doc_contact[$i]=="R")
       echo  "      <div><font color='red'>Приглашение не принято</font></div>			\n" ;
    if($sys_doc_contact[$i]=="Y")
       echo  "      <div><font color='green'>Приглашение принято</font></div>			\n" ;
    if($sys_doc_contact[$i]=="N")
       echo  "      <div><font color='blue'>Приглашение направлено</font></div>			\n" ;
    
       echo  "      <div>".$spec."</div>							\n" ;
       echo  "      <div><i>".$text."</i></div>							\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='Table_CT' width='10%'>						\n" ;

    if($sys_user_type=="Client"  ||
       $sys_user_type=="Doctor"  ||
       $sys_user_type=="Executor"  )
    {
       echo  "      <input type='button' value='Переписка' onclick=GoToMail('".$user."')>	\n" ;
       echo  "      <br>									\n" ;
       echo  "      <br>									\n" ;
    }
       echo  "      <input type='button' value='Детально'  onclick=GoToView('".$user."')>	\n" ;

    if($sys_user_type=="Client")
    {
       echo  "      <br>									\n" ;
       echo  "      <br>									\n" ;

      if($sys_doc_contact[$i]=="")
       echo  "      <input type='button' value='Приглашение'  onclick=GoToAccess('".$user."')>	\n" ;

      if($sys_doc_contact[$i]=="N" ||
         $sys_doc_contact[$i]=="Y"   )
       echo  "      <input type='button' value='Передать данные'  onclick=GoToAccess('".$user."')>	\n" ;
    }
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

<title>DarkMed Doctors List</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>

<script type="text/javascript">
<!--

    var  i_error ;
    var  a_specialities ;

  function FirstField() 
  {

	i_error=document.getElementById("Error") ;

	a_specialities=new Array() ; 

<?php
            ProcessDB() ;
?>

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

  function GoToView(p_user)
  {
    window.open("doctor_view.php"+"?Owner="+p_user) ;
  } 

  function GoToMail(p_user)
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	location.assign("messages_chat_lr_wrapper.php?Session="+v_session+"&Sender="+p_user) ;
  } 

  function GoToAccess(p_user)
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	location.assign("doctor_access.php?Session="+v_session+"&Doctor="+p_user) ;
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

  <table width="90%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td width="10%"> 
        <input type="button" class="HelpButton"     value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" class="CallBackButton" value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="FormTitle"> 
        <b>ВРАЧИ И СПЕЦИАЛИСТЫ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <p class="Error_CT" id="Error"></p>

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form">

  <table width="100%">
    <tbody>
    <tr>
      <td class="Normal_RT">Специальность: </td>
      <td><span class="Normal_LT " id="Speciality"></span></td>
    </tr>
    <tr>
      <td class="Normal_RT">ФИО: </td>
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
    <tbody  id="Doctors">

<?php
            ShowDoctors() ;
?>

    </tbody>
  </table>

  </form>

</body>

</html>
