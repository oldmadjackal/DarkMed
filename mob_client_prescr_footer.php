<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Z_clear_tmp.php" ;

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
                        $wait   =$_GET ["Wait"] ;

  FileLog("START", "    Session:".$session) ;

 if(isset($wait))
  FileLog("",      "       Wait:".$wait) ;

//--------------------------- �������� �����

   if(isset($wait))  sleep($wait) ;

//--------------------------- ������� � �������� ��������� �����

        RemoveTmpFolder($session) ;

//--------------------------- ����������

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
//  ������ ��������� �� �������� ����������

function SuccessMsg() {

    echo  "i_error.style.color='green' ;				\n" ;
    echo  "i_error.innerHTML  ='����� ������� ��� �����������' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed-Mobile Client Page Footer</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script type="text/javascript">
<!--

    var  i_error ;

  function FirstField() 
  {
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

         return true ;
  }

  function GoToPages() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

     parent.frames['section'].location.assign("mob_client_prescr.php"+"?Session="+v_session) ;
  }

  function GoToMenu() 
  {
     parent.frames['section'].location.assign('mob_menu_client.php') ;
  }

  function GoToNextPage() 
  {
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

  <table width="100%">
    <thead>
    </thead>
    <tbody>
    <tr class="fieldC">
      <td width="34%"> 
       <input class="B_bttn" type="button" value="������" onclick=GoToPages()>
      </td> 
      <td> 
       <input class="G_bttn" type="button" value="����" onclick=GoToMenu()>
      </td> 
      <td width="34%">
       <input hidden class="R_bttn" type="button" value="������" onclick=parent.frames['section'].ShowExtActions(true)>
      </td> 
    </tr>
    </tbody>
  </table>


</body>

</html>
