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
        <b>ПОМОЩЬ И ИНСТРУКЦИИ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p>В верхнем левом углу каждой страницы находятся 2 кнопки, обозначенные символами <font color=red>[?]</font> и <font color=red>[!]</font></p>
  <p>При нажатии на кнопку с символом <font color=red>[?]</font> в отдельной вкладке откроется окно с информацией по использованию текущей диалоговой формы.</p>
  <p>При нажатии на кнопку с символом <font color=red>[!]</font> в отдельной вкладке откроется окно для формирования сообщения, которое
     будет направлено администрации портала и в котором Вы можете изложить свои замечания и предложения.
     <br>      
     Предложения и замечания других пользователей можно просмотреть на вкладке главного меню <b>"Сообщения пользователей"</b></p>
  <p>Ниже приведены документы, в которых описан весь процесс работы пользователя с порталом, в зависимости от его роли:</p>

  <ul class="menu">
    <li><a href="DarkMed_doctor.docx"  target="section">Руководство пользователя для врача (docx-файл)</a></li> 
    <li><a href="DarkMed_client.docx"  target="section">Руководство пользователя для пациента (docx-файл)</a></li> 
  </ul>


</div>

</body>

</html>
