<?php
header("Content-type: text/html; charset=windows-1251") ;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
?>

  function GoTo(link) 
  {
    var  v_session ;

	v_session=TransitContext("restore","session","") ;

     if(v_session=="") 
     {
        parent.frames["section"].location.assign("logon.php") ;
     }
     else 
     {
        parent.frames["section"].location.assign(link+"?Session="+v_session) ;
     } 

  }

  function ShowClient() 
  {
    var  i_elem ;

       i_elem       =document.getElementById("ClientCard") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("DoctorCard") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("DoctorList") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("Messages") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("PrescriptionsSets") ;
       i_elem.hidden= true ;
  }

  function ShowDoctor() 
  {
    var  i_elem ;

       i_elem       =document.getElementById("ClientCard") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("DoctorCard") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("DoctorList") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("Messages") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("PrescriptionsSets") ;
       i_elem.hidden= false ;
  }

  function ShowAnonimous() 
  {
    var  i_elem ;

       i_elem       =document.getElementById("ClientCard") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("DoctorCard") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("DoctorList") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("Messages") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("PrescriptionsSets") ;
       i_elem.hidden= true ;
  }

//-->
</script>

</head>

<body>

<noscript>
</noscript>

<div class="menu">
  <div class="header">
     Header picture block
  </div>

  <ul class="menu">
    <li><a href="registry.php"                        target="section">Регистрация</a></li> 
    <li><a href="logon.php"                           target="section">Авторизация</a></li> 
    <li hidden id="ClientCard">
        <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('client_card.php'+'?Session='+v_session) ; "
         target="section">Карта пациента</a></li> 
    <li hidden id="DoctorCard">
        <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('doctor_card.php'+'?Session='+v_session) ; "
         target="section">Формуляр врача</a></li> 
    <li hidden id="DoctorList">
        <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('doctor_clients.php'+'?Session='+v_session) ; "
         target="section">Пациенты врача</a></li> 
    <li hidden id="Messages">
        <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('messages.php'+'?Session='+v_session) ; "
         target="section">Сообщения</a></li> 
    <li><a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['section'].location.assign('prescriptions_registry.php'+'?Session='+v_session) ; "
         target="section">Общий регистр назначений</a></li> 
    <li hidden id="PrescriptionsSets">
        <a href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('sets_registry.php'+'?Session='+v_session) ; "
         target="section">Комплексы назначений</a></li> 
    <br>
    <li><a href="help.php" target="section">Как пользоваться порталом?</a></li> 
    <br>
    <li><a href="callback_list.php" target="section">Сообщения пользователей</a></li> 
  </ul>

  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td> <input type="hidden" name="glbSession"  id="glbSession"> </td>
    </tr>
    <tr>
      <td> <input type="hidden" name="glbPassword" id="glbPassword"> </td>
    </tr>
    </tbody>
  </table>

  <div class="debug" id="DebugLog"></div>

</div>

</body>

</html>         