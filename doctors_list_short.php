<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script ="Doctors_list.php" ;
   $glb_log_off= true ;

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
  if(!isset($session ))  $session=$_POST["Session"] ;

    FileLog("START", "Session:".$session) ;

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
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;
              
           $a_specialities[$fields[0]]=$fields[1] ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение данных врачей

           $user_=$db->real_escape_string($user) ;

           $sql ="Select owner, CONCAT_WS(' ', d.name_f, d.name_i, d.name_o), speciality, remark, portrait, u.sign_p_key".
                  " From doctor_page_main d, users u".
                  " Where d.confirmed='Y'".
                  "  and  d.owner    =u.login".
                  "  and  d.owner in (select distinct m.receiver from messages m where m.sender='$user_' and type='CLIENT_ACCESS_INVITE' and done!='R')" ;

   if(!isset($glb_options_a["tester"]))  $sql.=" and  u.options not like '%Tester;%'";

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

            echo  "a_name['".$fields[0]."']='".$fields[1]."' ;   \n" ;
            echo  "a_sign['".$fields[0]."']='".$fields[5]."' ;   \n" ;

     }

     $res->close() ;

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

       echo  "  <tr class='Table_LT' id='Row_".$row."' onclick=SelectRow(".$row.",'".$user."') ; >	\n" ;
       echo  "    <td class='Table_LT' width='10%'>							\n" ;

    if($sys_doc_portrait[$i]!="") 
    {
       echo "<div class='Normal_CT'>							\n" ;
       echo "<img src='".$sys_doc_portrait[$i]."' height=100>				\n" ;
       echo "</div>									\n" ;
    }
       echo  "      <input type='hidden' id='Login_"  .$row."' value='".$user."'>	\n" ;
       echo  "    </td>									\n" ;
       echo  "    <td class='Table_LT'>							\n" ;
       echo  "      <div><b>".$sys_doc_fio[$i]."</b></div>				\n" ;
       echo  "      <div>".$spec."</div>						\n" ;
//     echo  "      <div><i>".$text."</i></div>						\n" ;
       echo  "    </td>									\n" ;

       echo  "  </tr>									\n" ;

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
  @import url("buttons.css") ;
  @import url("tables.css") ;
  @import url("text.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_error ;
    var  a_name ;
    var  a_sign ;

  function FirstField() 
  {

	i_error=document.getElementById("Error") ;

	a_sign=new Array() ; 
	a_name=new Array() ; 

<?php
            ProcessDB() ;
?>

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


	error_text=""

	i_error.style.color="red" ;
	i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;
  } 

  var  selected ;
  var  doctor ;
  
  function SelectRow(p_row, p_doctor)
  {
     var  cols ;

  
    if(selected!=null)
    {
                     cols=selected.getElementsByTagName('td') ;
      for(var i=0; i<cols.length; i++)  cols[i].className="Table_LT" ;
    }

                selected=document.getElementById("Row_"+p_row) ;
                    cols=selected.getElementsByTagName('td') ;
     for(var i=0; i<cols.length; i++)  cols[i].className="TableSelected_LT" ;

                 doctor=p_doctor ;

	  parent.frames["section"].EI_DoctorSelected(p_doctor) ;
  }

  function GoToView(p_user)
  {
    window.open("doctor_view.php"+"?Owner="+p_user) ;
  } 

  function EI_GetSelectedDoctor() 
  {
            doctor_pars=new Object ;
            doctor_pars.user=doctor ;
            doctor_pars.name=a_name[doctor] ;
            doctor_pars.pkey=a_sign[doctor] ;
     return(doctor_pars) ;
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

  <p class="Error_CT" id="Error"></p>

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form">

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
