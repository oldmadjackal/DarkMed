<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Registry.php" ;
   
   require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function RegistryDB(){

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение и анализ параметров

   $login   =$_POST["Login"   ] ;

  if(isset($login)) {
   $password=$_POST["Password"] ;
   $email   =$_POST["Email"] ;
   $type_a  =$_POST["Type"] ;
   $s_key   =$_POST["Sign_secret"] ;
   $p_key   =$_POST["Sign_public"] ;
   $msg_key =$_POST["Msg_key"] ;
   $crypto  =$_POST["Crypto"] ;
   $check   =$_POST["Check"] ;

                              $type="" ;
     foreach($type_a as $tmp) $type=$type.$tmp."," ;
  }

     $completeness=0 ;
   
  if(isset($login   ))  $completeness++ ;
  if(isset($password))  $completeness++ ;
  if(isset($email   ))  $completeness++ ;
  if(isset($type    ))  $completeness++ ;

  if($completeness==0)  return ;


  if(!isset($check)             || 
     substr($check,1,5)=="Check"  ) 
  {
          FileLog("ERROR", "Crypto check value is invalid or missed") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка генерации шифро-контроля") ;
                         return ;
  }

  FileLog("START", "Login:".$login." Password:".$password." Email:".$email." Type:".$type) ;

                                        echo  "   i_login.value=\"" .$login   ."\" ;\n" ;
                                        echo  "i_password.value=\"" .$password."\" ;\n" ;
                                        echo  "   i_email.value=\"" .$email   ."\" ;\n" ;

  if(strpos($type, "Client"  )!==false)  echo  "i_type_cln.checked=true ;\n" ;
  if(strpos($type, "Doctor"  )!==false)  echo  "i_type_dct.checked=true ;\n" ;
  if(strpos($type, "Executor")!==false)  echo  "i_type_exe.checked=true ;\n" ;

  if($completeness<4) {
                        FileLog("CANCEL", "NonComplete data") ;
                         echo  "SendFields() ;" ;
                               return ;
  }
//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Приведение параметров

   $login   =$db->real_escape_string($login   ) ;
   $password=$db->real_escape_string($password) ;
   $email   =$db->real_escape_string($email   ) ;
   $s_key   =$db->real_escape_string($s_key   ) ;
   $p_key   =$db->real_escape_string($p_key   ) ;
   $msg_key =$db->real_escape_string($msg_key ) ;
   $crypto  =$db->real_escape_string($crypto  ) ;
   $check   =$db->real_escape_string($check   ) ;

//--------------------------- Проверка повторной регистрации

     $res=$db->query("Select * from `users` Where `Login`='$login'") ;
  if($res===false) {
          FileLog("ERROR", "Select... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка проверки уникальности логина") ;
                         return ;
  }
  if($res->num_rows!=0) {
			   $res->free() ;
                            $db->close() ;
          FileLog("CANCEL", "Login duplicate detected") ;
         ErrorMsg("Такой логин уже использован другим пользователем") ;
                         return ;
  }
 //------------------------- Проверка корректности ввода Email   
 // if (!preg_match( '/^[A-Za-z0-9!#$%&\'*+-/=?^_`{|}~]+@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)+[A-Za-z]$/', $email)) { 
     if(!preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $email)){
         FileLog("CANCEL", "Incorrect email") ;
         ErrorMsg("Проверьте правильность написания Email") ;
                         return ;
  }  
//--------------------------- Создание учетной записи пользователя
        
                                              $options="" ;

       if(strpos($type, "Doctor"  )!==false)  $options.="Doctor;" ;
  else if(strpos($type, "Executor")!==false)  $options.="Executor;" ;
  else                                        $options.="Client;" ;
  
       if(strpos($login, "test"   )!==false)  $options.="Tester;" ;

              $options_a=OptionsToArray($options) ;
       $code_confirm=GetRandomString(40);
       $email_confirm="N";
     $res=$db->query("Insert into `users`(Login, Password, Email, Sign_p_key, Sign_s_key, Msg_key, Options,Email_Confirm,Code_Confirm)".
                      " values('$login','$password','$email','$p_key','$s_key','$msg_key','$options','$email_confirm','$code_confirm')") ;
  if($res===false) {
             FileLog("ERROR", "Insert USERS... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 1") ;
                         return ;
  }
//--------------------------- Создание главной записи доступа к корневой странице

     $res=$db->query("Insert into `access_list`(`Owner`,`Login`,`Page`,`Crypto`) values('$login','$login','0','$crypto')") ;
  if($res===false) {
             FileLog("ERROR", "Insert ACCESS_LIST... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 2") ;
                         return ;
  }
//--------------------------- Для пользователя типа CLIENT

if($options_a["user"]=="Client")
{
//- - - - - - - - - - - - - - Создание главной страницы
        $res=$db->query("Insert into `client_page_main`(`Owner`,`Check`) values('$login','$check')") ;
     if($res===false) {
             FileLog("ERROR", "Insert CLIENT_CARD_MAIN... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 3") ;
                         return ;
     }
}
//--------------------------- Для пользователя типа DOCTOR, EXECUTOR

if($options_a["user"]=="Doctor"  ||
   $options_a["user"]=="Executor"  )
{
//- - - - - - - - - - - - - - Создание главной страницы
     $res=$db->query("Insert into `doctor_page_main`(`Owner`) values('$login')") ;
  if($res===false) {
             FileLog("ERROR", "Insert DOCTOR_CARD_MAIN... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 4") ;
                         return ;
  }
}
//--------------------------- Сброс контекста авторизации

   echo  "parent.frames['menu'].ShowAnonimous() ;	" ;
   echo  "TransitContext('save', 'password', '') ;	" ;
   echo  "TransitContext('save', 'session', '') ;	" ;

//--------------------------- Отправка запроса подтверждения email

	Email_confirmation($db, $login,$code_confirm ,$error);

//--------------------------- Завершение
	
     $db->commit();
     $db->close() ;

        FileLog("", "User record successfully inserted)") ;

     SuccessMsg() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color=\"red\" ;      " ;
    echo  "i_error.innerHTML  =\"".$text."\" ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                       " ;
    echo  "i_error.innerHTML  =\"Вы успешно зарегистрированы!<br> Вам на почту отправлен запрос на подтверждение E-mail. Ссылка действительна 3 дня!\" ;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Registration</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;    
    var  i_login ;
    var  i_password ;
    var  i_email ;
    var  i_type_cln ;
    var  i_type_dct ;
    var  i_type_exe ;
    var  i_crypto ;
    var  i_check ;
    var  i_s_key ;
    var  i_p_key ;
    var  i_msg_key ;
    var  i_error ;    

  function FirstField() 
  {
       i_table   =document.getElementById("Fields") ;
       i_login   =document.getElementById("Login") ;
       i_password=document.getElementById("Password") ;
       i_email   =document.getElementById("Email") ;
       i_type_cln=document.getElementById("TypeClient") ;
       i_type_dct=document.getElementById("TypeDoctor") ;
       i_type_exe=document.getElementById("TypeExecutor") ;
       i_crypto  =document.getElementById("Crypto") ;
       i_check   =document.getElementById("Check") ;
       i_s_key   =document.getElementById("Sign_secret") ;
       i_p_key   =document.getElementById("Sign_public") ;
       i_msg_key =document.getElementById("Msg_key") ;
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
     var  password ;
     var  crypto ;
     var  msg_key ;
     var  check ;
     var  keys_pair ;
     var  error_text ;
     var  text ;

       i_table.rows[0].cells[0].style.color="black"   ;
       i_table.rows[1].cells[0].style.color="black"   ;
       i_table.rows[2].cells[0].style.color="black"   ;

        error_text="" ;
     
     if(i_login.value=="") {
       i_table.rows[0].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Логин'" ;
     }

     if(i_password.value=="") {
       i_table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Пароль'" ;
     }

     if(i_email.value=="") {
       i_table.rows[2].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Контактный e-mail'" ;
     }

     if(i_type_cln.checked==false &&
        i_type_dct.checked==false &&
        i_type_exe.checked==false   ) {
       i_table.rows[4].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Должена быть выбрана категория пользователя" ;
     } 

	       TransitContext("save", "password", i_password.value) ;

     if(error_text=="") {

          password      =    i_password.value
	i_password.value=MD5(i_password.value) ;
	i_password.value=    i_password.value.substr(1,4) ;

         crypto      =GetRandomString(64) ;
//       crypto      ="ClientKey_"+i_login.value+GetRandomString(32) ;  // DEBUG only
         check       =Check_generate() ;
       i_crypto.value=Crypto_encode(crypto, password) ;
       i_check .value=Crypto_encode(check,  crypto  ) ;

       if(               i_crypto.value  == "" ||
                         i_check .value  == "" ||
          Check_validate(i_check .value)===true  )
       {
              error_text=error_text+"<br>Ошибка крипто-системы. Попробуйте перезагрузить страницу." ;
       }

         msg_key      =GetRandomString(64) ;
//       msg_key      ="MsgKey_"+i_login.value+GetRandomString(32) ;  // DEBUG only
       i_msg_key.value=Crypto_encode(msg_key, password) ;
     }

     if(error_text=="") {

	keys_pair    =Sign_generate() ;
	i_s_key.value=Crypto_encode(keys_pair[0], password) ;
	i_p_key.value=              keys_pair[1] ;	
     }

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                   return true ;         
  } 


<?php
  require("common.inc") ;
  require("md5.inc") ;
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
        <input type="button" value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b>РЕГИСТРАЦИЯ</b>
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
      <td> <input type="text" size=20 name="Login" id="Login"> </td>
    </tr>
    <tr>
      <td class="field"> Пароль </td>
      <td> <input type="text" size=20 name="Password" id="Password"> </td>
    </tr>
    <tr>
      <td class="field"> Контактный e-mail </td>
      <td> <input type="text" size=60 name="Email" id="Email"> </td>
    </tr>
    <tr>
      <td class="field"> <p> </p> </td>
    </tr>
    <tr>
      <td class="field"> Тип учетной записи</td>
      <td>
        <div> <input type="radio" name="Type[]" value="Client"   id="TypeClient"  >Пациент   </div>
        <div> <input type="radio" name="Type[]" value="Doctor"   id="TypeDoctor"  >Врач      </div>
        <div> <input type="radio" name="Type[]" value="Executor" id="TypeExecutor">Специалист: тренер, массажист, медсестра </div>
      </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Зарегистрироваться"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <input type="hidden" name="Crypto" id="Crypto"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <input type="hidden" name="Check" id="Check"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td>
        <input type="hidden" name="Sign_secret" id="Sign_secret"> 
        <input type="hidden" name="Sign_public" id="Sign_public">
        <input type="hidden" name="Msg_key"     id="Msg_key"    >
      </td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
