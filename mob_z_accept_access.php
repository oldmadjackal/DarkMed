<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_z_accept_access.php" ;

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

                              $session=$_GET["Session"] ;
                              $message=$_GET["Message"] ;
                              $details=$_GET["Details"] ;          
  if(isset($_GET["Action"]))  $action =$_GET["Action"] ;

                      FileLog("START", "    Session:".$session) ;
                      FileLog("",      "    Message:".$message) ;
                      FileLog("",      "    Details:".$details) ;
  if(isset($action))  FileLog("",      "     Action:".$action) ;

//--------------------------- ���������

    if(!isset($action))  $action="none" ;

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

       $user_=$db->real_escape_string($user) ;

//--------------------------- ���������� ��������� ���������

       $message_=$db->real_escape_string($message) ;

                       $sql="Select type, sender".
                            "  From messages ".
	  		    " Where receiver='$user_'".
			    "  and  id      = $message_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������ ���������") ;
                         return ;
    }
    if($res->num_rows==0) {
          FileLog("ERROR", "No such message detected... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������.<br>������: ��������� �� �������") ;
                         return ;
    }
    
	      $fields=$res->fetch_row() ;
	              $res->close() ;

                  $msg_type=$fields[0] ;
                    $sender=$fields[1] ;

//--------------------------- �������� �� ���� ���������

   if($msg_type=="CLIENT_ACCESS_INVITE" ||
      $msg_type=="CLIENT_ACCESS_PAGES"    )
   {
           $owner=$sender ;
   }
   else
   if($msg_type=="CLIENT_PRESCRIPTIONS_ALERT")
   {
           $owner=$user_ ;
   }
   else
   if($msg_type=="CLIENT_INVITE_ACCEPT" ||
      $msg_type=="CLIENT_INVITE_REJECT"   )
   {
           $details="" ;
   }
   else
   {
          FileLog("ERROR", "Unknown message type detected... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������.<br>������: ���������������� ��� ���������") ;
                         return ;
   }

           $owner_=$owner ;
   
//--------------------------- �������� �� ������� ������ �� �����������

   if($msg_type=="CLIENT_ACCESS_PAGES")
   {
                       $sql="Select id ".
                            "from   messages ".
                            "where  Sender  ='$owner_'".
                            " and   Receiver='$user_'".
                            " and   Type    ='CLIENT_ACCESS_INVITE'".
                            " and   Done    ='R'" ;
        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
                       $db->rollback() ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ���������� �������") ;
                         return ;
     }
     if($res->num_rows!=0) {
          FileLog("", "Reject detected - access ignored") ;
                                 $details="" ;
                                 $action =$action."read" ;
     }
			      $res->free() ;
   }
//--------------------------- ����������� ����� ��������
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
                            "from  `access_list` ".
                            "where `Owner`='$owner_'".
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
			      $res->free() ;
        FileLog("", "Access already granted: ".$owner.":".$page." for ".$user) ;
                                 continue ;
     }
//- - - - - - - - - - - - - - �������� ������ � �������
                       $sql="Insert into access_list".
                            "(owner, login, page, crypto, ext_key) ".
                            "values".
                            "('$owner_','$user_','$page_','$key_1_','$key_2_')" ;
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
//--------------------------- �������� �������� ������� � �������
    do
    {
       if($details=="")  break ;
//- - - - - - - - - - - - - - �������� ������� �������� ������� � �������
                        $sql="Select client ".
                             "from   doctor_notes ".
                             "where  owner ='$user_'".
                             " and   client='$owner_'" ;
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
                       $sql="Insert into doctor_notes".
                            "(Owner, Client) ".
                            "values".
                            "('$user_','$owner_')" ;
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
//- - - - - - - - - - - - - -
//--------------------------- ������� � ��������� � ���������

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
//--------------------------- �������������� ���������

  if($action!="none")
  {
          echo "  execute='second' ;  \n" ;
  }
//--------------------------- ����������

             $db->commit() ;
	     $db->close() ;

              SuccessMsg() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='ER' ;		\n" ;
    echo  "i_text .value      ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  ������ ��������� �� �������� ����������

function SuccessMsg() {

    echo  "i_error.style.color='green' ;		\n" ;
    echo  "i_error.innerHTML  ='GP' ;			\n" ;
    echo  "i_text .value      ='������ ������������' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed=Mobile Message mark read processor</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script type="text/javascript">
<!--

    var  i_text ;
    var  i_error ;
    var  execute ;

  function FirstField() 
  {
       i_text =document.getElementById("Text" ) ;
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

    if(execute=="second")  parent.frames['section'].ProcessNext() ;

         return true ;
  }

  function ShowText() 
  {
	alert(i_text.value) ;
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

<b>
<div color="green" id="Error" onclick=ShowText()>
<font color="green">
GP
</font>
</div>
</b>

<input type="hidden" id="Text"> 

</body>

</html>
