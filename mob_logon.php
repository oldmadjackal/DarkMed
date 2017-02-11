<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_Logon.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных данных в БД

function RegistryDB() {

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение и анализ параметров

  if(isset($_POST["Login"   ]))  $login   =$_POST["Login"   ] ;
  if(isset($_POST["Password"]))  $password=$_POST["Password"] ;

     $completeness=0 ;

  if(isset($login   ))  $completeness++ ;
  if(isset($password))  $completeness++ ;

  if($completeness==0)  return ;

  if($completeness==0)  FileLog("START", "HandShake") ;
  else                  FileLog("START", "Login:".$login." Password:".$password) ;

//--------------------------- Вывод данных на экран

    echo     "   i_login.value='" .$login   ."' ;	\n" ;
    echo     "i_password.value='" .$password."' ;	\n" ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Верификация пользователя

   $login   =$db->real_escape_string($login   ) ;
   $password=$db->real_escape_string($password) ;

                     $sql="Select options, Email_confirm, email, Code_confirm  from users Where Login='$login' and Password='$password'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select... : ".$db->error) ;
                     $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка проверки пользователя") ;
                         return ;
  }
  if($res->num_rows==0) {
                    $res->free() ;
                     $db->close() ;
          FileLog("CANCEL", "Login or password failed") ;
         ErrorMsg("Несоответствие логина и пароля пользователя") ;
                         return ;
  }
              $fields=$res->fetch_row() ;

             $options=$fields[0] ;

  if($fields[1]=="N") {

    Email_confirmation($db, $login, $fields[3] ,$error);
                         $db->close() ;
               FileLog("CANCEL", "Неподтвержденный E-Mail") ;
              ErrorMsg("Ваш E-mail не подтвержден.<br>Если Вы не получили ссылку на Ваш E-mail:".$fields[2].
                       ", для отправки повторного подтверждения перейдите по ссылке ". 
                       "<a href=\"http://".$_SERVER["HTTP_HOST"]."/regisry_ack.php?confirm_key=Repeat\">сюда</a>" ) ;
                         return ; 
  }

                    $res->free() ;

//--------------------------- Выделение аттрибутов пользователя

    $options_a=OptionsToArray($options) ;

      $user_type=$options_a["user"] ;

//--------------------------- Регистрация сессии

           $session=GetRandomString(16) ;

                     $sql="Insert into sessions(Login, Session) values('$login','$session')" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Insert SESSION... : ".$db->error) ;
                     $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
  }

     $db->commit() ;

        FileLog("", "Session record successfully inserted") ;

//--------------------------- Удаление старых сессий

                     $sql="Delete from sessions where started<Date_Sub(Now(), interval 24 hour)" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Insert DELETE... : ".$db->error) ;
          InfoMsg("Ошибка на сервере. <br>Детали: ошибка очистки таблицы сессий") ;
  }


//--------------------------- Изменение конфигурации главного меню

                             echo  "TransitContext('save', 'user', '".$options."') ;	\n" ;       

   if($user_type=="Doctor")  echo  "location.assign('mob_menu_doctor.php') ;	\n" ;       
   else                      echo  "location.assign('mob_menu_client.php') ;	\n" ;

//--------------------------- Завершение

     $db->commit() ;
     $db->close() ;

     SuccessMsg($session) ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg($session) {

    echo  "TransitContext('save', 'session', '".$session."') ;	\n" ;

    echo  "i_error.style.color='green' ;				\n" ;
    echo  "i_error.innerHTML  ='Авторизация успешно пройдена!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed-Mobile Logon</title>
<meta http-equiv="Content-Type" content="text/html" charset="windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
  require("md5.inc") ;
?>

    var  i_table ;
    var  i_login ;
    var  i_password ;
    var  i_error ;    

  function FirstField() 
  {
       i_table   =document.getElementById("Fields") ;
       i_login   =document.getElementById("Login") ;
       i_password=document.getElementById("Password") ;
       i_error   =document.getElementById("Error") ;

       i_login.focus() ;

<?php
            RegistryDB() ;
?>

       i_password.value=TransitContext("restore", "password", "") ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

       i_table.rows[0].cells[0].style.color="black"   ;
       i_table.rows[1].cells[0].style.color="black"   ;

        error_text="" ;
     
     if(i_login.value=="") {
       i_table.rows[0].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Логин'" ;
     }

     if(i_password.value=="") {
       i_table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Пароль'" ;
     }

     if(error_text=="") {

       TransitContext("save", "password", i_password.value) ;

	i_password.value=MD5(i_password.value) ;
	i_password.value=i_password.value.substr(1,4) ;
     }

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                   return true ;         
  } 

//-->
</script>

</head>

<body onload="FirstField();">

<noscript>
</noscript>

<dev class="inputF">

  <table width="90%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td width="10%"> 
        <input type="button" value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" value="!" onclick=GoToCallBack() hidden id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b>АВТОРИЗАЦИЯ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> Логин </td>
      <td> <input type="text" size=15 maxlength=30 name="Login" id="Login"> </td>
    </tr>
    <tr>
      <td class="field"> Пароль </td>
      <td> <input type="password" size=15 maxlength=50 name="Password" id="Password"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Войти"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
