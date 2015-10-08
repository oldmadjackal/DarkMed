<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Callback_list.php" ;

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

  FileLog("START", "    Session:".$session) ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ������������� ������

  if(isset($session)) {

       $user=DbCheckSession($db, $session, $options, $error) ;
    if($user===false) {
                       ErrorMsg($error) ;
                         return ;
    }

       $user_=$db->real_escape_string($user ) ;
  }
//--------------------------- ������������ ������ ���������

                     $sql="Select created, category, message, status, remark".
			  "  From callback_msg".
			  " Order by created desc" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CALLBACK_MSG... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ������ ���������") ;
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

       echo "  AddNewMessage('$fields[0]','$fields[1]','$fields[2]','$fields[3]','$fields[4]') ;	\n" ;
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

<title>DarkMed Messages InBox</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_messages ;
    var  i_error ;
    var  password ;

  function FirstField() 
  {
       i_messages=document.getElementById("Messages") ;
       i_error   =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function SendFields() 
  {
         return true ;
  } 

  function AddNewMessage(p_created, p_category, p_message, p_status, p_remark)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  nl=new RegExp("@@","g") ;


       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_created) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_category) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_message) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . innerHTML=i_col_new.innerHTML.replace(nl,"<br>") ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_status) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_remark) ;
       i_col_new . className = "table" ;
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
        <b>��������� �������������</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p class="error" id="Error"></p>
  <table class="table" width="100%">
    <thead>
    </thead>
    <tbody id="Messages">
    </tbody>
  </table>

</div>

</body>

</html>
