<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_measurement_check.php" ;

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
                           $owner=$_GET ["Owner"] ;
                            $page=$_GET ["Page"] ;
                       $reference=$_GET ["Reference"] ;

                           $value=$_POST["Value"] ;

    FileLog("START", "  Session:".$session) ;
    FileLog("",      "    Owner:".$owner) ;
    FileLog("",      "     Page:".$page) ;
    FileLog("",      "Reference:".$reference) ;
    FileLog("",      "    Value:".$value) ;

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
//--------------------------- ���������� ����������

          $owner_    =$db->real_escape_string($owner    ) ;
          $user_     =$db->real_escape_string($user     ) ;
          $page_     =$db->real_escape_string($page     ) ;
          $reference_=$db->real_escape_string($reference) ;

//--------------------------- ���������� ��������� ������

  if(isset($value) && $value!="")
  {
          $value_=$db->real_escape_string($value) ;
//- - - - - - - - - - - - - - ����������� �������������� ����������
                     $sql="Select id".
			  "  From client_pages".
                          " Where owner='$owner_'".
                          "  and  page = $page_" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES(ID)... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� �������������� ��������") ;
                         return ;
  }
  else
  {
	 $fields=$res->fetch_row() ;

	$page_id=$fields[0] ;
  }

     $res->close() ;
//- - - - - - - - - - - - - - ���������� ��������� ������
			   $sql="Insert into ".
				"measurements( page_id,  measurement_id, value   )".
				"      values($page_id, $reference_,    '$value_')" ;
	   $res=$db->query($sql) ;
	if($res===false) {
               FileLog("ERROR", "Insert MEASUREMENTS... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ � ���� ������") ;
                           return ;
        }

          $db->commit() ;
//- - - - - - - - - - - - - - ����������� �� �����
      echo     "  i_value.value   ='".$value."' ;	\n" ;
      echo     "  i_value.disabled= true ;		\n" ;
      echo     "  i_save .hidden  = true ;		\n" ;

        FileLog("", "Measurements values saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- ���������� ����� ��������

                       $sql="Select crypto".
                            "  From access_list".
                            " Where owner    ='$owner_' ".
                            "  and  login    ='$user_' ".
                            "  and  page     = $page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select ACCESS_LIST...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ����������� ����� �������") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_num  ='" .$page."' ;		\n" ;
      echo     "   page_owner='" .$owner."' ;		\n" ;
      echo     "   page_key  ='" .$fields[0]."' ;	\n" ;

//--------------------------- ���������� ������ ����������

                     $sql="Select name, remark".
			  "  From prescriptions_pages".
                          " Where owner    ='$owner_'".
                          "  and  page     = $page_".
                          "  and  reference= $reference_" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_PAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ������ ����������") ;
                         return ;
  }
  else
  {  
	      $fields=$res->fetch_row() ;

       echo "   v_name  ='".$fields[0]."' ;	\n" ;
       echo "   v_remark='".$fields[1]."' ;	\n" ;
  }

     $res->close() ;

//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;				\n" ;
}

//============================================== 
//  ������ ��������������� ��������� �� WEB-��������

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

<title>DarkMed-Mobile Measurement check</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  v_name ;
    var  v_remark ;
    var  i_value ;
    var  i_save ;
    var  i_error ;
    var  page_owner ;
    var  page_num ;

  function FirstField() 
  {
    var  password ;
    var  page_key ;
    var  m_prefix ;
    var  pos ;


       i_value=document.getElementById("Value") ;
       i_save =document.getElementById("Save") ;
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;

       page_key= Crypto_decode( page_key, password) ;

             v_name  =Crypto_decode(v_name  , page_key) ;
             v_remark=Crypto_decode(v_remark, page_key) ;

       document.getElementById("Name"  ).innerHTML=v_name ;
       document.getElementById("Remark").innerHTML=v_remark ;

         i_value.focus() ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

	 error_text=""

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;
  } 

  function GoBack()
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

      location.assign("mob_client_prescr_view.php?Session="+v_session+"&Owner="+page_owner+"&Page="+page_num) ;
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
        <b>���� ������������ ���������</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <div class="error" id="Error"></div>
  <form onsubmit="return SendFields();" method="POST" id="Form">

  <div class="fieldC" width="20%">
    <b><div id="Name"></div></b>
       <br>
       <div id="Remark"></div>
       <br>
       <br>
       <input type="text" size=20 name="Value" id="Value"> 
       <br>
       <br>
       <input type="submit" class="G_bttn" value="���������" id="Save">
       <br>
       <br>
       <br>
       <input type="button" class="R_bttn" value="���������" id="Cancel" onclick=GoBack()>
  </div>

  </form>

</body>

</html>
