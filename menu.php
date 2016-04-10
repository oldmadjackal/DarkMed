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
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
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
  <div class="fieldC">
    <br>
    <img src="images/NewStep.png">
  </div>

  <ul class="menu">
    <li hidden id="ClientCard">
        <a class="menu_item"  href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('client_card.php'+'?Session='+v_session) ; "
         target="section" id="user_card_btn">Карта пациента</a>
		
	</li> 

	<li hidden id="DoctorCard">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('doctor_card.php'+'?Session='+v_session) ; "
         target="section" id="doctor_card_btn">Личный формуляр</a>

	</li> 

	<li hidden id="ClientsList">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('doctor_clients.php'+'?Session='+v_session) ; "
         target="section" id="patients_btn">Пациенты</a>

	</li> 

	<li><a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['section'].location.assign('doctors_list.php'+'?Session='+v_session) ; "
         target="section" id="doctors_btn">Врачи и специалисты</a> 

	</li>
		 
    <li hidden id="Messages">
        <a class="menu_item"  href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('messages.php'+'?Session='+v_session) ; "
         target="section" id="forum_btn">Сообщения</a>

	</li>


	<li>
		<a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['section'].location.assign('deseases_registry.php'+'?Session='+v_session) ; "
         target="section" id="ill_catalog_btn">Реестр заболеваний</a> 

	</li>

	<li>
		<a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['section'].location.assign('prescriptions_registry.php'+'?Session='+v_session) ; "
         target="section" id="presiption_btn">Регистр назначений</a>
		
	</li>

    <li hidden id="PrescriptionsSets">
        <a class="menu_item" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                  if(v_session=='')  parent.frames['section'].location.assign('logon.php') ;
                  else               parent.frames['section'].location.assign('sets_registry.php'+'?Session='+v_session) ; "
         target="section">Комплексы назначений</a></li>
		 
    <li><a class="menu_item" id="how_to_use_btn" href="help.php" target="section"> Помощь</a></li>

 
	<li> <a  class="menu_item" id="message_btn" href="javascript:
                 var  v_session=parent.frames['menu'].document.getElementById('glbSession').value ;
                                parent.frames['section'].location.assign('callback_list.php'+'?Session='+v_session) ; "
         target="section">Ваши пожелания</a> 
		 
	</li>


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
    <tr>
      <td> <input type="hidden" name="glbValue"    id="glbValue"> </td>
    </tr>
    <tr>
      <td> <input type="hidden" name="glbCallBack" id="glbCallBack"> </td>
    </tr>
    </tbody>
    </table>
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