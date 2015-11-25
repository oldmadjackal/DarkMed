<?php
header("Content-type: text/html; charset=windows-1251") ;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed-Mobile Client Menu</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
?>

//-->
</script>

</head>

<body>

<noscript>
</noscript>

<div class="menu">

  <ul class="menu">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td width="10%"> </td>
      <td>
          <li><a href="mob_logon.php" target="section">АВТОРИЗАЦИЯ</a></li> 
          <br>
      </td>
    </tr>
    <tr>
      <td width="10%"> </td>
      <td>
          <li id="ClientCard">
              <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('mob_logon.php') ;
                  else               parent.frames['section'].location.assign('mob_client_card.php'+'?Session='+v_session) ; "
               target="section">КАРТА ПАЦИЕНТА</a></li> 
          <br>
      </td>
    </tr>
    <tr>
      <td width="10%"> </td>
      <td>
          <li id="ClientData">
              <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('mob_logon.php') ;
                  else               parent.frames['section'].location.assign('mob_client_data.php'+'?Session='+v_session) ; "
               target="section">ОБСЛЕДОВАНИЯ И АНАЛИЗЫ</a></li> 
          <br>
      </td>
    </tr>
    <tr>
      <td width="10%"> </td>
      <td>
          <li id="ClientPrescriptions">
              <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('mob_logon.php') ;
                  else               parent.frames['section'].location.assign('mob_client_prescr.php'+'?Session='+v_session) ; "
               target="section">НАЗНАЧЕНИЯ</a></li> 
          <br>
      </td>
    </tr>
    <tr>
      <td width="10%"> </td>
      <td>
          <li id="Messages">
              <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('mob_logon.php') ;
                  else               parent.frames['section'].location.assign('mob_messages.php'+'?Session='+v_session) ; "
               target="section">СООБЩЕНИЯ</a></li> 
          <br>
      </td>
    </tr>
    <tr>
      <td width="10%"> </td>
      <td>
          <li><a href="mob_help.php" target="section">КАК ПОЛЬЗОВАТЬСЯ?</a></li> 
          <br>
      </td>
    </tr>
    </tbody>
  </table>
  </ul>

</div>

</body>

</html>         