<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Message_details_chat.php" ;

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

                        $session =$_GET ["Session"] ;
                        $receiver=$_GET ["Receiver"] ;
                        $topread =$_GET ["TopRead"] ;
                        $text    =$_POST["Text"] ;
                        $copy    =$_POST["Copy"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "   Receiver:".$receiver) ;
  FileLog("",      "    TopRead:".$topread) ;
  FileLog("",      "       Text:".$text) ;
  FileLog("",      "       Copy:".$copy) ;

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

//--------------------------- Извлечение типа, ключей подписи получателя и отправителя

       $receiver_=$db->real_escape_string($receiver) ;

                       $sql="Select login, sign_s_key, sign_p_key, msg_key, options".
                            "  From users".
                            " Where login='$user_' ".
                            "   or  login='$receiver_' " ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select USERS...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения ключей подписи") ;
                         return ;
    }

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

        if($fields[0]==$user) {
				 echo "snd_s_key='" .$fields[1]."' ;	\n" ;
				 echo "  msg_key='" .$fields[3]."' ;	\n" ;  
        }
        else                  {
				 echo "rcv_p_key='" .$fields[2]."' ;	\n" ;

		if(strpos($fields[4], "UserType=Doctor;")!==false)  $receiver_type="Doctor" ;
		else						    $receiver_type="Client" ;
	}
     }

				 echo "  msg_key=Crypto_decode(  msg_key, password) ;	\n" ;
				 echo "snd_s_key=Crypto_decode(snd_s_key, password) ;	\n" ;
				 echo " rcv_type='".$receiver_type."' ;			\n" ;

	              $res->close() ;

//--------------------------- Запрос атрибутов контрагента
//- - - - - - - - - - - - - - Контрагент - Доктор
   if($receiver_type=="Doctor") {

		       $sql="Select name_f, name_i, name_o".
			    "  From doctor_page_main".
			    " Where owner='$receiver_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса ключа подписи") ;
                         return ;
    }
    else
    {      
		$fields=$res->fetch_row() ;

	echo "    r_name_f='".$fields[0]."' ;	\n" ;
	echo "    r_name_i='".$fields[1]."' ;	\n" ;
	echo "    r_name_o='".$fields[2]."' ;	\n" ;
    }

       $res->close() ;
   }
//- - - - - - - - - - - - - - Контрагент - Пациент
   if($receiver_type=="Client") {

		       $sql="Select a.crypto, c.name_f, c.name_i, c.name_o".
			    "  From client_page_main c ".
                            "       left outer join access_list a on a.owner=c.owner and a.page=0".
			    " Where  c.owner='$receiver_'".
			    "  and   a.login='$user_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select CLIENT_MAIN_PAGE ... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса атрибутов пациента") ;
                         return ;
    }
    else
    {
	   $fields=$res->fetch_row() ;

	if($fields[0]=="") {
			     echo "    r_key   ='NONE' ;		\n" ;
			     echo "    r_name_f='".$receiver."' ;	\n" ;
			     echo "    r_name_i='' ;			\n" ;
			     echo "    r_name_o='' ;			\n" ;
	}
	else		   {
			     echo "    r_key   ='".$fields[0]."' ;	\n" ;
			     echo "    r_name_f='".$fields[1]."' ;	\n" ;
			     echo "    r_name_i='".$fields[2]."' ;	\n" ;
			     echo "    r_name_o='".$fields[3]."' ;	\n" ;
	}
    }

       $res->close() ;
   }
//--------------------------- Отправка сообщения

  if(isset($text)) 
  {
//- - - - - - - - - - - - - - Регистрация сообщения
          $text=$db->real_escape_string($text) ;
          $copy=$db->real_escape_string($copy) ;

                       $sql="Insert into messages(Receiver,Sender,Type,Text,Copy)".
                                         " values('$receiver_','$user_','CHAT_MESSAGE','$text','$copy')" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания сообщения") ;
                         return ;
    }
//- - - - - - - - - - - - - - Направление уведомления по Email
            Email_msg_notification($db, $receiver, $error) ;
//- - - - - - - - - - - - - - Простановка метки "Прочитано"
          $topread=$db->real_escape_string($topread) ;

                       $sql="Update messages".
                            "   Set `read`='Y'".
                            " Where sender  ='$receiver_'".
                            "  and  receiver='$user_'".
                            "  and  type='CHAT_MESSAGE'".
                            "  and  id<=$topread".
                            "  and  `read` is null" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка простановки меток 'прочитано'") ;
                         return ;
    }
//- - - - - - - - - - - - - -
             $db->commit() ;

		echo "  parent.frames['section'].location.reload() ;	\n" ;

        FileLog("", "Message for User ".$receiver." sent successfully") ;

              SuccessMsg() ;
  }
//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;				\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;		\n" ;
    echo  "i_error.innerHTML  ='Сообщение передано!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Message details chat</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_receiver ;
    var  i_text ;
    var  i_copy ;
    var  i_error ;
    var  password ;
    var  snd_s_key ;
    var  rcv_p_key ;
    var  rcv_type ;
    var  msg_key ;

  function FirstField() 
  {
    var  r_name_f ;
    var  r_name_i ;
    var  r_name_o ;
    var  r_key ;


       i_receiver =document.getElementById("Receiver") ;
       i_text     =document.getElementById("Text") ;
       i_copy     =document.getElementById("Copy") ;
       i_error    =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

    if(rcv_type=="Client" && r_key!="NONE")
    {
	r_key   =Crypto_decode(r_key,    password) ;
	r_name_f=Crypto_decode(r_name_f, r_key) ;
	r_name_i=Crypto_decode(r_name_i, r_key) ;
	r_name_o=Crypto_decode(r_name_o, r_key) ;
    }

       i_receiver.innerHTML=r_name_f+" "+r_name_i+" "+r_name_o ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

        error_text="" ;

    if(i_text.value=="")  return(false) ;

	i_copy.value=Crypto_encode(i_text.value, msg_key) ;
	i_text.value=  Sign_encode(i_text.value, snd_s_key, rcv_p_key) ;

     if(error_text!="") {
                          i_error.style.color="red" ;
                          i_error.innerHTML  = error_text ;
                              return false ;
                        }

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

  <form onsubmit="return SendFields();" method="POST">

  <b><dev id="Receiver"> </dev></b>
  <input type="submit" value="Отправить">
  <br>

  <textarea cols=80 rows=4 wrap="soft" name="Text" id="Text"></textarea>

  <input type="hidden" name="Copy" id="Copy">

  </form>

</body>

</html>
