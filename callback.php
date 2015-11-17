<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Callback.php" ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ������ � ��

function RegistryDB() {

//--------------------------- ���������� ������������

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         return ;
  }
//--------------------------- ���������� � ������ ����������

	   $session =$_GET ["Session" ] ;
	   $form    =$_GET ["Form"    ] ;
	   $category=$_POST["Category"] ;
	   $message =$_POST["Message" ] ;

   FileLog("START", "    Session:".$session) ;
   FileLog("",      "       Form:".$form) ;
   FileLog("",      "   Category:".$category) ;
   FileLog("",      "    Message:".$message) ;

    if(!isset($category) ) return ;

//--------------------------- ����� ������ �� �����

    echo     "i_category.value   ='".$category."' ;	\n" ;
    echo     "i_message .value   ='".$message ."' ;	\n" ;

    echo     "i_category.disabled= true ;		\n" ;
    echo     "i_message .disabled= true ;		\n" ;
    echo     "i_save    .disabled= true ;		\n" ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ������������� ������

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false)  $user="Unknown" ;

//--------------------------- ����������� ���������

    $session =$db->real_escape_string($session) ;
    $user    =$db->real_escape_string($user) ;
    $form    =$db->real_escape_string($form) ;
    $category=$db->real_escape_string($category) ;
    $message =$db->real_escape_string($message) ;

                       $sql="Insert into ".
			    " callback_msg(  session,    user,    form,    category,    message, status)".
                            "       Values('$session', '$user', '$form', '$category', '$message', 'NEW')" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Insert CALLBACK_MSG... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ����������� ���������") ;
                         return ;
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

    echo  "i_error.style.color=\"red\" ;	\n" ;
    echo  "i_error.innerHTML  =\"".$text."\" ;	\n" ;
}

//============================================== 
//  ������ ��������� �� �������� �����������

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;				\n" ;
    echo  "i_error.innerHTML  =\"��������� ������� ����������������\" ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed CallBack Message</title>
<meta http-equiv="Content-Type" content="text/html" charset="windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
?>

    var  i_category ;
    var  i_message ;
    var  i_error ;    
    var  i_save ;

  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;


       i_category=document.getElementById("Category") ;
       i_message =document.getElementById("Message") ;
       i_error   =document.getElementById("Error") ;
       i_save    =document.getElementById("Save") ;

       i_category.focus() ;

<?php
            RegistryDB() ;
?>

       i_message.value=i_message.value.replace(nl,"\n") ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;
     var  nl=new RegExp("\n","g") ;


        error_text="" ;
     
     if(i_category.value=="dummy")  error_text="�� �����a ���� ���������" ;

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

          i_message.value=i_message.value.replace(nl,"@@") ;

                   return true ;         
  } 

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
        <input type="button" value="!" onclick=GoToCallBack() id="GoToCallBack" disabled> 
      </td> 
      <td class="title"> 
        <b>����� �������� �����</b>
      </td> 
    </tr>
    </tbody>
  </table>

<div class="fieldC">

  <div><br></div>
  <div>�� ������ ������� ����� ���� ����������� � ��������� �� ������ �������</div>
  <div><br></div>

  <form onsubmit="return SendFields();" method="POST">

  <div class="error" id="Error"></div>

  <div>
  ���� ���������
  <select name="Category" id="Category">
    <option value="dummy" selected> -- ������� ���� ��������� -- </option>
    <option value="Form design"> ������ � ����������� ����� </option>
    <option value="Workflow"   > ����������� �� ����������� ������� � ����� </option>
    <option value="Dictionary" > ���������� ������� �������� � ������������ </option>
    <option value="Error   "   > ������ � ������ ������� </option>
    <option value="Callback"   > ����� �������� ����� </option>
    <option value="Other"      > ������ </option>
  </select>
  </div>

  <br>
  <textarea cols=60 rows=7 wrap="soft" name="Message" id="Message"> </textarea>

  <br>
  <br>
  <input type="submit" value="��������� ���������" id="Save">

  </form>

</div>

</body>

</html>
