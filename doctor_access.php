<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_access.php" ;

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
                          $doctor =$_GET ["Doctor"] ;

                         $receiver=$_POST["Receiver"] ;
    if(isset($receiver))
    {
                         $letter  =$_POST["Letter"] ;
                         $invite  =$_POST["Invite"] ;
                         $incopy  =$_POST["InCopy"] ;
    }

           FileLog("START", "    Session:".$session) ;
           FileLog("",      "     Doctor:".$doctor) ;

    if(isset($receiver))
    {
           FileLog("",      "   Receiver:".$receiver) ;
           FileLog("",      "     Letter:".$letter) ;
           FileLog("",      "     Invite:".$invite) ;
           FileLog("",      "     InCopy:".$incopy) ;
    }
//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
  }

          $user_  =$db->real_escape_string($user) ;
          $doctor_=$db->real_escape_string($doctor) ;

//--------------------------- Сохранение данных о сообщении

  if(isset($receiver))
  {
          $receiver=$db->real_escape_string($receiver) ;
          $letter  =$db->real_escape_string($letter) ;
          $invite  =$db->real_escape_string($invite) ;
          $incopy  =$db->real_escape_string($incopy) ;

                       $sql="Insert into messages(Receiver,Sender,Type,Text,Details,Copy)".
                            " values('$receiver','$user','CLIENT_ACCESS_INVITE','$invite','$letter','$incopy')" ;
       $res=$db->query($sql) ;
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
//--------------------------- Извлечение данных врача

                     $sql="Select CONCAT_WS(' ', d.name_f, d.name_i, d.name_o), sign_p_key".
                          "  From doctor_page_main d, users u".
                          " Where d.owner='$doctor_'".
                          "  and  d.owner=u.login" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
  }
  if($res->num_rows==0) {
          FileLog("ERROR", "No such doctor in DB) : ".$doctor) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестный врач") ;
                         return ;
  }

	      $fields=$res->fetch_row() ;

       echo "  i_doctor_FIO.innerHTML='".$fields[0]."' ;	\n" ;
       echo "    doctor_key          ='".$fields[1]."' ;	\n" ;
       echo "    i_receiver.value    ='".$doctor."' ;		\n"  ;

	              $res->close() ;

//--------------------------- Формирование записи о ключе подписи и о головной странице

                     $sql="Select a1.crypto, u.sign_s_key, u.msg_key, a2.page".
			  "  From access_list a1 inner join users u on a1.owner=u.login".
                          "                 left outer join access_list a2 on a2.owner='$user_' and a2.login='$doctor_' and a2.page=a1.page".
			  " Where a1.owner='$user_'".
			  "  and  a1.login='$user_'".
			  "  and  a1.page = 0" ;
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

       echo "    link_key      ='".$fields[0]."' ;				\n" ;
       echo "    link_key      =Crypto_decode(link_key, password) ;		\n" ;
       echo " a_pages_keys['0']=link_key ;					\n" ;
       echo " a_files_keys['0']='' ;						\n" ;

       echo "    sender_key    ='".$fields[1]."' ;				\n" ;
       echo "    sender_key    =Crypto_decode(sender_key, password) ;		\n" ;

       echo "       msg_key    ='".$fields[2]."' ;				\n" ;
       echo "       msg_key    =Crypto_decode(msg_key, password) ;		\n" ;

       echo "  AddNewPage('Главная страница','0','main','".$fields[3]."') ;	\n" ;
  }

     $res->close() ;

//--------------------------- Формирование списка страниц пациента

                     $sql="Select p.page, p.title, a1.crypto, a1.ext_key, p.type, a2.page".
			  "  From client_pages p inner join access_list a1 on a1.owner='$user_' and a1.login='$user_'   and a1.page=p.page".
                               "            left outer join access_list a2 on a2.owner='$user_' and a2.login='$doctor_' and a2.page=p.page".
			  " Where p.owner='$user_'".
			  "  and  p.page>0" ;
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

       echo "    link_key     ='".$fields[2]."' ;						\n" ;
       echo "    link_key     =Crypto_decode(link_key, password) ;				\n" ;
       echo "    link_text    ='".$fields[1]."' ;						\n" ;
       echo "    link_text    =Crypto_decode(link_text, link_key) ;				\n" ;
       echo "    file_key     ='".$fields[3]."' ;						\n" ;
       echo "    file_key     =Crypto_decode(file_key, password) ;				\n" ;
       echo "  AddNewPage(link_text, '".$fields[0]."','".$fields[4]."','".$fields[5]."') ;	\n" ;
       echo "    a_pages_keys['".$fields[0]."']=link_key ;					\n" ;
       echo "    a_files_keys['".$fields[0]."']=file_key ;					\n" ;
     }
  }

     $res->close() ;

//--------------------------- Завершение
//        <div> <input type="checkbox" name="pages[]" id="Page_0" value="0">Главная страница </div>

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

<title>DarkMed Doctor access grants</title>
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
    var  i_doctor_FIO ;
    var  i_receiver ;
    var  i_letter ;
    var  i_invite ;
    var  i_incopy ;
    var  password ;
    var  sender_key ;
    var  doctor_key ;
    var  msg_key ;
    var  a_pages_keys ;
    var  a_files_keys ;

  function FirstField() 
  {
       i_table     =document.getElementById("Fields") ;
       i_pages     =document.getElementById("Pages") ;
       i_doctor_FIO=document.getElementById("DoctorFIO") ;
       i_error     =document.getElementById("Error") ;
       i_receiver  =document.getElementById("Receiver") ;
       i_letter    =document.getElementById("Letter") ;
       i_invite    =document.getElementById("Invite") ;
       i_incopy    =document.getElementById("InCopy") ;

       password=TransitContext("restore", "password", "") ;

 	  a_pages_keys=new Array() ;
 	  a_files_keys=new Array() ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function SendFields() 
  {
     var  i_page ;
     var  error_text ;


	i_letter.value="" ;
            error_text="Не задано ни одной страницы для доступа" ;

    for(var page in a_pages_keys)
    {
	  i_page=document.getElementById("Page_"+page) ;
      if(i_page!=null)  
       if(i_page.checked) { 
          i_letter.value=i_letter.value+page+":"+a_pages_keys[page]+":"+a_files_keys[page]+";" ;
              error_text=   "" ;
                          }
    }

     if(error_text=="")
     {
       i_incopy.value=Crypto_encode(i_invite.value, msg_key) ;
       i_letter.value=  Sign_encode(i_letter.value, sender_key, doctor_key) ;
       i_invite.value=  Sign_encode(i_invite.value, sender_key, doctor_key) ;
     }

        i_error.style.color="red" ;
        i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;         
  } 

  function AddNewPage(p_title, p_value, p_type, p_check)
  {
     var  i_pages ;
     var  i_div_new ;
     var  i_check_new ;
     var  i_text_new ;

          if(p_type=="main"  )  i_pages=document.getElementById("Main") ;
     else if(p_type=="client")  i_pages=document.getElementById("Pages") ;
     else                       i_pages=document.getElementById("Prescriptions") ;

       i_div_new        = document.createElement("div") ;
       i_check_new      = document.createElement("input") ;
       i_check_new.type ="checkbox" ;
       i_check_new.value= p_value ;

    if(p_check=='')
    {
       i_check_new.id   ="Page_"+p_value ;
       i_check_new.name ="pages[]" ;
    }
    else
    {
       i_check_new.checked =true ;
       i_check_new.disabled=true ;
    }

       i_text_new       = document.createTextNode(p_title) ;

       i_div_new.appendChild(i_check_new) ;
       i_div_new.appendChild(i_text_new) ;
       i_pages  .appendChild(i_div_new  ) ;

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

  <div class="error" id="Error"></div>

  <b><div class="fieldC" id="DoctorFIO"></div></b>

  <br>
  <table width="100%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td id="Main">
      </td>
      <td></td>
    </tr>
    <tr>
      <td class="fieldL"><br><b>Результаты анализов и обследований</b></td>
      <td class="fieldL"><br><b>Назначения</b></td>
    </tr>
    <tr>
      <td class="fieldL" id="Pages">
      </td>
      <td class="fieldL" id="Prescriptions">
      </td>
    </tr>
    </tbody>
  </table>

  <br>

  <table width="100%">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="fieldC">
        Сопроводительный техт <br>
        <textarea cols=100 rows=3 wrap="soft" name="Invite" id="Invite"></textarea>
      </td>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="submit" value="Направить приглашение"> </td>
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
