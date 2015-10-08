<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Logon.php" ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ��������������� ������ � ��

function RegistryDB() {

//--------------------------- ���������� ������������

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         return ;
  }
//--------------------------- ���������� � ������ ����������

   $login   =$_POST["Login"   ] ;
   $password=$_POST["Password"] ;

     $completeness=0 ;
   
  if(isset($login   ))  $completeness++ ;
  if(isset($password))  $completeness++ ;

  if($completeness==0)  return ;

  if($completeness==0)  FileLog("START", "HandShake") ;
  else                  FileLog("START", "Login:".$login." Password:".$password) ;

//--------------------------- ����� ������ �� �����

    echo     "   v_login.value=\"" .$login   ."\" ;\n" ;
    echo     "v_password.value=\"" .$password."\" ;\n" ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ����������� ������������

   $login   =$db->real_escape_string($login   ) ;
   $password=$db->real_escape_string($password) ;

     $res=$db->query("Select * from `users` Where `Login`='$login' and `Password`='$password'") ;
  if($res===false) {
          FileLog("ERROR", "Select... : ".$db->error) ;
                     $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ������������") ;
                         return ;
  }
  if($res->num_rows==0) {
                    $res->free() ;
                     $db->close() ;
          FileLog("CANCEL", "Login or password failed") ;
         ErrorMsg("�������������� ������ � ������ ������������") ;
                         return ;
  }

                    $res->free() ;

//--------------------------- ����������� ������

           $session=GetRandomString(16) ;

     $res=$db->query("Insert into `sessions`(`Login`, `Session`) values('$login','$session')") ;
  if($res===false) {
          FileLog("ERROR", "Insert SESSION... : ".$db->error) ;
                     $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ � ���� ������") ;
                         return ;
  }
        FileLog("", "Session record successfully inserted") ;

//--------------------------- ����������

     $db->commit() ;
     $db->close() ;

     SuccessMsg($session) ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "error.style.color=\"red\" ;      \n" ;
    echo  "error.innerHTML  =\"".$text."\" ;\n" ;
}

//============================================== 
//  ������ ��������� �� �������� �����������

function SuccessMsg($session) {

    echo  "TransitContext(\"save\", \"session\", \"".$session."\") ; \n" ;

    echo  "error.style.color=\"green\" ;                       \n" ;
    echo  "error.innerHTML  =\"����������� ������� ��������!\" ;\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Logon</title>
<meta http-equiv="Content-Type" content="text/html" charset="windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
  require("md5.inc") ;
?>

    var  table ;    
    var  v_login ;
    var  v_password ;
    var  error ;    

  function FirstField() 
  {
         table   =document.getElementById("Fields") ;
       v_login   =document.getElementById("Login") ;
       v_password=document.getElementById("Password") ;
         error   =document.getElementById("Error") ;

       v_login.focus() ;

<?php
            RegistryDB() ;
?>

       v_password.value=TransitContext("restore", "password", "") ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

       table.rows[0].cells[0].style.color="black"   ;
       table.rows[1].cells[0].style.color="black"   ;

        error_text="" ;
     
     if(v_login.value=="") {
       table.rows[0].cells[0].style.color="red"   ;
             error_text=error_text+"<br>�� ������ ���� '�����'" ;
     }

     if(v_password.value=="") {
       table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>�� ������ ���� '������'" ;
     }

     if(error_text=="") {

       TransitContext("save", "password", v_password.value) ;

	v_password.value=MD5(v_password.value) ;
	v_password.value=v_password.value.substr(1,4) ;
     }

       error.style.color="red" ;
       error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                   return true ;         
  } 

//-->
</script>

</head>

<body onload="FirstField();">

<noscript>
</noscript>

<div class="inputF">

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
        <b>�����������</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> ����� </td>
      <td> <input type="text" size=20 name="Login" id="Login"> </td>
    </tr>
    <tr>
      <td class="field"> ������ </td>
      <td> <input type="text" size=20 name="Password" id="Password"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="�����"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
