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

                        $session=$_GET ["Session"] ;
  if(!isset($session))  $session=$_POST["Session"] ;

                        $sender =$_GET ["Sender" ] ;
  if(!isset($sender ))  $sender =$_POST["Sender" ] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "     Sender:".$sender) ;

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

//--------------------------- Извлечение ключей подписи получателя и отправителя

       $sender_=$db->real_escape_string($sender) ;

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

                     $sql="Select m.sender, m.id, m.type, t.name, m.text, m.copy, m.sent".
			  "  From messages m ".
                          "       inner join ref_messages_types t on t.code=m.type and  t.language='RU' ".
			  " Where (m.receiver='$user_'   and m.sender='$sender_')".
                          "   or  (m.receiver='$sender_' and m.sender='$user_'  )".
                          " Order by id desc"  ;
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
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
		      $fields=$res->fetch_row() ;

	       echo "    msg_id   ='".$fields[1]."' ;	\n" ;
	       echo "    msg_type ='".$fields[2]."' ;	\n" ;
	       echo "    msg_type_='".$fields[3]."' ;	\n" ;
	       echo "    msg_sent ='".$fields[6]."' ;	\n" ;

	if($fields[0]==$user) {
	       echo "    msg_dir  ='S' ;		\n" ;
	       echo "    msg_text ='".$fields[5]."' ;	\n" ;
	       echo "    msg_text =Crypto_decode(msg_text, msg_key) ;	\n" ;
	}
        else		      {
	       echo "    msg_dir  ='R' ;		\n" ;
	       echo "    msg_text ='".$fields[4]."' ;	\n" ;
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

<title>DarkMed Messages InBox</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_messages ;
    var  i_error ;
    var  password ;
    var  session ;
    var  partner ;
    var  rec_s_key ;
    var  rec_p_key ;
    var  snd_p_key ;
    var  msg_text ;

  function FirstField() 
  {

       i_messages=document.getElementById("Messages") ;
       i_error   =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;
        session=TransitContext("restore", "session",  "") ;

<?php
            ProcessDB() ;
?>

	parent.frames["details"].location.assign("message_details_chat.php?Session="+session+
                                                                        "&Receiver="+partner) ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


            error_text="" ;

        i_error.style.color="red" ;
        i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

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

		   txt_left ="" ;
		   txt_right="" ;

   if(p_dir=='R')  txt_left =p_text ;
   else		   txt_right=p_text ;

	i_row_new = document.createElement("tr") ;
//	i_row_new . className = "table" ;

	i_col_new = document.createElement("td") ;

   if(p_dir=='R') {
	i_col_new . className = "chat_r" ;
        i_dev_new = document.createElement("dev") ;
	i_dev_new . style.fontWeight=600 ;
        i_txt_new = document.createTextNode(header) ;
	i_dev_new . appendChild(i_txt_new) ;
	i_col_new . appendChild(i_dev_new) ;
	i_dev_new = document.createElement("br") ;
	i_col_new . appendChild(i_dev_new) ;
		  }
        i_txt_new = document.createTextNode(txt_left) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;

   if(p_dir=='S') {
	i_col_new . className = "chat_s" ;
        i_dev_new = document.createElement("dev") ;
	i_dev_new . style.fontWeight=600 ;
        i_txt_new = document.createTextNode(header) ;
	i_dev_new . appendChild(i_txt_new) ;
	i_col_new . appendChild(i_dev_new) ;
	i_dev_new = document.createElement("br") ;
	i_col_new . appendChild(i_dev_new) ;
		  }
        i_txt_new = document.createTextNode(txt_right) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_messages.appendChild(i_row_new) ;

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

<div>

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
        <b>ПЕРЕПИСКА</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">
  <p class="error" id="Error"></p>
  <table width="100%">
    <thead>
    <input type="submit" value="Обновить"> 
    </thead>
    <tbody id="Messages">
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
