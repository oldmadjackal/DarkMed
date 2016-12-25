<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Messages_char_lr.php" ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД

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
                                 $sender  =$_GET ["Sender" ] ;
                                 $parent  =$_GET ["Parent" ] ;
  if(isset($_POST["Text"    ]))  $text    =$_POST["Text"] ;
  if(isset($_POST["Copy"    ]))  $copy    =$_POST["Copy"] ;
  if(isset($_POST["TopId"   ]))  $topid   =$_POST["TopId"] ;
  if(isset($_POST["ChatOnly"]))  $chatonly=$_POST["ChatOnly"] ;

                        FileLog("START", "    Session:".$session) ;
                        FileLog("",      "     Sender:".$sender) ;
  if(isset($text    ))  FileLog("",      "       Text:".$text) ;
  if(isset($copy    ))  FileLog("",      "       Copy:".$copy) ;
  if(isset($topid   ))  FileLog("",      "      TopId:".$topid) ;
  if(isset($chatonly))  FileLog("",      "   ChatOnly:".$chatonly) ;

  if(!isset($chatonly))  $chatonly="true" ;

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
    
           echo "  snd_user='".$sender."' ;	\n" ;

       $sender_=$db->real_escape_string($sender) ;

                       $sql="Select login, sign_s_key, sign_p_key, msg_key, options".
                            "  From users".
                            " Where login='$user_' ".
                            "   or  login='$sender_' " ;
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
                                 echo "rcv_s_key='" .$fields[1]."' ;	\n" ;
				 echo "rcv_p_key='" .$fields[2]."' ;	\n" ;
				 echo "  msg_key='" .$fields[3]."' ;	\n" ;
                              }
        else                  {
                                 echo "snd_p_key='" .$fields[2]."' ;	\n" ; 

		if(strpos($fields[4], "Client;")!==false)  $sender_type="Client" ;
		else			        	   $sender_type="Doctor" ;
                              }
     }

				 echo "  msg_key=Crypto_decode(  msg_key, password) ;	\n" ;
				 echo "rcv_s_key=Crypto_decode(rcv_s_key, password) ;	\n" ;
				 echo " snd_type='".$sender_type."' ;			\n" ;

	              $res->close() ;

//--------------------------- Запрос атрибутов контрагента
//- - - - - - - - - - - - - - Контрагент - Доктор
   if($sender_type=="Doctor") {

		       $sql="Select name_f, name_i, name_o".
			    "  From doctor_page_main".
			    " Where owner='$sender_'" ;
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

	echo "    s_name_f='".$fields[0]."' ;	\n" ;
	echo "    s_name_i='".$fields[1]."' ;	\n" ;
	echo "    s_name_o='".$fields[2]."' ;	\n" ;
    }

       $res->close() ;
   }
//- - - - - - - - - - - - - - Контрагент - Пациент
   if($sender_type=="Client") {

		       $sql="Select a.crypto, c.name_f, c.name_i, c.name_o".
			    "  From client_page_main c ".
                            "       left outer join access_list a on a.owner=c.owner and a.page=0".
			    " Where  c.owner='$sender_'".
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
			     echo "    s_key   ='NONE' ;		\n" ;
			     echo "    s_name_f='".$sender."' ;		\n" ;
			     echo "    s_name_i='' ;			\n" ;
			     echo "    s_name_o='' ;			\n" ;
	}
	else		   {
			     echo "    s_key   ='".$fields[0]."' ;	\n" ;
			     echo "    s_name_f='".$fields[1]."' ;	\n" ;
			     echo "    s_name_i='".$fields[2]."' ;	\n" ;
			     echo "    s_name_o='".$fields[3]."' ;	\n" ;
	}
    }

       $res->close() ;
   }
//--------------------------- Отправка сообщения

  if(isset($text) && $text<>"") 
  {
//- - - - - - - - - - - - - - Регистрация сообщения
          $text=$db->real_escape_string($text) ;
          $copy=$db->real_escape_string($copy) ;

                       $sql="Insert into messages(Receiver,Sender,Type,Text,Copy)".
                                         " values('$sender_','$user_','CHAT_MESSAGE','$text','$copy')" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания сообщения") ;
                         return ;
    }
//- - - - - - - - - - - - - - Направление уведомления по Email
            Email_msg_notification($db, $sender, $error) ;
//- - - - - - - - - - - - - - Простановка метки "Прочитано"
          $topid=$db->real_escape_string($topid) ;

                       $sql="Update messages".
                            "   Set `read`='Y'".
                            " Where sender  ='$sender_'".
                            "  and  receiver='$user_'".
                            "  and  type='CHAT_MESSAGE'".
                            "  and  id<=$topid".
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

        FileLog("", "Message for User ".$sender." sent successfully") ;
  }
//--------------------------- Формирование списка сообщений

           echo "  i_chat_only_cb.checked=".$chatonly." ;	\n" ;

                         $sql ="Select m.sender, m.id, m.type, t.name, m.text, m.copy, m.sent".
			       "  From messages m ".
                               "       inner join ref_messages_types t on t.code=m.type and  t.language='RU' ".
			       " Where ((m.receiver='$user_'   and m.sender='$sender_') or".
                               "        (m.receiver='$sender_' and m.sender='$user_'  )   )" ;
  if($chatonly=="true")  $sql.="  and    m.type='CHAT_MESSAGE'"  ;
                         $sql.=" Order by id desc"  ;

     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка сообщений") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No messages detected") ;

	           echo "    top_id='0' ;	\n" ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
		      $fields=$res->fetch_row() ;

	if($i==0)  echo "    top_id   ='".$fields[1]."' ;	\n" ;

		   echo "    msg_id   ='".$fields[1]."' ;	\n" ;
		   echo "    msg_type ='".$fields[2]."' ;	\n" ;
		   echo "    msg_type_='".$fields[3]."' ;	\n" ;
		   echo "    msg_sent ='".$fields[6]."' ;	\n" ;

	if($fields[0]==$user) {
		   echo "    msg_dir  ='S' ;					\n" ;
		   echo "    msg_text ='".$fields[5]."' ;			\n" ;
	 	   echo "    msg_text =Crypto_decode(msg_text, msg_key) ;	\n" ;
	}
        else		      {
		   echo "    msg_dir  ='R' ;						\n" ;
	 	   echo "    msg_text ='".$fields[4]."' ;				\n" ;
		   echo "    msg_text =Sign_decode(msg_text, snd_p_key, rcv_s_key) ;	\n" ;
	}

		   echo "  AddNewMessage(msg_dir, msg_id, msg_type, msg_type_, msg_text, msg_sent) ;	\n" ;
     }
  }

     $res->close() ;

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
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    " ;
    echo  "i_error.innerHTML  =\"Доступ к указанным страницам предоставлен.\" ;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Chat Left-Right</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script src="rsa.js"></script>

<script type="text/javascript">
<!--

    var  i_sender ;
    var  i_text ;
    var  i_copy ;
    var  i_messages ;
    var  i_top_id ;
    var  i_chat_only ;
    var  i_chat_only_cb ;
    var  i_error ;
    var  snd_user ;
    var  rcv_s_key ;
    var  rcv_p_key ;
    var  snd_p_key ;
    var  msg_text ;
    var  snd_type ;
    var  msg_key ;
    var  top_id ;

  function FirstField() 
  {
    var  session ;
    var  password ;
    var  top_id ;
    var  s_name_f ;
    var  s_name_i ;
    var  s_name_o ;
    var  s_key ;


       i_sender      =document.getElementById("Sender") ;
       i_text        =document.getElementById("Text") ;
       i_copy        =document.getElementById("Copy") ;
       i_messages    =document.getElementById("Messages") ;
       i_top_id      =document.getElementById("TopId") ;
       i_chat_only   =document.getElementById("ChatOnly") ;
       i_chat_only_cb=document.getElementById("ChatOnly_cb") ;
       i_error       =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;
        session=TransitContext("restore", "session",  "") ;

<?php
            ProcessDB() ;
?>

    if(snd_type=="Client" && s_key!="NONE")
    {
	s_key   =Crypto_decode(s_key,    password) ;
	s_name_f=Crypto_decode(s_name_f, s_key) ;
	s_name_i=Crypto_decode(s_name_i, s_key) ;
	s_name_o=Crypto_decode(s_name_o, s_key) ;
    }

         i_sender.innerHTML=s_name_f+" "+s_name_i+" "+s_name_o ;
         i_top_id.value    =top_id ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

        error_text="" ;

        i_chat_only.value=i_chat_only_cb.checked ;

     if(i_text.value=="")  return(true) ;

	i_copy.value=Crypto_encode(i_text.value, msg_key) ;
	i_text.value=  Sign_encode(i_text.value, rcv_s_key, snd_p_key) ;

     if(error_text!="") {
                          i_error.style.color="red" ;
                          i_error.innerHTML  = error_text ;
                              return false ;
                        }

      return true ;         
  } 

  function AddNewMessage(p_dir, p_id, p_type, p_type_desc, p_text, p_sent)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_dev_new ;
     var  txt_left ;
     var  txt_right ;
     var  header ;


	header=p_sent+" "+p_type_desc ;

	i_row_new = document.createElement("tr") ;
//	i_row_new . className = "table" ;

	i_col_new = document.createElement("td") ;

   if(p_dir=='R') 
	i_col_new . className = "chat_r" ;
   else i_col_new . className = "chat_s" ;

        i_dev_new = document.createElement("dev") ;
	i_dev_new . style.fontWeight=600 ;
        i_txt_new = document.createTextNode(header) ;
	i_dev_new . appendChild(i_txt_new) ;
	i_col_new . appendChild(i_dev_new) ;
	i_dev_new = document.createElement("br") ;
	i_col_new . appendChild(i_dev_new) ;
        i_txt_new = document.createTextNode(p_text) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_messages.appendChild(i_row_new) ;

    return ;         
  } 

  function EI_GetSelectedDoctor() 
  {
            doctor_pars=new Object ;
            doctor_pars.user=snd_user ;
            doctor_pars.name=i_sender.innerHTML ;
            doctor_pars.pkey=snd_p_key ;
     return(doctor_pars) ;
  } 

  function GoBack() 
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	location.replace("doctors_list_short.php?Session="+v_session) ;
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

  <form onsubmit="return SendFields();" method="POST">
    <input type="button" value="<< Вернуться" onclick=GoBack()> 
    <br>
    <br>
    <div class="Bold_CT" id="Sender"> </div>
    <br>
    <div class="Normal_CT">
      <textarea cols=42 rows=5 wrap="soft" name="Text" id="Text"></textarea>
      <br>
      <input type="submit" value="Обновить/Отправить"> 
      <br>
      <br>
      <input type="checkbox" id="ChatOnly_cb"> Только сообщения чата
      <br>
      <br>
      <input type="hidden" name="Copy"     id="Copy">
      <input type="hidden" name="TopId"    id="TopId">
      <input type="hidden" name="ChatOnly" id="ChatOnly">
    </div>

  <table width="100%">
    <tbody id="Messages">
    </tbody>
  </table>

  </form>

</body>

</html>
