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
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("buttons.css") ;
</style>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<script type="text/javascript">

<!--

<?php
  require("common.inc") ;
?>

  function ShowClient() 
  {
    var  i_elem ;

       i_elem       =document.getElementById("ClientCard") ;
       i_elem.hidden= false ;
	   i_elem       =document.getElementById("Analyses") ;
       i_elem.hidden= false ;
	   i_elem       =document.getElementById("Prescriptions") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("DoctorCard") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("ClientsList") ;
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
	   i_elem       =document.getElementById("Analyses") ;
       i_elem.hidden= true ;
	   i_elem       =document.getElementById("Prescriptions") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("DoctorCard") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("ClientsList") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("Messages") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("PrescriptionsSets") ;
       i_elem.hidden= false ;
  }

  function ShowExecutor() 
  {
    var  i_elem ;

       i_elem       =document.getElementById("ClientCard") ;
       i_elem.hidden= true ;
	   i_elem       =document.getElementById("Analyses") ;
       i_elem.hidden= true ;
	   i_elem       =document.getElementById("Prescriptions") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("DoctorCard") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("ClientsList") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("Messages") ;
       i_elem.hidden= false ;
       i_elem       =document.getElementById("PrescriptionsSets") ;
       i_elem.hidden= true ;
  }

  function ShowAnonimous() 
  {
    var  i_elem ;

       i_elem       =document.getElementById("ClientCard") ;
       i_elem.hidden= true ;
	   i_elem       =document.getElementById("Analyses") ;
       i_elem.hidden= true ;
	   i_elem       =document.getElementById("Prescriptions") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("DoctorCard") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("ClientsList") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("Messages") ;
       i_elem.hidden= true ;
       i_elem       =document.getElementById("PrescriptionsSets") ;
       i_elem.hidden= true ;
  }

//-->
</script>
<script type="text/javascript" src="https://secure.skypeassets.com/i/scom/js/skype-uri.js"></script>

</head>

<body>

<div class="menu">
  <div class="Normal_CT">
    <br>
    <img src="images/NewStep.png">
  </div>

  <ul class="menu">
    <li hidden id="ClientCard">
        <a class="menu_item"  href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['view'].location.assign('logon.php') ;
                  else               parent.frames['view'].location.assign('client_card.php'+'?Session='+v_session) ; "
         target="view" id="user_card_btn">Карта пациента</a>
    </li> 
	
	<li hidden id="Analyses">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['view'].location.assign('logon.php') ;
                  else               parent.frames['view'].location.assign('client_analises.php'+'?Session='+v_session) ; "
         target="view" id="analyses_btn">Анализы и снимки</a>
    </li> 
	
	<li hidden id="Prescriptions">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['view'].location.assign('logon.php') ;
                  else               parent.frames['view'].location.assign('client_prescriptions.php'+'?Session='+v_session) ; "
         target="view" id="prescriptions_btn">Назначения</a>
    </li> 

    <li hidden id="DoctorCard">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['view'].location.assign('logon.php') ;
                  else               parent.frames['view'].location.assign('doctor_card.php'+'?Session='+v_session) ; "
         target="view" id="doctor_card_btn">Личный формуляр</a>
    </li> 
    <li hidden id="ClientsList">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['view'].location.assign('logon.php') ;
                  else               parent.frames['view'].location.assign('doctor_clients_wrapper.php'+'?Session='+v_session) ; "
         target="view" id="patients_btn">Пациенты</a>
    </li> 
    <li>
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['view'].location.assign('doctors_list.php'+'?Session='+v_session) ; "
         target="view" id="doctors_btn">Врачи и специалисты</a> 
    </li>
    <li hidden id="Messages">
        <a class="menu_item"  href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['view'].location.assign('logon.php') ;
                  else               parent.frames['view'].location.assign('messages_wrapper.php'+'?Session='+v_session) ; "
         target="view" id="forum_btn">Сообщения</a>
    </li>
    <li>
	<a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['view'].location.assign('deseases_registry_wrapper.php'+'?Session='+v_session) ; "
         target="view" id="ill_catalog_btn">Реестр заболеваний</a> 
    </li>
    <li>
	<a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['view'].location.assign('prescriptions_registry_wrapper.php'+'?Session='+v_session) ; "
         target="view" id="presiption_btn">Регистр назначений</a>
    </li>
    <li hidden id="PrescriptionsSets">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['view'].location.assign('logon.php') ;
                  else               parent.frames['view'].location.assign('sets_registry_wrapper.php'+'?Session='+v_session) ; "
         target="view">Комплексы назначений</a>
    </li>	 
    <br>
    <li>
        <a class="menu_item" id="how_to_use_btn" href="help.php" target="view"> Помощь</a>
    </li>
    <li>
        <a class="menu_item" id="message_btn" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['view'].location.assign('callback_list_wrapper.php'+'?Session='+v_session) ; "
         target="view">Ваши пожелания</a> 
    </li>
  </ul>

  <table width="100%" id="Fields">
    <tbody>
      <tr>
        <td> <input type="hidden" name="glbSession"  id="glbSession"> </td>
      </tr>
      <tr>
        <td> <input type="hidden" name="glbPassword" id="glbPassword"> </td>
      </tr>
      <tr>
        <td> <input type="hidden" name="glbValue"    id="glbValue"> </td>
      </tr>
      <tr>
        <td> <input type="hidden" name="glbCallBack" id="glbCallBack"> </td>
      </tr>
      <tr>
        <td> <input type="hidden" name="glbUser"     id="glbUser"> </td>
      </tr>
    </tbody>
  </table>

  <div class="debug" id="DebugLog" hidden></div>

<!-- 	<div id="SkypeButton_Call" class="skype">
		<em/><em/>
		 <script type="text/javascript">
				 Skype.ui({
				 "name": "dropdown",
				 "element": "SkypeButton_Call",
				 "participants": ["lizochka42"],
				 "listParticipants":"false",
				 "imageSize": 16,
				 "imageColor": "skype"
				 });
		 </script>
	</div> -->
</div>

</body>

</html>         