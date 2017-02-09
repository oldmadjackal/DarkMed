<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_chat.php" ;

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

                        $session=$_GET ["Session"] ;
  if(!isset($session))  $session=$_POST["Session"] ;

                        $sender =$_GET ["Sender"] ;
  if(!isset($sender ))  $sender =$_POST["Sender"] ;

  if(isset($_POST["ChatOnly"]))  $chatonly=$_POST["ChatOnly"] ;

                        $topread=$_POST["TopRead"] ;

  if( isset($topread))
  {
                        $text   =$_POST["Text"] ;
                        $copy   =$_POST["Copy"] ;
  }

     FileLog("START", "    Session:".$session) ;
     FileLog("",      "     Sender:".$sender) ;

  if( isset($topread))
  {
     FileLog("",      "    TopRead:".$topread) ;
     FileLog("",      "       Text:".$text) ;
     FileLog("",      "       Copy:".$copy) ;
  }

  if( isset($chatonly))  FileLog("",      "   ChatOnly:".$chatonly) ;

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

       $user_  =$db->real_escape_string($user  ) ;
       $sender_=$db->real_escape_string($sender) ;

//--------------------------- Отправка сообщения

  if(isset($text) && $test!="") 
  {
//- - - - - - - - - - - - - - Регистрация сообщения
          $topread=$db->real_escape_string($topread) ;
          $text   =$db->real_escape_string($text) ;
          $copy   =$db->real_escape_string($copy) ;

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
//- - - - - - - - - - - - - - Простановка метки "Прочитано"
          $topread=$db->real_escape_string($topread) ;

                       $sql="Update messages".
                            "   Set `read`='Y'".
                            " Where sender  ='$sender_'".
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

        FileLog("", "Message for User ".$sender." sent successfully") ;

              SuccessMsg() ;
  }
//--------------------------- Извлечение ключей подписи получателя и отправителя

                       $sql="Select login, sign_s_key, sign_p_key, msg_key".
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

        if($fields[0]==$user) {  echo "rec_s_key='" .$fields[1]."' ;	\n" ;
				 echo "rec_p_key='" .$fields[2]."' ;	\n" ;
				 echo "  msg_key='" .$fields[3]."' ;	\n" ;  }
        else                  {  echo "snd_p_key='" .$fields[2]."' ;	\n" ;  }
    }

				 echo "  msg_key=Crypto_decode(  msg_key, password) ;	\n" ;
				 echo "rec_s_key=Crypto_decode(rec_s_key, password) ;	\n" ;

				 echo "  partner='" .$sender."' ;	\n" ;

	              $res->close() ;

//--------------------------- Формирование списка сообщений

           echo "  i_chat_only_cb.checked=".$chatonly." ;	\n" ;

                          $sql="Select m.sender, m.id, m.type, t.name, m.text, m.copy, m.sent".
			       "  From messages m ".
                               "       inner join ref_messages_types t on t.code=m.type and  t.language='RU' ".
			       " Where ((m.receiver='$user_'   and m.sender='$sender_') or ".
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
		   echo "    msg_text =Sign_decode(msg_text, snd_p_key, rec_s_key) ;	\n" ;
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

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color='green' ;		\n" ;
    echo  "i_error.innerHTML  ='Сообщение отправлено' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Mobile Chat</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script src="rsa.js"></script>

<script type="text/javascript">
<!--

    var  i_chat_only ;
    var  i_chat_only_cb ;
    var  i_messages ;
    var  i_text ;
    var  i_copy ;
    var  i_error ;
    var  rec_s_key ;
    var  rec_p_key ;
    var  snd_p_key ;
    var  top_id ;
    var  msg_key ;

  function FirstField() 
  {
    var  session ;
    var  partner ;
    var  password ;
    var  top_id ;


       i_chat_only   =document.getElementById("ChatOnly") ;
       i_chat_only_cb=document.getElementById("ChatOnly_cb") ;
       i_messages    =document.getElementById("Messages") ;
       i_text        =document.getElementById("Text") ;
       i_copy        =document.getElementById("Copy") ;
       i_error       =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;
        session=TransitContext("restore", "session",  "") ;

<?php
            ProcessDB() ;
?>

       document.getElementById("TopRead").value=top_id ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


        i_chat_only.value=i_chat_only_cb.checked ;

            error_text="" ;

    if(i_text.value=="")  return(false) ;

	i_copy.value=Crypto_encode(i_text.value, msg_key) ;
	i_text.value=  Sign_encode(i_text.value, rec_s_key, snd_p_key) ;

        i_error.style.color="red" ;
        i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;         
  } 

  function AddNewMessage(p_dir, p_id, p_type, p_type_desc, p_text, p_sent)
  {
     var  i_tab_new ;
     var  i_tbd_new ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_dev_new ;
     var  txt_left ;
     var  txt_right ;
     var  header ;


	header=p_sent+" "+p_type_desc ;

		   txt_left ="" ;
		   txt_right="" ;

   if(p_dir=='R')  txt_left =p_text ;
   else		   txt_right=p_text ;

	i_tab_new = document.createElement("table") ;
	i_tbd_new = document.createElement("tbody") ;

	i_row_new = document.createElement("tr") ;

	i_col_new = document.createElement("td") ;

   if(p_dir=='R')
   {
	i_col_new . className = "ChatReceiver" ;
	i_col_new . width     = "75%" ;
        i_dev_new = document.createElement("dev") ;
	i_dev_new . style.fontWeight=600 ;
        i_txt_new = document.createTextNode(header) ;
	i_dev_new . appendChild(i_txt_new) ;
	i_col_new . appendChild(i_dev_new) ;
	i_dev_new = document.createElement("br") ;
	i_col_new . appendChild(i_dev_new) ;
   }
   else
   {
	i_col_new . width     = "25%" ;
   }

        i_txt_new = document.createTextNode(txt_left) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;

   if(p_dir=='S')
   {
	i_col_new . className = "ChatSender" ;
	i_col_new . width     = "75%" ;
        i_dev_new = document.createElement("dev") ;
	i_dev_new . style.fontWeight=600 ;
        i_txt_new = document.createTextNode(header) ;
	i_dev_new . appendChild(i_txt_new) ;
	i_col_new . appendChild(i_dev_new) ;
	i_dev_new = document.createElement("br") ;
	i_col_new . appendChild(i_dev_new) ;
   }
   else
   {
	i_col_new . width     = "25%" ;
   }

        i_txt_new = document.createTextNode(txt_right) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_tbd_new .appendChild(i_row_new) ;
	i_tab_new .appendChild(i_tbd_new) ;
	i_messages.appendChild(i_tab_new) ;

    return ;         
  } 

  function EnableReply() 
  {
	document.getElementById("Send" ).hidden=true ;
	document.getElementById("Reply").hidden=false ;     
	document.getElementById("Text" ).focus() ;     
  }

  function ChatOnlyCheck() 
  {
          SendFields() ;

     document.forms[0].submit() ;
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
        <input type="button" value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" value="!" hidden onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b>ПЕРЕПИСКА</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">

  <div class="Normal_CT">
    <p class="Error_LT" id="Error"></p>
    <input type="checkbox" style="transform: scale(4)" id="ChatOnly_cb" onclick=ChatOnlyCheck()>
    . Только сообщения чата
    <input type="hidden" name="ChatOnly" id="ChatOnly">
    <br> 
    <br> 
  </div>

  <div class="Normal_CT" id="Send">
    <input type="button" value="Написать сообщение" onclick=EnableReply()>
  </div>

  <div class="fieldC" hidden id="Reply">
    <textarea cols=32 rows=7 wrap="soft" name="Text" id="Text"></textarea>
    <br>
    <input type="submit" value="Отправить"> 
    <input type="hidden" name="TopRead" id="TopRead">
    <input type="hidden" name="Copy"    id="Copy">
  </div>

  <br>

  <div id="Messages">
  </div>

  </form>

</body>

</html>
