<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Messages_char_lr.php" ;

  require("stdlib.php") ;

//============================================== 
//  ������ � ��

function ProcessDB() {

//--------------------------- ���������� ������������

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         return ;
  }
//--------------------------- ���������� ����������

                        $session=$_GET ["Session"] ;
  if(!isset($session))  $session=$_POST["Session"] ;

                        $sender =$_GET ["Sender" ] ;
  if(!isset($sender ))  $sender =$_POST["Sender" ] ;

  if(isset($_POST["ChatOnly"]))  $chatonly=$_POST["ChatOnly"] ;

                        FileLog("START", "    Session:".$session) ;
                        FileLog("",      "     Sender:".$sender) ;
  if(isset($chatonly))  FileLog("",      "   ChatOnly:".$chatonly) ;

  if(!isset($chatonly))  $chatonly="true" ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ������������� ������

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
  }

       $user_=$db->real_escape_string($user ) ;

//--------------------------- ���������� ������ ������� ���������� � �����������

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
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������ �������") ;
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

//--------------------------- ������������ ������ ���������

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
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ������ ���������") ;
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

//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color=\"red\" ;      " ;
    echo  "i_error.innerHTML  =\"".$text."\" ;" ;
    echo  "return ;" ;
}

//============================================== 
//  ������ ��������� �� �������� ��������

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    " ;
    echo  "i_error.innerHTML  =\"������ � ��������� ��������� ������������.\" ;" ;
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
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script src="rsa.js"></script>

<script type="text/javascript">
<!--

    var  i_chat_only ;
    var  i_chat_only_cb ;
    var  i_messages ;
    var  i_error ;
    var  rec_s_key ;
    var  rec_p_key ;
    var  snd_p_key ;
    var  msg_text ;

  function FirstField() 
  {
    var  session ;
    var  partner ;
    var  password ;
    var  top_id ;


       i_chat_only   =document.getElementById("ChatOnly") ;
       i_chat_only_cb=document.getElementById("ChatOnly_cb") ;
       i_messages    =document.getElementById("Messages") ;
       i_error       =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;
        session=TransitContext("restore", "session",  "") ;

<?php
            ProcessDB() ;
?>

	parent.frames["details"].location.replace("message_details_chat.php?Session="+session+
                                                                        "&Receiver="+partner+
                                                                         "&TopRead="+top_id) ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


        i_chat_only.value=i_chat_only_cb.checked ;

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

	i_col_new = document.createElement("td") ;

   if(p_dir=='R') {
	i_col_new . className = "ChatReceiver" ;
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
	i_col_new . className = "ChatSender" ;
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

  <table width="90%">
    <tbody>
    <tr>
      <td width="10%"> 
        <input type="button" class="HelpButton"     value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" class="CallBackButton" value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="FormTitle"> 
        <b>���������</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">
    <p class="Error_CT" id="Error"></p>
    <input type="submit" value="��������"> 
    <br>
    <br>
    <input type="checkbox" id="ChatOnly_cb"> ������ ��������� ����
    <input type="hidden" name="ChatOnly" id="ChatOnly">
    <br>
    <br>
  <table width="100%">
    <tbody id="Messages">
    </tbody>
  </table>

  </form>

</body>

</html>
