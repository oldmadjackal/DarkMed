<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Callback_details.php" ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ��������������� � ��

function ProcessDB() {

   global  $glb_options_a ;

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
          $status =$_POST["Status"] ;
          $remark =$_POST["Remark"] ;


  FileLog("START", "Session :".$session) ;
  FileLog("",      "Message :".$message) ;
  FileLog("",      "Status  :".$status) ;
  FileLog("",      "Remark  :".$remark) ;

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

//--------------------------- ������ ���� ������������

    if(!isset($glb_options_a["support"]))
    {
          FileLog("ERROR", "Deny callback message reply forming for this user") ;
                            $db->close() ;
         ErrorMsg("� ������������ ��� ���� ��� ������ �� ���������") ;
                         return ;
    }
//--------------------------- ���������� ������

          $message_=$db->real_escape_string($message) ;

//--------------------------- ���������� ������ ��� �����������

  if(!isset($status))
  {
                       $sql="Select status, remark".
                            "  From callback_msg".
                            " Where id=$message_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select CALLBACK_MSG... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������ ���������") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $status=$fields[0] ;
                   $remark=$fields[1] ;

        FileLog("", "Doctor notes presented successfully") ;
  }
//--------------------------- ���������� ������ �� ��������
  else
  {
          $status_=$db->real_escape_string($status) ;
          $remark_=$db->real_escape_string($remark) ;

                       $sql="Update callback_msg".
                            "   Set status='$status_'".
                            "      ,remark='$remark_'".
                            " Where id=$message_" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update CALLBACK_MSG... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ � ���� ������") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "Reply saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- ����� ������ �� ��������

      echo     "  i_status.value='".$status."' ;	\n" ;
      echo     "  i_remark.value='".$remark."' ;	\n" ;

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

    echo  "i_error.style.color='green' ;			\n" ;
    echo  "i_error.innerHTML  ='������ ������� ���������!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed CallBack details</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_status ;
    var  i_remark ;
    var  i_error ;

  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;


       i_status=document.getElementById("Status") ;
       i_remark=document.getElementById("Remark") ;
       i_error =document.getElementById("Error") ;

       i_remark.focus() ;

<?php
            ProcessDB() ;
?>

       i_remark.value=i_remark.value.replace(nl,"\n") ;

         return true ;
  }

  function SendFields() 
  {
     var  i_client_cat ;
     var  error_text ;
     var  nl=new RegExp("\n","g") ;

	error_text=""
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

          i_remark.value=i_remark.value.replace(nl,"@@") ;

                         return true ;         
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

<div class="inputF">
  <form onsubmit="return SendFields();" method="POST">

  <table>
    <thead>
    </thead>
    <tbody>
      <tr>
        <td width="5%"> </td>
        <td>
          <div class="error" id="Error"></div>
        </td>
      </tr>
      <tr>
        <td width="5%"> </td>
        <td>
          <input type="submit" value="���������">
          <select id="Status" name="Status">
            <option value="�������������">�������������</option>
            <option value="����������"   >����������</option>
          </select>
        </td>
      </tr>
      <tr>
        <td width="5%"> </td>
        <td>
          <textarea cols=80 rows=3 maxlength=1000 wrap="soft" name="Remark" id="Remark"></textarea>
        </td>
      </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
