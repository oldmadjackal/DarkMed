<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_invite_page.php" ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ��������������� � ��

function ProcessDB() {

  global  $sys_doc_count    ;
  global  $sys_doc_owner    ;
  global  $sys_doc_fio      ;
  global  $sys_doc_spec     ;
  global  $sys_doc_remark   ;
  global  $sys_doc_portrait ;
  global  $sys_doc_sign     ;
  global  $a_specialities   ;

//--------------------------- ���������� ������������

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         return ;
  }
//--------------------------- ���������� ����������

                          $session=$_GET ["Session"] ;
  if(!isset($session ))   $session=$_POST["Session"] ;

                             $page=$_GET ["Page"] ;
  if(!isset($page))          $page=$_POST["Page"] ;

                         $receiver=$_POST["Receiver"] ;
  if(isset($receiver))
  {
                           $letter=$_POST["Letter"] ;
                           $invite=$_POST["Invite"] ;
                           $incopy=$_POST["InCopy"] ;
  }

           FileLog("START", "  Session:".$session) ;
           FileLog("",      "     Page:".$page) ;

    if(isset($receiver))
    {
           FileLog("",      " Receiver:".$receiver) ;
           FileLog("",      "   Letter:".$letter) ;
           FileLog("",      "   Invite:".$invite) ;
           FileLog("",      "   InCopy:".$incopy) ;
    }
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

          $page_=$db->real_escape_string($page) ;
          $user_=$db->real_escape_string($user) ;

//--------------------------- ���������� ������ � ���������

  if(isset($receiver))
  {
          $receiver=$db->real_escape_string($receiver) ;
          $letter  =$db->real_escape_string($letter) ;
          $invite  =$db->real_escape_string($invite) ;
          $incopy  =$db->real_escape_string($incopy) ;

                       $sql="Insert into messages(Receiver,Sender,Type,Text,Details,Copy)".
                            " values('$receiver','$user','CLIENT_ACCESS_INVITE','$invite','$letter','$incopy')" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ � ���� ������ 3") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "Access message saved successfully") ;

          echo  "  location.assign('mob_client_page.php?Session=".$session."&Page=".$page."') ;	\n" ;

     return ;
  }
//--------------------------- ������������ ������ ��������������

                     $sql="Select code, name".
			  "  From ref_doctor_specialities".
			  " Where language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_DOCTOR_SPECIALITIES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ����������� ��������������") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

               $a_specialities[$fields[0]]=$fields[1] ;
     }
  }

     $res->close() ;

//--------------------------- ���������� ������ ������

                     $sql ="Select d.owner, CONCAT_WS(' ', d.name_f, d.name_i, d.name_o), d.speciality, d.remark, d.portrait, u.sign_p_key".
                           "  From doctor_page_main d inner join users u on d.owner=u.login".
                           " Where d.confirmed='Y'" ;
                     $sql.="  and  d.owner in (select distinct m.receiver from messages m where m.sender='$user_')" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������") ;
                         return ;
  }

          $sys_doc_count=$res->num_rows ;

      echo  "  doctors_cnt=".$res->num_rows." ;	\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

          $sys_doc_owner   [$i]= $fields[0] ;
          $sys_doc_fio     [$i]= $fields[1] ;
          $sys_doc_spec    [$i]= $fields[2] ;
          $sys_doc_remark  [$i]= $fields[3] ;
          $sys_doc_portrait[$i]= $fields[4] ;
          $sys_doc_sign    [$i]= $fields[5] ;
     }

     $res->close() ;

//--------------------------- ���������� ������ ��������

                     $sql="Select a.crypto, a.ext_key, u.sign_s_key, u.msg_key".
			  "  From access_list a, users u".
			  " Where a.owner=u.login".
			  "  and  a.owner='$user_'".
			  "  and  a.login='$user_'".
			  "  and  a.page = $page" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ����� ������� ��������") ;
                         return ;
  }
  else
  {      
	      $fields=$res->fetch_row() ;

       echo "    page	       ='".$page."' ;				\n" ;
       echo "    page_key      ='".$fields[0]."' ;			\n" ;
       echo "    page_key      =Crypto_decode(page_key, password) ;	\n" ;

       echo "    file_key      ='".$fields[1]."' ;			\n" ;
       echo "    file_key      =Crypto_decode(file_key, password) ;	\n" ;

       echo "    sender_key    ='".$fields[2]."' ;			\n" ;
       echo "    sender_key    =Crypto_decode(sender_key, password) ;	\n" ;

       echo "       msg_key    ='".$fields[3]."' ;			\n" ;
       echo "       msg_key    =Crypto_decode(msg_key, password) ;	\n" ;
  }

     $res->close() ;



//--------------------------- �����������

//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ����������� �������������� ������ ��������

function ShowDoctors() {

  global  $sys_doc_count    ;
  global  $sys_doc_owner    ;
  global  $sys_doc_fio      ;
  global  $sys_doc_spec     ;
  global  $sys_doc_remark   ;
  global  $sys_doc_portrait ;
  global  $sys_doc_sign     ;
  global  $a_specialities   ;


  for($i=0 ; $i<$sys_doc_count ; $i++)
  {
        $row =               $i ;
        $user=$sys_doc_owner[$i] ;
        $text=str_replace("@@", "<br>", $sys_doc_remark[$i]) ;
        $spec=$sys_doc_spec[$i] ;

    foreach($a_specialities as $code => $name)  $spec=str_replace($code, $name, $spec) ;
                                                $spec=substr($spec, 0, strlen($spec)-1) ;
                                                $spec=str_replace(",", ", ", $spec) ;

       echo  "  <tr class='table' id='Row_".$row."' onclick=SelectDoctor('".$row."')>		\n" ;
       echo  "    <td class='table' width='10%'>						\n" ;

    if($sys_doc_portrait[$i]!="") 
    {
       echo "<div class='fieldC'>								\n" ;
       echo "<img src='".$sys_doc_portrait[$i]."' height=200>					\n" ;
       echo "</div>										\n" ;
    }
       echo  "      <input type='hidden' id='Login_".$row."' value='".$user."'>			\n" ;
       echo  "      <input type='hidden' id='Sign_" .$row."' value='".$sys_doc_sign[$i]."'>	\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='table'>								\n" ;
       echo  "      <div><b>".$sys_doc_fio[$i]."</b></div>					\n" ;
       echo  "      <div>".$spec."</div>							\n" ;
       echo  "    </td>										\n" ;
       echo  "  </tr>										\n" ;

  }

}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;\n" ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
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

<title>DarkMed Doctors List</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_receiver ;
    var  i_letter ;
    var  i_incopy ;
    var  i_invite ;
    var  i_error ;
    var  password ;
    var  page ;
    var  page_key ;
    var  file_key ;
    var  doctors_cnt ;
    var  doctor_key ;

  function FirstField() 
  {

	i_receiver=document.getElementById("Receiver") ;
	i_letter  =document.getElementById("Letter") ;
	i_incopy  =document.getElementById("InCopy") ;
	i_invite  =document.getElementById("Invite") ;
	i_error   =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


	error_text=""

	i_error.style.color="red" ;
	i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

       i_letter.value=page+":"+page_key+":"+file_key+";" ;

       i_incopy.value=Crypto_encode(i_invite.value, msg_key) ;
       i_letter.value=  Sign_encode(i_letter.value, sender_key, doctor_key) ;
       i_invite.value=  Sign_encode(i_invite.value, sender_key, doctor_key) ;

                         return true ;
  } 

  function SelectDoctor(p_row) 
  {
     for(i=0 ; i<doctors_cnt ; i++)
       if(i!=p_row)  document.getElementById("Row_"+i).hidden=true ;
       else         {
			i_receiver.value=document.getElementById("Login_"+i).value ;
			    doctor_key  =document.getElementById("Sign_" +i).value ;
                    }

	document.getElementById("DoctorsText").hidden=true ;
	document.getElementById("Invitation" ).hidden=false ;     
  }

  function ResetDoctors() 
  {
     for(i=0 ; i<doctors_cnt ; i++)
         document.getElementById("Row_"+i).hidden=false ;

	document.getElementById("DoctorsText").hidden=false ;
	document.getElementById("Invitation" ).hidden=true ;     
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

  <table width="90%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td width="10%"> 
        <input type="button" value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" value="!" hidden onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b>�����������</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <p class="error" id="Error"></p>

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form">

  <div id="DoctorsText">�������� �����:</div>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Doctors">

<?php
            ShowDoctors() ;
?>

    </tbody>
  </table>

  <table width="100%" hidden id="Invitation">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="fieldC"> <br> <input type="button" value="������� ������� �����������" onclick=ResetDoctors()> </td>
    </tr>
    <tr>
      <td class="fieldC">
        <br>
        ���������������� ���� 
        <br>
        <textarea cols=32 rows=7 wrap="soft" name="Invite" id="Invite"></textarea>
      </td>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="submit" class="G_bttn" value="��������� �����������"> </td>
    </tr>
    </tbody>
  </table>

	<input type="hidden" name="Receiver" id="Receiver" >
	<input type="hidden" name="Letter"   id="Letter" >
	<input type="hidden" name="InCopy"   id="InCopy" >

  </form>

</div>

</body>

</html>
