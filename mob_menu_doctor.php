<?php
header("Content-type: text/html; charset=windows-1251") ;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed-Mobile Doctor Menu</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css") ;
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
?>

//-->
</script>

</head>

<body onload="FirstField();">

<script>

  function FirstField() 
  {
        parent.frames['details'].location.assign('mob_footer_menu.html');

         return true ;
  }

</script>

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
          <li><a href="mob_logon.php" target="section">�����������</a></li> 
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
                  else               parent.frames['section'].location.assign('doctor_card.php'+'?Session='+v_session) ; "
               target="section">�������� �����</a></li> 
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
               target="section">���������</a></li> 
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
                  else               parent.frames['section'].location.assign('mob_doctor_clients.php'+'?Session='+v_session) ; "
               target="section">��������</a></li> 
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
                  else               parent.frames['section'].location.assign('mob_doctors_list.php'+'?Session='+v_session) ; "
               target="section">����� � �����������</a></li> 
          <br>
      </td>
    </tr>
    <tr>
      <td width="10%"> </td>
      <td>
          <li><a href="mob_help.php" target="section">��� ������������?</a></li> 
          <br>
      </td>
    </tr>
    </tbody>
  </table>
  </ul>

</div>

</body>

</html>         