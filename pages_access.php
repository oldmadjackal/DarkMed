<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Pagess_access.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение параметров

                        $session=$_GET ["Session"] ;
  if(!isset($session))  $session=$_POST["Session"] ;

                         $receiver=$_POST["Receiver"] ;
                         $letter  =$_POST["Letter"] ;
                         $invite  =$_POST["Invite"] ;
                         $incopy  =$_POST["InCopy"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "   Receiver:".$receiver) ;
  FileLog("",      "     Letter:".$letter) ;
  FileLog("",      "     Invite:".$invite) ;
  FileLog("",      "     InCopy:".$incopy) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                    ErrorMsg($error) ;
                         return ;
  }

          $user_=$db->real_escape_string($user) ;

//--------------------------- Формирование списка специальностей

                     $sql="Select code, name".
			  "  From ref_doctor_specialities".
			  " Where language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_DOCTOR_SPECIALITIES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника специальностей") ;
                         return ;
  }
  else
  {  
       echo "   a_specialities['Dummy']=\"\" ;\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_specialities['".$fields[0]."']='".$fields[1]."' ;\n" ;
     }
  }

     $res->close() ;

//--------------------------- Формирование списка врачей

                     $sql="Select owner, name_f, name_i, name_o, speciality".
			  "  From doctor_page_main".
                          " Where confirmed='Y'".
			  " Order by name_f, name_i, name_o" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка врачей") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;
                $decl=$fields[1]." ".$fields[2]." ".$fields[3]."=".$fields[4] ;

       echo "   a_doctors['".$fields[0]."']='".$decl."' ;\n" ;
     }
  }

     $res->close() ;

//--------------------------- Сохранение данных о сообщении

  if(isset($receiver))
  {
          $receiver=$db->real_escape_string($receiver) ;
          $letter  =$db->real_escape_string($letter) ;
          $invite  =$db->real_escape_string($invite) ;
          $incopy  =$db->real_escape_string($incopy) ;

       $res=$db->query("Insert into messages(Receiver,Sender,Type,Text,Details,Copy)".
                       " values('$receiver','$user','CLIENT_ACCESS_INVITE','$invite','$letter','$incopy')") ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 3") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "Access message saved successfully") ;
     SuccessMsg() ;
  }

//--------------------------- Формирование записи о ключе подписи и о головной странице

                     $sql="Select a.crypto, u.sign_s_key, u.msg_key".
			  "  From access_list a, users u".
			  " Where a.owner=u.login".
			  "  and  a.owner='$user_'".
			  "  and  a.login='$user_'".
			  "  and  a.page = 0" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса ключа главной страницы") ;
                         return ;
  }
  else
  {      
	      $fields=$res->fetch_row() ;

       echo "    link_key      ='".$fields[0]."' ;			\n" ;
       echo "    link_key      =Crypto_decode(link_key, password) ;	\n" ;
       echo " a_pages_keys['0']=link_key ;				\n" ;
       echo " a_files_keys['0']='' ;					\n" ;

       echo "    sender_key    ='".$fields[1]."' ;			\n" ;
       echo "    sender_key    =Crypto_decode(sender_key, password) ;	\n" ;

       echo "       msg_key    ='".$fields[2]."' ;			\n" ;
       echo "       msg_key    =Crypto_decode(msg_key, password) ;	\n" ;
  }

     $res->close() ;

//--------------------------- Формирование списка страниц пациента

                     $sql="Select p.page, p.title, a.crypto, a.ext_key".
			  "  From client_pages p, access_list a".
			  " Where p.owner='$user_'".
			  "  and  p.page>0".
			  "  and  a.owner='$user_'".
			  "  and  a.login='$user_'".
			  "  and  a.page = p.page" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса разделов") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No additional pages detected") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

		$link_href="Client_page.php?Session=".$session."&Page=".$fields[0] ;

       echo "    link_key     ='".$fields[2]."' ;			\n" ;
       echo "    link_key     =Crypto_decode(link_key, password) ;	\n" ;
       echo "    link_text    ='".$fields[1]."' ;			\n" ;
       echo "    link_text    =Crypto_decode(link_text, link_key) ;	\n" ;
       echo "    file_key     ='".$fields[3]."' ;			\n" ;
       echo "    file_key     =Crypto_decode(file_key, password) ;	\n" ;
       echo "  AddNewPage(link_text, '".$fields[0]."') ;		\n" ;
       echo "    a_pages_keys['".$fields[0]."']=link_key ;		\n" ;
       echo "    a_files_keys['".$fields[0]."']=file_key ;		\n" ;
     }
  }

     $res->close() ;

//--------------------------- Формирование полей выбора доктора

        echo     "  AddNewSpeciality('') ;\n" ;
        echo     "   FormDoctorsList('') ;\n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;					\n" ;
    echo  "i_error.innerHTML  ='Доступ к указанным страницам предоставлен.' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Pages access grants</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;
    var  i_pages ;
    var  i_error ;
    var  i_receiver ;
    var  i_letter ;
    var  i_invite ;
    var  i_incopy ;
    var  password ;
    var  sender_key ;
    var  msg_key ;
    var  a_pages_keys ;
    var  a_files_keys ;
    var  a_specialities ;
    var  a_doctors ;

  function FirstField() 
  {
       i_table   =document.getElementById("Fields") ;
       i_pages   =document.getElementById("Pages") ;
       i_error   =document.getElementById("Error") ;
       i_receiver=document.getElementById("Receiver") ;
       i_letter  =document.getElementById("Letter") ;
       i_invite  =document.getElementById("Invite") ;
       i_incopy  =document.getElementById("InCopy") ;

       password=TransitContext("restore", "password", "") ;

	a_specialities=new Array() ; 
 	  a_pages_keys=new Array() ;
 	  a_files_keys=new Array() ;
             a_doctors=new Array() ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function SendFields() 
  {
     var  i_page ;
     var  i_doctor ;
     var  doctor_key ;
     var  error_text ;


	i_letter.value="" ;
            error_text="Не задано ни одной страницы для доступа" ;

    for(var page in a_pages_keys)
    {
	  i_page=document.getElementById("Page_"+page) ;
       if(i_page.checked) { 
          i_letter.value=i_letter.value+page+":"+a_pages_keys[page]+":"+a_files_keys[page]+";" ;
              error_text=   "" ;
                          }
    }

        i_doctor        =document.getElementById("Doctors_list") ;
        i_receiver.value= i_doctor.value ;
     if(i_receiver.value=="")  error_text="Укажите получателя доступа" ;

     if(error_text=="")
     {
           doctor_key=parent.frames['details'].document.getElementById('Sign_key').value ;
       i_incopy.value=Crypto_encode(i_invite.value, msg_key) ;
       i_letter.value=  Sign_encode(i_letter.value, sender_key, doctor_key) ;
       i_invite.value=  Sign_encode(i_invite.value, sender_key, doctor_key) ;
     }

        i_error.style.color="red" ;
        i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;         
  } 

  function AddNewPage(p_title, p_value)
  {
     var  i_pages ;
     var  i_div_new ;
     var  i_check_new ;
     var  i_text_new ;

       i_pages          = document.getElementById("Pages") ;
       i_div_new        = document.createElement("div") ;
       i_check_new      = document.createElement("input") ;
       i_check_new.type ="checkbox" ;
       i_check_new.name ="pages[]" ;
       i_check_new.id   ="Page_"+p_value ;
       i_check_new.value= p_value ;
       i_text_new       = document.createTextNode(p_title) ;

       i_div_new.appendChild(i_check_new) ;
       i_div_new.appendChild(i_text_new) ;
       i_pages  .appendChild(i_div_new  ) ;

    return ;         
  } 

  function AddNewSpeciality(p_selected)
  {
     var  i_specialities ;
     var  i_select_new ;
     var  selected ;

       i_specialities       = document.getElementById("Specialities") ;
       i_select_new         = document.createElement("select") ;
       i_select_new.id      ="Speciality" ;
       i_select_new.name    ="Speciality" ;
       i_select_new.onchange= function(e) { FormDoctorsList(this.options[this.selectedIndex].value); } ;

    for(var elem in a_specialities)
    {
                             selected=false ;
       if(p_selected==elem)  selected=true ;

                            i_select_new.length++ ;
       i_select_new.options[i_select_new.length-1].text    =a_specialities[elem] ;
       i_select_new.options[i_select_new.length-1].value   =               elem ;
       i_select_new.options[i_select_new.length-1].selected=           selected ;
    }

       i_specialities.appendChild(i_select_new) ;	

    return ;         
  } 

  function FormDoctorsList(p_speciality)
  {
     var  i_doctors ;
     var  i_select_old ;
     var  i_select_new ;
     var  a_words ;

       i_doctors   =document.getElementById("Doctors") ;
       i_select_old=document.getElementById("Doctors_list") ;

    if(i_select_old!=null)  i_doctors.removeChild(i_select_old) ;

       i_select_new         =document.createElement("select") ;
       i_select_new.id      ="Doctors_list" ;
       i_select_new.name    ="Doctors_list" ;
       i_select_new.size    = 10 ;
       i_select_new.onchange= function(e) {
					    var  v_session ;
						 v_session=TransitContext("restore","session","") ;
						parent.frames["details"].location.assign("doctor_details.php"+
                                                                                         "?Session="+v_session+
                                                                                         "&Doctor="+this.options[this.selectedIndex].value) ;
					  } ;

    for(var elem in a_doctors)
    {
		a_words=a_doctors[elem].split("=") ;

      if(                   p_speciality ==""      || 
                            p_speciality =="Dummy" || 
         a_words[1].indexOf(p_speciality)!=-1        )
      {
                             i_select_new.length++ ;
        i_select_new.options[i_select_new.length-1].text =a_words[0] ;
        i_select_new.options[i_select_new.length-1].value= elem ;
      } 
    }

       i_doctors.appendChild(i_select_new) ;

    return ;         
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
        <b>ПРЕДОСТАВЛЕНИЕ ДОСТУПА К СВОИМ ДАННЫМ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">

  <p class="error" id="Error"></p>

  <table width="100%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td>
        <div> <input type="checkbox" name="pages[]" id="Page_0" value="0">Главная страница </div>
        <p> </p>
      </td>
      <td>
        <div id="Specialities"> Специальность: </div>
        <p> </p>
      </td>
    </tr>
    <tr>
      <td class="fieldL" id="Pages">
      </td>
      <td class="fieldL" id="Doctors">
      </td>
    </tr>
    </tbody>
  </table>

  <table width="100%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td> <br> </td>
    </tr>
    <tr>
      <td class="fieldC">
        Сопроводительный техт <br>
        <textarea cols=100 rows=3 wrap="soft" name="Invite" id="Invite"> </textarea>
      </td>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="submit" value="Предоставить доступ"> </td>
    </tr>
    </tbody>
  </table>

	<input type="hidden" name="Receiver" id="Receiver" >
	<input type="hidden" name="Letter"   id="Letter" >
	<input type="hidden" name="InCopy"   id="InCopy" >

  </form>

  <ul class="menu" name="Pages" id="Pages">
  </ul>

</div>

</body>

</html>
