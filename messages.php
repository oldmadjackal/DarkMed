<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Messages.php" ;

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

  FileLog("START", "    Session:".$session) ;

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

//--------------------------- Формирование списка сообщений

  if(strpos($options, "UserType=Doctor;")!==false)
  {
                     $sql="Select m.id, m.sender, m.type, t.name, m.text, u.sign_p_key,".
			  "       c.name_f, c.name_i, c.name_o, a.crypto".
			  "  From messages m ".
                          "       inner join ref_messages_types t on t.code=m.type and  t.language='RU' ".
                          "       inner join users u on u.login=m.sender ".
                          "       inner join client_page_main c on c.owner=m.sender ".
                          "       left outer join access_list a on a.owner=c.owner and a.login=m.receiver and a.page=0 ".
			  " Where m.`receiver`='$user_'".
                          " Order by m.id desc" ;
  }
  else
  {
                     $sql="Select m.id, m.sender, m.type, t.name, m.text, u.sign_p_key,".
			  "       d.name_f, d.name_i, d.name_o, 'nocrypt'".
			  "  From messages m ".
                          "       inner join ref_messages_types t on t.code=m.type and  t.language='RU' ".
                          "       inner join users u on u.login=m.sender ".
                          "       inner join doctor_page_main d on d.owner=m.sender ".
			  " Where m.`receiver`='$user_'".
                          " Order by m.id desc" ;
  }

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

       echo "  sender_key     =\"".$fields[5]."\" ;					\n" ;
       echo "    msg_id       =\"".$fields[0]."\" ;					\n" ;
       echo "    msg_sender_id=\"".$fields[1]."\" ;					\n" ;
       echo "    msg_type     =\"".$fields[2]."\" ;					\n" ;
       echo "    msg_type_    =\"".$fields[3]."\" ;					\n" ;
       echo "    msg_text     =\"".$fields[4]."\" ;					\n" ;
       echo "    msg_text     =Sign_decode(msg_text, sender_key, receiver_key) ;	\n" ;
       echo "    msg_sender   =\"".$fields[1]."\" ;					\n" ;

      if($fields[9]=="nocrypt")
      {
       echo "     msg_sender  ='".$fields[6]."'+' '	\n" ;
       echo "                 +'".$fields[7]."'+' '	\n" ;
       echo "                 +'".$fields[8]."' ;	\n" ;
      }
      else
      if($fields[9]!="")
      {
       echo "     msg_name_key=Crypto_decode('".$fields[9]."', password) ;	\n" ;
       echo "     msg_sender  =Crypto_decode('".$fields[6]."', msg_name_key)+' '	\n" ;
       echo "                 +Crypto_decode('".$fields[7]."', msg_name_key)+' '	\n" ;
       echo "                 +Crypto_decode('".$fields[8]."', msg_name_key) ;	\n" ;
      }	

       echo "  AddNewMessage(msg_id, msg_sender_id, msg_sender, msg_type, msg_type_, msg_text) ;	\n" ;
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

    var  i_table ;
    var  i_error ;
    var  password ;

  function FirstField() 
  {
    var  receiver_key ;
    var  sender_key ;
    var  msg_text ;

       i_table   =document.getElementById("Fields") ;
       i_pages   =document.getElementById("Pages") ;
       i_error   =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function SendFields() 
  {
     var  i_page ;
     var  i_doctor ;
     var  doctor_key ;
     var  error_text ;


	i_letter.value="" ;
            error_text="Не задано ни одной страницы для доступа" ;

        i_error.style.color="red" ;
        i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;         
  } 

  function AddNewMessage(p_id, p_sender_id, p_sender, p_type, p_type_desc, p_text)
  {
     var  i_messages ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_lst_new ;
     var  i_del_new ;

       i_messages= document.getElementById("Messages") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_id) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_sender) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_type_desc) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_text) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Детали" ;
       i_shw_new . id     ="Details_"+p_id ;
       i_shw_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									            v_form="message_details_any.php" ;
					  if(p_type=="CLIENT_ACCESS_INVITE"      )  v_form="message_details_invite.php" ;
					  if(p_type=="CLIENT_PRESCRIPTIONS_ALERT")  v_form="message_details_prescr.php" ;
					  if(p_type=="CHAT_MESSAGE"              )  v_form="message_details_simple.php" ;
						parent.frames["details"].location.assign(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Message="+p_id) ;
					} ;

       i_lst_new = document.createElement("input") ;
       i_lst_new . type   ="button" ;
       i_lst_new . value  ="Переписка" ;
       i_lst_new . id     ="Listing_"+p_id ;
       i_lst_new . onclick= function(e) {
					    var  v_session ;
						 v_session=TransitContext("restore","session","") ;

						location.assign("messages_chat_lr.php?Session="+v_session+
                                                                                        "&Sender="+p_sender_id) ;
					} ;

       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="Прочитано" ;
       i_del_new . id     ="Delete_"+p_id ;
       i_del_new . onclick="DeleteMessage" ;

       i_col_new = document.createElement("td") ;
       i_col_new . appendChild(i_shw_new) ;
       i_col_new . appendChild(i_del_new) ;
       i_col_new . appendChild(i_lst_new) ;
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
        <b>ВХОДЯЩИЕ СООБЩЕНИЯ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">
  <p class="error" id="Error"></p>
  <table class="table" width="100%">
    <thead>
    </thead>
    <tbody id="Messages">
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
