<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Message_details_invite.php" ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ��������������� � ��

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
                                  $message=$_GET ["Message"] ;

    if(isset($_POST["Action" ]))  $action =$_POST["Action" ] ;
    if(isset($_POST["Details"]))  $details=$_POST["Details"] ;
    if(isset($_POST["Check"  ]))  $check  =$_POST["Check"  ] ;
    if(isset($_POST["Text"   ]))  $text   =$_POST["Text"   ] ;
    if(isset($_POST["Copy"   ]))  $copy   =$_POST["Copy"   ] ;    
    
                         FileLog("START", "    Session:".$session) ;
                         FileLog("",      "    Message:".$message) ;
    if(isset($action ))  FileLog("",      "     Action:".$action) ;
    if(isset($details))  FileLog("",      "    Details:".$details) ;
    if(isset($check  ))  FileLog("",      "      Check:".$check) ;
    if(isset($text   ))  FileLog("",      "       Text:".$text) ;
    if(isset($copy   ))  FileLog("",      "       Copy:".$copy) ;

//--------------------------- ���������

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ������������� ������

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                    ErrorMsg($error) ;
                         return ;
  }

       $user_   =$db->real_escape_string($user ) ;
       $message_=$db->real_escape_string($message) ;

//--------------------------- ������ ������ ������� ����������

                     $sql="Select u.sign_s_key, u.msg_key".
			  "  From `users` u".
			  " Where u.login  ='$user_'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select USERS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ����� �������") ;
                         return ;
  }
  else
  {      
	      $fields=$res->fetch_row() ;

       echo "    doc_s_key='".$fields[0]."' ;			\n" ;
       echo "    doc_s_key=Crypto_decode(doc_s_key, password) ;	\n" ;
       echo "      msg_key='".$fields[1]."' ;			\n" ;  
  }

     $res->close() ;

//--------------------------- ���������� ������ ���������

                     $sql="Select m.id, m.sender, m.sent, m.text, m.details, u.sign_p_key,".
                          "       c.check, c.name_f, c.name_i, c.name_o, m.done".
			  "  From `messages` m, `ref_messages_types` t, users u, client_page_main c".
			  " Where m.`receiver`='$user_'".
			  "  and  m.`id`      = $message_".
			  "  and  u.`login`   = m.`sender`".
			  "  and  c.`owner`   = m.`sender`".
			  "  and  t.`code`    = m.`type`".
			  "  and  t.`language`='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������") ;
                         return ;
  }
  if($res->num_rows==0) {
          FileLog("ERROR", "No such message in DB) : ".$message) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ����������� ���������") ;
                         return ;
  }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

        FileLog("", "Message presented successfully") ;

             $owner=$fields[1] ;

      echo "    message_id           ='".$fields[0]."' ;				\n" ;
      echo "    sender_user          ='".$fields[1]."' ;				\n" ;
      echo "    snd_p_key            ='".$fields[5]."' ;				\n" ;
      echo "    details              ='".$fields[4]."' ;				\n" ;
      echo "    details              =Sign_decode(details, snd_p_key, doc_s_key) ;	\n" ;

      echo "  i_msg_sender .innerHTML='".$owner."' ;					\n" ;
      echo "  i_msg_sent   .innerHTML='".$fields[2]."' ;				\n" ;
      echo "    text                 ='".$fields[3]."' ;				\n" ;
      echo "    text                 =Sign_decode(text, snd_p_key, doc_s_key) ;		\n" ;
      echo "  i_msg_text   .innerHTML= text ;						\n" ;

      echo "    c_check              ='".$fields[6]."' ;				\n" ;
      echo "    c_name_f             ='".$fields[7]."' ;				\n" ;
      echo "    c_name_i             ='".$fields[8]."' ;				\n" ;
      echo "    c_name_o             ='".$fields[9]."' ;				\n" ;

      echo "    msg_done             ='".$fields[10]."' ;				\n" ;

//--------------------------- ���������� ����� ������� � ������� �������� �������

                     $sql="Select crypto".
			  "  From `access_list`".
			  " Where `owner`='$owner'".
			  "  and  `login`='$user_'".
			  "  and  `page` =  0" ;

     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ����� ������� ��������") ;
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

//--------------------------- ����������� ����� ��������

  if(isset($action) && $action=="Accept") 
  {
        $check_=$db->real_escape_string($check) ;
//- - - - - - - - - - - - - - ������� �������, �� ������� ������������ ������
	$words=explode(" ", $details) ;

    for($i=0 ; $i<count($words) ; $i=$i+3)
    {
       if($words[$i]=="")  break ;

          $page  =$words[$i  ] ;
          $key_1 =$words[$i+1] ;
          $key_2 =$words[$i+2] ;
          $page_ =$db->real_escape_string($page) ;
          $key_1_=$db->real_escape_string($key_1) ;
          $key_2_=$db->real_escape_string($key_2) ;
//- - - - - - - - - - - - - - �������� ���������� ������� �������
                       $sql="Select page ".
                            "from   access_list ".
                            "where  Owner='$owner'".
                            " and   Login='$user_'".
                            " and   Page ='$page_'" ;
        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                       $db->rollback() ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ���������� �������") ;
                         return ;
     }
     if($res->num_rows!=0) {
        FileLog("", "Access already granted: ".$owner.":".$page." for ".$user) ;
                                 continue ;
     }
			      $res->free() ;
//- - - - - - - - - - - - - - �������� ������ � �������
                       $sql="Insert into access_list".
                            "(owner, login, page, crypto, ext_key) ".
                            "values".
                            "('$owner','$user_','$page_','$key_1_','$key_2_')" ;
        $res=$db->query($sql) ;
     if($res===false) {
               FileLog("ERROR", "Insert ACCESS_LIST... : ".$db->error) ;
                       $db->rollback() ;
                       $db->close() ;
              ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ � ���� ������") ;
                           return ;
     }

        FileLog("", "Access successfully granted: ".$owner.":".$page." for ".$user) ;
//- - - - - - - - - - - - - - ������� �������, �� ������� ������������ ������
    }
//- - - - - - - - - - - - - - �������� �������� ������� � �������
    do
    {
//- - - - - - - - - - - - - - �������� ������� �������� ������� � �������
                        $sql="Select client ".
                             "from   doctor_notes ".
                             "where  owner ='$user_'".
                             " and   client='$owner'" ;
        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Select DOCTOR_NOTES... : ".$db->error) ;
                       $db->rollback() ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ���������� �������� �������� �������") ;
                         break ;
     }

     if($res->num_rows!=0) {
        FileLog("", "Notes page already created for ".$owner) ;
	  	              $res->free() ;
                                 break ;
     }

	  	              $res->free() ;
//- - - - - - - - - - - - - - �������� �������� ������� � �������
                       $sql="Insert into `doctor_notes`".
                            "(`Owner`, `Client`, `Check`) ".
                            "values".
                            "('$user_','$owner','$check_')" ;
        $res=$db->query($sql) ;
     if($res===false) {
               FileLog("ERROR", "Insert DOCTOR_NOTES... : ".$db->error) ;
                       $db->rollback() ;
                       $db->close() ;
              ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� �������� �������") ;
                           break ;
     }

        FileLog("", "Notes page successfully created for ".$owner) ;
//- - - - - - - - - - - - - - �������� �������� ������� � �������
    } while(false) ;
  }
//--------------------------- �������� �������� ��� ������

  if(isset($action) && $action=="Reject") 
  {
                       $sql="Delete from access_list ".
                            "Where  owner='$owner'".
                            " and   login='$user_'" ;
        $res=$db->query($sql) ;
     if($res===false) {
               FileLog("ERROR", "Delete ACCESS_LIST... : ".$db->error) ;
                       $db->rollback() ;
                       $db->close() ;
              ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� �� ���� ������") ;
                           return ;
     }

        FileLog("", "Access successfully deleted : ".$owner." for ".$user) ;
  }
//--------------------------- ������������ ��������� � �����

  if(isset($action)) 
  {
//- - - - - - - - - - - - - - ������������ ��������� ��������
          $text=$db->real_escape_string($text) ;
          $copy=$db->real_escape_string($copy) ;

    if($action=="Accept")  $msg_type='CLIENT_INVITE_ACCEPT' ;
    else                   $msg_type='CLIENT_INVITE_REJECT' ;

                       $sql="Insert into messages(Receiver,Sender,Type,Text,Copy)".
                                         " values('$owner','$user_','$msg_type','$text','$copy')" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ���������") ;
                         return ;
    }

//- - - - - - - - - - - - - - ������������ email-����������
    if($action=="Accept")  Email_msg_notification($db, $owner, $error) ;
    else                   Email_msg_notification($db, $owner, $error) ;
//- - - - - - - - - - - - - -
  }
//--------------------------- ������� � ���������

  if(isset($action)) 
  {
       if($action=="Accept")  $flag='Y' ;
       else                   $flag='R' ;

                          $sql="Update messages ".
                               "   Set `read`='Y' ".
                               "      ,`done`='$flag' ".
	                       " Where receiver='$user_'".
	  		       "  and  id      = $message_" ;
          $res=$db->query($sql) ;
       if($res===false) {
              FileLog("ERROR", "Update MESSAGES... : ".$db->error) ;
                                $db->close() ;
             ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ��������� ������� ���������") ;
                             return ;
       }
  }
//--------------------------- ����������

  if(isset($action)) 
  {
           $db->commit() ;

      if($action=="Accept")  SuccessMsg("������ ������������!") ;
      else                   SuccessMsg("������ �����������!") ;
  }
//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  ������ ��������� �� �������� �����������

function SuccessMsg($text) {

    echo  "i_error.style.color='green' ;	\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Message details invite</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_msg_sender ;
    var  i_msg_sent ;
    var  i_msg_text ;
    var  i_details ;
    var  i_check ;
    var  i_text ;
    var  i_copy ;
    var  i_action ;
    var  i_accept ;
    var  i_reject ;
    var  i_error ;
    var  password ;
    var  message_id ;
    var  sender_user ;
    var  execute ;
    var  doc_s_key ;
    var  snd_p_key ;
    var  rcv_type ;
    var  msg_key ;
    var  a_pages_keys ;
    var  a_files_keys ;

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


       i_msg_sender =document.getElementById("MsgSender") ;
       i_msg_sent   =document.getElementById("MsgSent") ;
       i_msg_text   =document.getElementById("MsgText") ;
       i_error      =document.getElementById("Error") ;
       i_details    =document.getElementById("Details") ;
       i_check      =document.getElementById("Check") ;
       i_text       =document.getElementById("Text") ;
       i_copy       =document.getElementById("Copy") ;
       i_action     =document.getElementById("Action") ;
       i_accept     =document.getElementById("Accept") ;
       i_reject     =document.getElementById("Reject") ;

       password=TransitContext("restore", "password", "") ;

        a_pages_keys=new Array() ;
        a_files_keys=new Array() ;

<?php
            ProcessDB() ;
?>

      words_1=details.split(';') ;

    for(var i=0 ; i<words_1.length ; i++)
    {
      if(words_1[i]!="") 
      {
                      words_2    =words_1[i].split(':') ;
         a_pages_keys[words_2[0]]=words_2[1] ;
         a_files_keys[words_2[0]]=words_2[2] ;
      }
    }

    if(c_name_key!="NONE")
    {
       c_name_key=Crypto_decode(c_name_key, password) ;
    }
    else
    {
      if(a_pages_keys["0"]!="") c_name_key=a_pages_keys["0"] ;
    }

    if(c_name_key!="NONE")
    {
       c_name_f=Crypto_decode(c_name_f, c_name_key) ;
       c_name_i=Crypto_decode(c_name_i, c_name_key) ;
       c_name_o=Crypto_decode(c_name_o, c_name_key) ;

       i_msg_sender.innerHTML=c_name_f+" "+c_name_i+" "+c_name_o ;
    }

        if(msg_done=='Y') {  i_accept.hidden=true ;  }
        if(msg_done=='R') {  i_reject.hidden=true ;  }

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

        error_text="" ;

	i_copy.value=Crypto_encode(i_text.value, msg_key) ;
	i_text.value=  Sign_encode(i_text.value, doc_s_key, snd_p_key) ;

     if(error_text!="") {
                          i_error.style.color="red" ;
                          i_error.innerHTML  = error_text ;
                              return false ;
                        }

      return true ;         
  }

  function AcceptInvite() 
  {
     var  details ;

        error_text="" ;

           details="" ;

    for(var elem in a_pages_keys)
    {
        details=details+elem+" " ;
	details=details+Crypto_encode(a_pages_keys[elem], password) ;
        details=details+" " ;
	details=details+Crypto_encode(a_files_keys[elem], password) ;
        details=details+" " ;
    }

	  i_details.value=details ;
          
	  i_action.value="Accept" ;
            i_text.value="���� ����������� �������" ;

                      SendFields() ; 
        document.forms[0].submit() ;
  }
  
  function RejectInvite() 
  {
	  i_action.value="Reject" ;
            i_text.value="���� ����������� ���������" ;

                      SendFields() ; 
        document.forms[0].submit() ;
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

  <div class="Normal_CT"><b>����������� �� ��������</b></div>
  <br>
  <div class="Normal_CT">
    <div id="MsgSender"></div>
    <input type="button" value="���������" onclick=GoToChat()>
  </div>
  <br>
  <div class="Normal_CT" id="MsgSent"> </div>
  <br>         
  <em><div class="Normal_CT" id="MsgText"></div>
  <br>
  <div class="Normal_CT">
    <input type="button" class="InsertButton" id="Accept" value="�������"  onclick=AcceptInvite()>
    <input type="button" class="DeleteButton" id="Reject" value="��������" onclick=RejectInvite()>
  </div>

  <input type="hidden" name="Details" id="Details">
  <input type="hidden" name="Check"   id="Check">
  <input type="hidden" name="Action"  id="Action">
  <input type="hidden" name="Text"    id="Text">
  <input type="hidden" name="Copy"    id="Copy">

  </form>

</body>

</html>
