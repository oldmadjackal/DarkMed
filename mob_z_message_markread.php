<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_z_message_markread.php" ;

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

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "    Message:".$message) ;

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

//--------------------------- ��������� �� ��������� ����� "���������"

       $message_=$db->real_escape_string($message) ;

                     $sql="Update messages ".
                          "   Set `read`='Y' ".
			  " Where receiver='$user_'".
			  "  and  id      = $message_" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Update MESSAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ��������� ������� ���������") ;
                         return ;
  }

             $db->commit() ;

              SuccessMsg() ;

//--------------------------- ����������

     $db->close() ;

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

    echo  "i_error.style.color='green' ;				\n" ;
    echo  "i_error.innerHTML  ='GP' ;					\n" ;
    echo  "i_text .value      ='��������� �������� ��� �����������' ;	\n" ;
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

  function FirstField() 
  {
       i_text =document.getElementById("Text" ) ;
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

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
