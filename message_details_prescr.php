<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Message_details_prescr.php" ;

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

                        $details=$_POST["Details"] ;
                        $check  =$_POST["Check"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "    Message:".$message) ;
  FileLog("",      "    Details:".$details) ;
  FileLog("",      "    Check  :".$check  ) ;

//--------------------------- ����������� ��
     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ������������� ������

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
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
                          "       d.name_f, d.name_i, d.name_o".
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

      echo "    message_id           =\"".$fields[0]."\" ;				\n" ;
      echo "    sender_user          =\"".$fields[1]."\" ;				\n" ;
      echo "    sender_key           =\"".$fields[5]."\" ;				\n" ;
      echo "    details              =\"".$fields[4]."\" ;				\n" ;
      echo "    details              =Sign_decode(details, sender_key, receiver_key) ;	\n" ;

      echo "  i_msg_sender .innerHTML=\"".$fields[1]."\" ;				\n" ;
      echo "  i_msg_sent   .innerHTML=\"".$fields[2]."\" ;				\n" ;
      echo "    text                 =\"".$fields[3]."\" ;				\n" ;
      echo "    text                 =Sign_decode(text, sender_key, receiver_key) ;	\n" ;
      echo "  i_msg_text   .innerHTML= text ;						\n" ;

      echo "    d_name_f             =\"".$fields[6]."\" ;				\n" ;
      echo "    d_name_i             =\"".$fields[7]."\" ;				\n" ;
      echo "    d_name_o             =\"".$fields[8]."\" ;				\n" ;

//--------------------------- ����������� ����� ��������

  if(isset($details)) 
  {
        $owner_=$db->real_escape_string($owner) ;
        $check_=$db->real_escape_string($check) ;
//- - - - - - - - - - - - - - ������� �������, �� ������� ������������ ������
	$words=explode(" ", $details) ;

    for($i=0 ; $i<count($words) ; $i=$i+2)
    {
       if($words[$i]=="")  break ;

          $page   =$words[$i  ] ;
          $key    =$words[$i+1] ;
          $page_  =$db->real_escape_string($page) ;
          $key_   =$db->real_escape_string($key) ;
//- - - - - - - - - - - - - - �������� ���������� ������� �������
                       $sql="Select page ".
                            "from  `access_list` ".
                            "where `Owner`='$user_'".
                            " and  `Login`='$user_'".
                            " and  `Page` ='$page_'" ;
        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                       $db->rollback() ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ���������� �������") ;
                         return ;
     }
     if($res->num_rows!=0) {
        FileLog("", "Access already granted: ".$user.":".$page." for ".$user) ;
                                 continue ;
     }
			      $res->free() ;
//- - - - - - - - - - - - - - �������� ������ � �������
                       $sql="Insert into `access_list`".
                            "(`Owner`, `Login`, `Page`,  `Crypto`) ".
                            "values".
                            "('$user_','$user_','$page_','$key_')" ;
        $res=$db->query($sql) ;
     if($res===false) {
               FileLog("ERROR", "Insert ACCESS_LIST... : ".$db->error) ;
                       $db->rollback() ;
                       $db->close() ;
              ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ � ���� ������") ;
                           return ;
     }

        FileLog("", "Access successfully granted: ".$user.":".$page." for ".$user) ;
//- - - - - - - - - - - - - - ������� �������, �� ������� ������������ ������
    }
//- - - - - - - - - - - - - -
             $db->commit() ;

              SuccessMsg() ;
  }
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
//  ������ ��������� �� �������� �����������

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    " ;
    echo  "i_error.innerHTML  =\"������ ������������!\" ;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Message details prescription alert</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_msg_sender ;
    var  i_msg_sent ;
    var  i_msg_text ;
    var  i_details ;
    var  i_check ;
    var  i_error ;
    var  password ;
    var  message_id ;
    var  sender_user ;
    var  a_pages_keys ;

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
       i_error      =document.getElementById("Error") ;
       i_details    =document.getElementById("Details") ;
       i_check      =document.getElementById("Check") ;

       password=TransitContext("restore", "password", "") ;

        a_pages_keys=new Array() ;

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
      }
    }

       i_msg_sender.innerHTML=d_name_f+" "+d_name_i+" "+d_name_o ;

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

  <table border="0" width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr class="fieldL">
      <td width="20%">
         <b><dev>������� ����������</dev></b><br>
         <dev id="MsgSender"> </dev><br>
         <dev id="MsgSent"> </dev><br>
         <input type="submit" value="�������">
         <input type="button" value="��� ���?" onclick=GoToView()></td>
      </td>
      <td width="2%">
      </td>
      <td width="73%">
         <div id="MsgText"></div>
      </td>
    </tr>
    </tbody>
  </table>

  <input type="hidden" name="Details" id="Details">
  <input type="hidden" name="Check"   id="Check">

  </form>

</body>

</html>
