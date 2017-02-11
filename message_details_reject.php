<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Message_details_reject.php" ;

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
    if(isset($_GET ["Action" ]))  $action =$_GET ["Action" ] ;

                         FileLog("START", "    Session:".$session) ;
                         FileLog("",      "    Message:".$message) ;
    if(isset($action ))  FileLog("",      "     Action:".$action) ;

//--------------------------- ���������

                         $details="dummy" ;
    if(!isset($action))  $action ="none" ;

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

//--------------------------- ������ ����� ������� ����������

                     $sql="Select u.sign_s_key".
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

       echo "    receiver_key=\"".$fields[0]."\" ;			\n" ;
       echo "    receiver_key=Crypto_decode(receiver_key, password) ;	\n" ;
  }

     $res->close() ;

//--------------------------- ���������� ������ ���������

                     $sql="Select m.id, m.sender, m.sent, m.text, m.details, u.sign_p_key,".
                          "       d.name_f, d.name_i, d.name_o, m.done".
			  "  From `messages` m, `ref_messages_types` t, users u, doctor_page_main d".
			  " Where m.`receiver`='$user_'".
			  "  and  m.`id`      = $message".
			  "  and  u.`login`   = m.`sender`".
			  "  and  d.`owner`   = m.`sender`".
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

      echo "    message_id           ='".$fields[0]."' ;				\n" ;
      echo "    sender_user          ='".$fields[1]."' ;				\n" ;
      echo "    sender_key           ='".$fields[5]."' ;				\n" ;
      echo "    details              ='".$fields[4]."' ;				\n" ;
      echo "    details              =Sign_decode(details, sender_key, receiver_key) ;	\n" ;

      echo "  i_msg_sender .innerHTML='".$fields[1]."' ;				\n" ;
      echo "  i_msg_sent   .innerHTML='".$fields[2]."' ;				\n" ;
      echo "    text                 ='".$fields[3]."' ;				\n" ;
      echo "    text                 =Sign_decode(text, sender_key, receiver_key) ;	\n" ;
      echo "  i_msg_text   .innerHTML= text ;						\n" ;

      echo "    d_name_f             ='".$fields[6]."' ;				\n" ;
      echo "    d_name_i             ='".$fields[7]."' ;				\n" ;
      echo "    d_name_o             ='".$fields[8]."' ;				\n" ;

      echo "    msg_done             ='".$fields[9]."' ;				\n" ;

//--------------------------- ����������� ����� ��������

  if(isset($details)) 
  {
//- - - - - - - - - - - - - - ������� � ��������� � ���������
            $message_=$db->real_escape_string($message) ;

                        $sql ="Update messages ".
                              "   Set `done`='Y' " ;

     if(strpos($action, "read")!==false)
                        $sql.="      ,`read`='Y' " ;

                        $sql.=" Where receiver='$user_'".
			      "  and  id      = $message_" ;
        $res=$db->query($sql) ;
     if($res===false) {
              FileLog("ERROR", "Update MESSAGES... : ".$db->error) ;
                                $db->close() ;
             ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ��������� ������� ���������") ;
                             return ;
       }
//- - - - - - - - - - - - - -
             $db->commit() ;

//              SuccessMsg() ;
  }  
//--------------------------- �������������� ���������

  if($action!="none")
  {
//- - - - - - - - - - - - - - ������ ������
     if(!isset($details)) 
     {
          echo "  execute='first' ;  \n" ;
     }
//- - - - - - - - - - - - - - ������ ������
     else 
     {
          echo "  execute='second' ;  \n" ;
     }
//- - - - - - - - - - - - - -
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

function SuccessMsg() {

    echo  "i_error.style.color='green' ;		\n" ;
    echo  "i_error.innerHTML  ='������ ������������!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Message details reject alert</title>
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
    var  i_accept ;
    var  i_error ;
    var  password ;
    var  message_id ;
    var  sender_user ;
    var  execute ;


  function FirstField() 
  {
    var  receiver_key ;
    var  text ;
    var  details ;
    var  words_1 ;
    var  words_2 ;
    var  d_name_f ;
    var  d_name_i ;
    var  d_name_o ;


       i_msg_sender =document.getElementById("MsgSender") ;
       i_msg_sent   =document.getElementById("MsgSent") ;
       i_msg_text   =document.getElementById("MsgText") ;
       i_accept     =document.getElementById("Accept") ;
       i_error      =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

             execute="none" ;

<?php
            ProcessDB() ;
?>

       i_msg_sender.innerHTML=d_name_f+" "+d_name_i+" "+d_name_o ;

    if(execute=="first" ) {  SendFields() ; document.forms[0].submit() ;  }
    else
    if(execute=="second")  parent.frames['section'].ProcessNext() ;
    else
    {
        if(msg_done=='Y')  i_accept.hidden=true ;
    }

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;
     var  details ;

        error_text="" ;

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

	parent.parent.frames['view'].location.assign("messages_chat_lr_wrapper.php?Session="+v_session+"&Sender="+sender_user) ;
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

  <div class="Normal_CT"><b>����(����������) �������� ���� �����������</b></div>
  <br>
  <div class="Normal_CT">
    <div id="MsgSender"></div>
    <input type="button" value="��� ���?"  onclick=GoToView()>
    <input type="button" value="���������" onclick=GoToChat()>
  </div>
  <br>
  <div class="Normal_CT" id="MsgSent"> </div>
  <br>         
  <em><div class="Normal_CT" id="MsgText"></div>

  <div class="Normal_CT">
    <input type="submit" id="Accept" value="�������">
  </div>
  
  </form>

</body>

</html>
