<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Help.php" ;

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
//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ������ ������ �������

                     $sql="Select id, date(created), title, notes".
                          "  From releases".
                          " Order by created desc" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select RELEASES... : ".$db->error) ;
          InfoMsg("������ �� �������. <br>������: ������ ��������� ������ �������") ;
  }
  else {

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   AddRelease(".$fields[0].", '".$fields[1]."', '".$fields[2]."', '".$fields[3]."') ;	\n" ;
     }

                    $res->free() ;
  }

//--------------------------- ����������

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
    echo  "i_error.innerHTML  ='����������� ������� ��������!' ;	\n" ;
}
//============================================== 

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Help</title>
<meta http-equiv="Content-Type" content="text/html" charset="windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
?>

    var  i_releases ;
    var  i_error ;    

  function FirstField() 
  {
       i_releases=document.getElementById("Releases") ;
       i_error   =document.getElementById("Error") ;

<?php
            RegistryDB() ;
?>

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


        error_text="" ;
     
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
        <b>������ � ����������</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p>� ������� ����� ���� ������ �������� ��������� 2 ������, ������������ ��������� <font color=red>[?]</font> � <font color=red>[!]</font></p>
  <p>��� ������� �� ������ � �������� <font color=red>[?]</font> � ��������� ������� ��������� ���� � ����������� �� ������������� ������� ���������� �����.</p>
  <p>��� ������� �� ������ � �������� <font color=red>[!]</font> � ��������� ������� ��������� ���� ��� ������������ ���������, �������
     ����� ���������� ������������� ������� � � ������� �� ������ �������� ���� ��������� � �����������.
     <br>      
     ����������� � ��������� ������ ������������� ����� ����������� �� ������� �������� ���� <b>"��������� �������������"</b></p>
  <p>���� ��������� ���������, � ������� ������ ���� ������� ������ ������������ � ��������, � ����������� �� ��� ����:</p>

  <ul class="menu">
    <li><a href="tutorial\DarkMed_doctor.docx"  target="section">����������� ������������ ��� ����� (docx-����)</a></li> 
    <li><a href="tutorial\DarkMed_client.docx"  target="section">����������� ������������ ��� �������� (docx-����)</a></li> 
  </ul>

  <p>����� ����, ������������ ���������� �� ������������� �������:</p>

  <ul class="menu">
    <li><a href="tutorial\Doctor_1_prepare.mp4"      target="section">��������� ����� - �������� ������, ������ ������, ���������� ���������� ���������� (mp4-����)</a></li> 
    <li><a href="tutorial\Doctor_2_appointment.mp4"  target="section">��������� ����� - �������� ������, ����� �������� � ����������� ���������� (mp4-����)</a></li> 
    <li><a href="tutorial\Client_1.mp4"              target="section">��������� �������� - �������� ������ (mp4-����)</a></li> 
    <li><a href="tutorial\Client_mobile_1.mp4"       target="section">��������� �������� - ��������� ������ (mp4-����)</a></li> 
  </ul>

  <div class="error" id="Error"></div>

  <br>
  <form onsubmit="return SendFields();" method="POST">

  <div class="fieldC"id="Releases_intro"> 
    <b>�������� ��������� ����������(�������).</b>
    <br>
    ��� ��������� ���������� ���������� �������� �� ��� �������� - �������� ��������� � �������� �������.
    <br>
  </div>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Releases">
      <tr> 
       <td width="12%"> </td>
       <td> </td>
      </tr> 
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
