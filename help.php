<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Help.php" ;
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

//-->
</script>

</head>

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
    <li><a href="DarkMed_doctor.docx"  target="section">����������� ������������ ��� ����� (docx-����)</a></li> 
    <li><a href="DarkMed_client.docx"  target="section">����������� ������������ ��� �������� (docx-����)</a></li> 
  </ul>


</div>

</body>

</html>
