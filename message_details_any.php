<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Message_details_any.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }

//--------------------------- Извлечение параметров

                        $session=$_GET ["Session"] ;
                        $message=$_GET ["Message"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "    Message:".$message) ;

//--------------------------- Подключение БД
     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                    ErrorMsg($error) ;
                         return ;
  }

       $user_=$db->real_escape_string($user ) ;

//--------------------------- Запрос ключа подписи получателя

                     $sql="Select u.sign_s_key".
			  "  From `users` u".
			  " Where u.login  ='$user_'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select USERS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса ключа подписи") ;
                         return ;
  }
  else
  {      
	      $fields=$res->fetch_row() ;

       echo "    receiver_key=\"".$fields[0]."\" ;			\n" ;
       echo "    receiver_key=Crypto_decode(receiver_key, password) ;	\n" ;
  }

     $res->close() ;

//--------------------------- Извлечение данных сообщения

                     $sql="Select m.id, m.sender, m.type, t.name, m.sent, m.text, m.details, u.sign_p_key".
			  "  From `messages` m, `ref_messages_types` t, users u".
			  " Where m.`receiver`='$user_'".
			  "  and  m.`id`      = $message".
			  "  and  u.`login`   = m.`sender`".
			  "  and  t.`code`    = m.`type`".
			  "  and  t.`language`='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
  }
  if($res->num_rows==0) {
          FileLog("ERROR", "No such message in DB) : ".$message) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестное сообщение") ;
                         return ;
  }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

        FileLog("", "Message presented successfully") ;

//--------------------------- Формирование данных страницы

      echo "    sender_key           =\"".$fields[7]."\" ;				\n" ;

      echo "  i_msg_id     .innerHTML=\"".$fields[0]."\" ;				\n" ;
      echo "  i_msg_sender .innerHTML=\"".$fields[1]."\" ;				\n" ;
      echo "  i_msg_type   .innerHTML=\"".$fields[3]." (".$fields[2].")\" ;		\n" ;
      echo "  i_msg_sent   .innerHTML=\"".$fields[4]."\" ;				\n" ;
      echo "    text                 =\"".$fields[5]."\" ;				\n" ;
      echo "    text                 =Sign_decode(text, sender_key, receiver_key) ;	\n" ;
      echo "  i_msg_text   .innerHTML= text ;						\n" ;
      echo "    text                 =\"".$fields[6]."\" ;				\n" ;
      echo "    text                 =Sign_decode(text, sender_key, receiver_key) ;	\n" ;
      echo "  i_msg_details.innerHTML= text ;						\n" ;

      echo "  i_login      .value    =\"".$user."\" ;					\n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color=\"red\" ;      " ;
    echo  "i_error.innerHTML  =\"".$text."\" ;" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    " ;
    echo  "i_error.innerHTML  =\"Данные успешно сохранены!\" ;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Message details any-form</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_msg_id ;
    var  i_msg_sender ;
    var  i_msg_type ;
    var  i_msg_sent ;
    var  i_msg_text ;
    var  i_msg_details ;
    var  i_error ;
    var  password ;

  function FirstField() 
  {
    var  receiver_key ;
    var  text ;

       i_msg_id     =document.getElementById("MsgId") ;
       i_msg_sender =document.getElementById("MsgSender") ;
       i_msg_type   =document.getElementById("MsgType") ;
       i_msg_sent   =document.getElementById("MsgSent") ;
       i_msg_text   =document.getElementById("MsgText") ;
       i_msg_details=document.getElementById("MsgDetails") ;
       i_error      =document.getElementById("Error") ;
       i_login      =document.getElementById("Login") ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

         return true ;
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

  <div class="error" id="Error"></div>

  <table border="0" width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr class="fieldL">
      <td width="20%">
         <dev id="MsgId"> </dev><br>
         <b><dev id="MsgSender"> </dev></b><br>
         <dev id="MsgType"> </dev><br>
         <dev id="MsgSent"> </dev><br>
      </td>
      <td width="2%">
      </td>
      <td width="73%">
         <div id="MsgText"></div>
         <i><p id="MsgDetails"></p></i>
      </td>
    </tr>
    </tbody>
  </table>

<input type="hidden" name="Login"    id="Login"   >

</body>

</html>
