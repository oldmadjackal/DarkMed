<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Message_details_simple.php" ;

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
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
  }

       $user_=$db->real_escape_string($user ) ;

//--------------------------- Запрос ключа подписи получателя

                     $sql="Select u.sign_s_key".
			  "  From users u".
			  " Where u.login='$user_'" ;
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

       echo "    receiver_key='".$fields[0]."' ;			\n" ;
       echo "    receiver_key=Crypto_decode(receiver_key, password) ;	\n" ;
  }

     $res->close() ;

//--------------------------- Извлечение данных сообщения

       $message_=$db->real_escape_string($message) ;

                     $sql="Select m.id, m.sender, m.sent, m.text, u.sign_p_key, u.options".
			  "  From messages m, users u".
			  " Where m.receiver='$user_'".
			  "  and  m.id      = $message_".
			  "  and  u.login   = m.sender" ;
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

	   $owner=$fields[1] ;

	if(strpos($fields[5], "Client;")!==false)  $sender_type="Client" ;
	else			                   $sender_type="Doctor" ;

      echo "    message_id          ='".$fields[0]."' ;					\n" ;
      echo "    sender_user         ='".$fields[1]."' ;					\n" ;
      echo "    sender_key          ='".$fields[4]."' ;					\n" ;

      echo "  i_msg_sender.innerHTML='".$owner."' ;					\n" ;
      echo "  i_msg_sent  .innerHTML='".$fields[2]."' ;					\n" ;
      echo "    text                ='".$fields[3]."' ;					\n" ;
      echo "    text                =Sign_decode(text, sender_key, receiver_key) ;	\n" ;
      echo "  i_msg_text  .innerHTML= text ;						\n" ;

//--------------------------- Извлечение реквизитов отправителя сообщения

//- - - - - - - - - - - - - - Отправитель - Пациент
   if($sender_type=="Client") {

		       $sql="Select c.check, c.name_f, c.name_i, c.name_o".
			    "  From client_page_main c".
			    " Where c.owner='$owner'" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных отправителя") ;
                         return ;
    }
    if($res->num_rows==0) {
          FileLog("ERROR", "No such message in DB) : ".$message) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестный отправитель") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo "    c_check ='".$fields[0]."' ;	\n" ;
      echo "    c_name_f='".$fields[1]."' ;	\n" ;
      echo "    c_name_i='".$fields[2]."' ;	\n" ;
      echo "    c_name_o='".$fields[3]."' ;	\n" ;

      echo "    i_who_button.disabled=true ;	\n" ;
   }
//- - - - - - - - - - - - - - Отправитель - Врач
   if($sender_type=="Doctor") {

		       $sql="Select d.name_f, d.name_i, d.name_o".
			    "  From doctor_page_main d".
			    " Where d.owner='$owner'" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных отправителя") ;
                         return ;
    }
    if($res->num_rows==0) {
          FileLog("ERROR", "No such message in DB) : ".$message) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестный отправитель") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo "    c_name_f='".$fields[0]."' ;	\n" ;
      echo "    c_name_i='".$fields[1]."' ;	\n" ;
      echo "    c_name_o='".$fields[2]."' ;	\n" ;
   }
//--------------------------- Извлечение ключа доступа к главной странице клиента

   if($sender_type=="Client") {

                     $sql="Select crypto".
			  "  From access_list".
			  " Where owner='$owner'".
			  "  and  login='$user_'".
			  "  and  page =  0" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения ключа главной страницы") ;
                         return ;
    }

    if($res->num_rows==0) 
    {
		echo "    c_name_key='NONE' ;		\n" ;
    }
    else
    {
		$fields =$res->fetch_row() ;

		echo "    c_name_key='".$fields[0]."' ;	\n" ;
    }

		$res->close() ;
   }
   else 
   {
		echo "    c_name_key='OPEN' ;		\n" ;
   }
//--------------------------- Завершение

        FileLog("", "Message presented successfully") ;

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;		\n" ;
    echo  "i_error.innerHTML  ='Доступ предоставлен!' ;	\n" ;
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
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script src="rsa.js"></script>

<script type="text/javascript">
<!--

    var  i_msg_sender ;
    var  i_msg_sent ;
    var  i_msg_text ;
    var  i_who_button ;
    var  i_error ;
    var  password ;
    var  message_id ;
    var  sender_user ;

  function FirstField() 
  {
    var  receiver_key ;
    var  text ;
    var  details ;
    var  words_1 ;
    var  words_2 ;
    var  c_check ;
    var  c_name_f ;
    var  c_name_i ;
    var  c_name_o ;
    var  c_key ;


       i_msg_sender=document.getElementById("MsgSender") ;
       i_msg_sent  =document.getElementById("MsgSent") ;
       i_msg_text  =document.getElementById("MsgText") ;
       i_who_button=document.getElementById("WhoIsIt") ; 
       i_error     =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

    if(c_name_key=="OPEN")
    {
       i_msg_sender.innerHTML=c_name_f+" "+c_name_i+" "+c_name_o ;
    }  
    else 
    if(c_name_key!="NONE")
    {
       c_name_key=Crypto_decode(c_name_key, password) ;
       c_name_f  =Crypto_decode(c_name_f, c_name_key) ;
       c_name_i  =Crypto_decode(c_name_i, c_name_key) ;
       c_name_o  =Crypto_decode(c_name_o, c_name_key) ;

       i_msg_sender.innerHTML=c_name_f+" "+c_name_i+" "+c_name_o ;
    }

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;
     var  details ;

        error_text="" ;

           details="" ;

    for(var elem in a_pages_keys)
    {
        details=details+elem+" " ;
	details=details+Crypto_encode(a_pages_keys[elem], password) ;
        details=details+" " ;
    }

	  i_details.value=details ;

     if(error_text!="") {
                          i_error.style.color="red" ;
                          i_error.innerHTML  = error_text ;
                              return false ;
                        }

      return true ;         
  } 

  function GoToView()
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

    window.open("doctor_view.php"+"?Session="+v_session+"&Owner="+sender_user) ;
  }

  function GoToChat()
  {
     var  v_session ;

       v_session=TransitContext("restore","session","") ;

	parent.frames['section'].location.assign("messages_chat_lr.php?Session="+v_session+"&Sender="+sender_user) ;
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

  <div class="Error_CT" id="Error"></div>

  <form onsubmit="return SendFields();" method="POST">

  <div class="Normal_CT"><b>Сообщение</b></div>
  <br>
  <div class="Normal_CT">
    <div id="MsgSender"></div>
    <input type="button" value="Кто это?"  id="WhoIsIt" onclick=GoToView()>
    <input type="button" value="Переписка"              onclick=GoToChat()>
  </div>
  <br>
  <div class="Normal_CT" id="MsgSent"> </div>
  <br>         
  <em><div class="Normal_CT" id="MsgText"></div>
  <br>

  </form>

</body>

</html>
