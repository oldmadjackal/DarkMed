<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_invite_page.php" ;

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

                             $page=$_GET ["Page"] ;
  if(!isset($page))          $page=$_POST["Page"] ;

                         $receiver=$_POST["Receiver"] ;
  if(isset($receiver))
  {
                           $letter=$_POST["Letter"] ;
                           $invite=$_POST["Invite"] ;
                           $incopy=$_POST["InCopy"] ;
  }

           FileLog("START", "  Session:".$session) ;
           FileLog("",      "     Page:".$page) ;

    if(isset($receiver))
    {
           FileLog("",      " Receiver:".$receiver) ;
           FileLog("",      "   Letter:".$letter) ;
           FileLog("",      "   Invite:".$invite) ;
           FileLog("",      "   InCopy:".$incopy) ;
    }
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
  if(       $options=="Anonimous"                )  $sys_user_type="Anonimous" ;
  if(strpos($options, "UserType=Doctor;")!==false)  $sys_user_type="Doctor" ;

//--------------------------- Сохранение данных о сообщении

  if(isset($receiver))
  {
          $receiver=$db->real_escape_string($receiver) ;
          $letter  =$db->real_escape_string($letter) ;
          $invite  =$db->real_escape_string($invite) ;
          $incopy  =$db->real_escape_string($incopy) ;

                       $sql="Insert into messages(Receiver,Sender,Type,Text,Details,Copy)".
                            " values('$receiver','$user','CLIENT_ACCESS_INVITE','$invite','$letter','$incopy')" ;
//       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 3") ;
//                         return ;
    }

          $db->commit() ;

        FileLog("", "Access message saved successfully") ;

          echo  "  location.assign('mob_client_page.php?Session=".$session."&Page=".$page."') ;	\n" ;

     return ;
  }
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

               $a_specialities[   $fields[0]   ]=   $fields[1] ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение данных врачей

           $user_=$db->real_escape_string($user) ;

                     $sql ="Select owner, CONCAT_WS(' ', d.name_f, d.name_i, d.name_o), speciality, remark, portrait".
                           "  From doctor_page_main d".
                           " Where d.confirmed='Y'" ;
//                   $sql.="  and  d.owner in (select distinct m.receiver from messages m where m.sender='$user_')" ;
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

//--------------------------- Отображение

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

       echo  "  <tr class='table' id='Row_".$row."' onclick=SelectDoctor('".$row."')>		\n" ;
       echo  "    <td class='table' width='10%'>						\n" ;

    if($sys_doc_portrait[$i]!="") 
    {
       echo "<div class='fieldC'>								\n" ;
       echo "<img src='".$sys_doc_portrait[$i]."' height=200>					\n" ;
       echo "</div>										\n" ;
    }
       echo  "      <input type='hidden' id='Login_"  .$row."' value='".$user."'>		\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='table'>								\n" ;
       echo  "      <div><b>".$sys_doc_fio[$i]."</b></div>					\n" ;
       echo  "      <div>".$spec."</div>							\n" ;
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
  @import url("mob_common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_error ;
    var  doctors_cnt ;

  function FirstField() 
  {

	i_error=document.getElementById("Error") ;

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

  function SelectDoctor(p_row) 
  {
     for(i=0 ; i<doctors_cnt ; i++)
       if(i!=p_row)  document.getElementById("Row_"+i).hidden=true ;

	document.getElementById("DoctorsText").hidden=true ;
	document.getElementById("Invitation" ).hidden=false ;     
  }

  function ResetDoctors() 
  {
     for(i=0 ; i<doctors_cnt ; i++)
         document.getElementById("Row_"+i).hidden=false ;

	document.getElementById("DoctorsText").hidden=false ;
	document.getElementById("Invitation" ).hidden=true ;     
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
        <b>ПРИГЛАШЕНИЕ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <p class="error" id="Error"></p>

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form">

  <div id="DoctorsText">Выбирете врача:</div>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Doctors">

<?php
            ShowDoctors() ;
?>

    </tbody>
  </table>

  <table width="100%" hidden id="Invitation">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="fieldC"> <br> <input type="button" value="Выбрать другого специалиста" onclick=ResetDoctors()> </td>
    </tr>
    <tr>
      <td class="fieldC">
        <br>
        Сопроводительный техт 
        <br>
        <textarea cols=32 rows=7 wrap="soft" name="Invite" id="Invite"></textarea>
      </td>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="submit" class="G_bttn" value="Направить приглашение"> </td>
    </tr>
    </tbody>
  </table>

	<input type="hidden" name="Receiver" id="Receiver" >
	<input type="hidden" name="Letter"   id="Letter" >
	<input type="hidden" name="InCopy"   id="InCopy" >

  </form>

</div>

</body>

</html>
