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

    echo     "   i_login.value='" .$login   ."' ;	\n" ;
    echo     "i_password.value='" .$password."' ;	\n" ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ����������� ������������

   $login   =$db->real_escape_string($login   ) ;
   $password=$db->real_escape_string($password) ;

                     $sql="Select options from users Where Login='$login' and Password='$password'" ;
     $res=$db->query($sql) ;
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

	      $fields=$res->fetch_row() ;
             $options=$fields[0] ;

                    $res->free() ;

//--------------------------- ��������� ��������� ������������

    $options_a=OptionsToArray($options) ;

     $user_type=$options_a["user"] ;

//--------------------------- ����������� ������

           $session=GetRandomString(16) ;

                     $sql="Insert into sessions(Login, Session) values('$login','$session')" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Insert SESSION... : ".$db->error) ;
                     $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ � ���� ������") ;
                         return ;
  }

     $db->commit() ;

        FileLog("", "Session record successfully inserted") ;

//--------------------------- �������� ������ ������

                     $sql="Delete from sessions where started<Date_Sub(Now(), interval 24 hour)" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Insert DELETE... : ".$db->error) ;
          InfoMsg("������ �� �������. <br>������: ������ ������� ������� ������") ;
  }

     SuccessMsg($session) ;

//--------------------------- ������ ������ ������������� �������

                     $sql="Select id, date(created), title, notes, user".
                          "  From releases r left outer join releases_read m on r.id=m.release_id and m.user='$login'".
                          " Where user is null".
                          "  and (types like '%$user_type%' or types is null)".
                          " Order by created" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select RELEASES... : ".$db->error) ;
          InfoMsg("������ �� �������. <br>������: ������ ��������� ������ �������") ;
  }
  else {

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "      i_rl_intro.hidden=false ;								\n" ;
       echo "   AddRelease(".$fields[0].", '".$fields[1]."', '".$fields[2]."', '".$fields[3]."') ;	\n" ;
     }

                    $res->free() ;
  }

//--------------------------- ������ ������� ������������� ���������

                    $msg_flag="0" ;

                     $sql="Select count(*)".
                          "  From messages".
                          " Where receiver='$login'".
                          "  and  `read` is null" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
          InfoMsg("������ �� �������. <br>������: ������ ���������� ������� ������������� ���������") ;
  }
  else {
	      $fields=$res->fetch_row() ;

                $msg_flag=$fields[0] ;
           
                    $res->free() ;
  }
//--------------------------- ��������� ������������ �������� ����

        if($user_type=="Doctor"  )  echo  "parent.frames['menu'].ShowDoctor() ; " ;
   else if($user_type=="Executor")  echo  "parent.frames['menu'].ShowExecutor() ; " ;
   else                             echo  "parent.frames['menu'].ShowClient() ; " ;

//--------------------------- �������������� ������� �� �����

   if($msg_flag=="0")
   {
      if($user_type=="Doctor"  ||
         $user_type=="Executor"  )
      {
      }
      else
      {
          echo  "  location.assign('client_card.php'+'?Session=".$session."') ;	\n" ;
      }
   }
   else
   {
          echo  "  location.assign('messages.php'+'?Session=".$session."') ;	\n" ;
   }
//--------------------------- ����������

     $db->commit() ;
     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  ������ ��������� �� �������� �����������

function SuccessMsg($session) {

    echo  "TransitContext('save', 'session', '".$session."') ;	\n" ;

    echo  "i_error.style.color='green' ;				\n" ;
	
	echo "
     var  v_session = TransitContext('restore','session','') ; 
	 parent.frames['title'].changeHiddenAuthBtns(v_session); ";

    echo  "i_error.innerHTML  ='����������� ������� ��������!' ;	\n" ;
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

    var  i_table ;
    var  i_login ;
    var  i_password ;
    var  i_releases ;
    var  i_rl_intro ;
    var  i_error ;    

  function FirstField() 
  {
       i_table   =document.getElementById("Fields") ;
       i_login   =document.getElementById("Login") ;
       i_password=document.getElementById("Password") ;
       i_releases=document.getElementById("Releases") ;
       i_rl_intro=document.getElementById("Releases_intro") ;
       i_error   =document.getElementById("Error") ;

       i_login.focus() ;

<?php
            RegistryDB() ;
?>

       i_password.value=TransitContext("restore", "password", "") ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

       i_table.rows[0].cells[0].style.color="black"   ;
       i_table.rows[1].cells[0].style.color="black"   ;

        error_text="" ;
     
     if(i_login.value=="") {
       i_table.rows[0].cells[0].style.color="red"   ;
             error_text=error_text+"<br>�� ������ ���� '�����'" ;
     }

     if(i_password.value=="") {
       i_table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>�� ������ ���� '������'" ;
     }

     if(error_text=="") {

       TransitContext("save", "password", i_password.value) ;

	i_password.value=MD5(i_password.value) ;
	i_password.value=i_password.value.substr(1,4) ;
     }

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

 
      return true ;         
  } 

  function AddRelease(p_id, p_date, p_name, p_link)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_lnk_new ;
     var  i_shw_new ;


       i_row_new = document.createElement("tr") ;
       i_row_new . id     ='Release_'+p_id ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "field" ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="���������" ;
       i_shw_new . id     ='Mark_'+p_id ;
       i_shw_new . onclick= function(e) {  MarkRead(p_id) ;  }
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "fieldC" ;
       i_txt_new = document.createTextNode(p_date) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "fieldL" ;
       i_lnk_new = document.createElement("a") ;
       i_lnk_new . href="#" ;
       i_lnk_new . onclick= function(e) {
					   window.open("releases/"+p_link) ;
					} ;
       i_txt_new = document.createTextNode(p_name) ;
       i_lnk_new . appendChild(i_txt_new) ;
       i_col_new . appendChild(i_lnk_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_releases. appendChild(i_row_new) ;

    return ;         
  }

  function MarkRead(p_id)
  {
    var  i_release ;
    var  v_session ;

         i_release=document.getElementById("Release_"+p_id) ;
         i_release.style.textDecoration="line-through" ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["details"].location.assign("z_release_markread.php?Session="+v_session+
                                                                   "&Release="+p_id) ;
  }

//-->
</script>


</head>

<body onload="FirstField();">

<noscript>
</noscript>

<dev class="inputF">

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
      <td> <input type="password" size=20 name="Password" id="Password"> </td>
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

  <br>
  <div class="fieldC" hidden id="Releases_intro"> 
    <b>��� ������� �������� ����������.</b>
    <br>
    ��� ��������� ���������� ���������� �������� �� ��� �������� - �������� ��������� � �������� �������.
    <br>
    ������ ������ ���������� ����� ����������� �� ������� "��� ������������ ��������?".
  </div>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Releases">
      <tr> 
       <td width="12%"> </td>
       <td width="12%"> </td>
       <td> </td>
      </tr> 
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
