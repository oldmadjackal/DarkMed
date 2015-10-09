<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_card.php" ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД

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

                          $owner=$_GET ["Owner"] ;

                         $name_f=$_POST["Name_F"] ;
                         $name_i=$_POST["Name_I"] ;
                         $name_o=$_POST["Name_O"] ;
                         $remark=$_POST["Remark"] ;
                         $check =$_POST["Check"] ;

  FileLog("START", "Session:".$session) ;
  FileLog("",      "  Owner:".$owner) ;
  FileLog("",      "  Check:".$check) ;
  FileLog("",      " Name_F:".$name_f) ;
  FileLog("",      " Name_I:".$name_i) ;
  FileLog("",      " Name_O:".$name_o) ;
  FileLog("",      " Remark:".$remark) ;

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
//--------------------------- Определение владельца страницы

  if(!isset($owner))  $owner =$user ; 

  if($owner!=$user)  $read_only=true ; 
  else               $read_only=false ; 

                      $owner_=$db->real_escape_string($owner) ;
                      $user_ =$db->real_escape_string($user ) ;

//--------------------------- Извлечение ключа шифрования главной страницы

                       $sql="Select crypto ".
                            "  From access_list ".
                            " Where owner='$owner_' ".
                            "  and  login='$user_' " ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения ключа доступа") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_key=\"" .$fields[0]."\" ;\n" ;

//--------------------------- Извлечение данных для отображения
//
//  Сохранение допускается только для владельца страницы

  if(!isset($check) || $read_only)
  {
                       $sql="Select `check`, name_f, name_i, name_o, remark".
                            "  From  client_page_main".
                            " Where  owner='$owner_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $check =$fields[0] ;
                   $name_f=$fields[1] ;
                   $name_i=$fields[2] ;
                   $name_o=$fields[3] ;
                   $remark=$fields[4] ;

        FileLog("", "User main page selected successfully") ;
  }
//--------------------------- Сохранение данных со страницы
  else
  {
          $name_f_=$db->real_escape_string($name_f) ;
          $name_i_=$db->real_escape_string($name_i) ;
          $name_o_=$db->real_escape_string($name_o) ;
          $remark_=$db->real_escape_string($remark) ;

                       $sql="Update client_page_main".
                            " Set   name_f='$name_f_'".
                            "      ,name_i='$name_i_'".
                            "      ,name_o='$name_o_'".
                            "      ,remark='$remark_'".
                            " Where owner ='$user_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update CLIENT_PAGE_MAIN... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "User main page saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Вывод данных пациента на страницу

      echo     "  i_check .value=\"".$check ."\" ;\n" ;
      echo     "  i_name_f.value=\"".$name_f."\" ;\n" ;
      echo     "  i_name_i.value=\"".$name_i."\" ;\n" ;
      echo     "  i_name_o.value=\"".$name_o."\" ;\n" ;
      echo     "  i_remark.value=\"".$remark."\" ;\n" ;

//--------------------------- Формирование списка дополнительных страницы пациента

  if(!$read_only)
  {

                     $sql="Select p.page, p.title, a.crypto".
			  "  From client_pages p, access_list a".
			  " Where p.owner='$user'".
			  "  and  p.page > 0".
			  "  and  a.owner='$user_'".
			  "  and  a.login='$user_'".
			  "  and  a.page =p.page".
                          "  and  p.type ='Client'".
                          " Order by p.page" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES(type ='Client')... : ".$db->error) ;
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

		$link_href="client_page?Session=".$session."&Page=".$fields[0] ;

       echo     "         link_key     =\"".$fields[2]."\" ;			\n" ;
       echo     "         link_key     =Crypto_decode(link_key, password) ;	\n" ;
       echo     "         link_text    =\"".$fields[1]."\" ;			\n" ;
       echo     "         link_text    =Crypto_decode(link_text, link_key) ;	\n" ;
       echo     "	i_list_new     =document.createElement(\"li\") ;	\n" ;
       echo     "	i_link_new     =document.createElement(\"a\") ;		\n" ;
       echo     "       i_link_new.href=\"".$link_href."\" ;			\n" ;
       echo     "       i_text_new     =document.createTextNode(link_text) ;	\n" ;
       echo     "       i_link_new.appendChild(i_text_new) ;			\n" ;
       echo     "       i_list_new.appendChild(i_link_new) ;			\n" ;
       echo     "       i_pages   .appendChild(i_list_new) ;			\n" ;
     }
  }

     $res->close() ;

  }

//--------------------------- Формирование списка назначений пациента

  if(!$read_only)
  {

                     $sql="Select p.page, p.title, a.crypto".
			  "  From client_pages p, access_list a".
			  " Where p.owner='$user'".
			  "  and  p.page > 0".
			  "  and  a.owner='$user_'".
			  "  and  a.login='$user_'".
			  "  and  a.page =p.page".
                          "  and  p.type ='Prescription'".
                          " Order by p.page" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES(type ='Prescription')... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса назначений") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No prescriptions pages detected") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

		$link_href="client_prescr_view?Session=".$session."&Owner=".$user."&Page=".$fields[0] ;

       echo     "         link_key     =\"".$fields[2]."\" ;			\n" ;
       echo     "         link_key     =Crypto_decode(link_key, password) ;	\n" ;
       echo     "         link_text    =\"".$fields[1]."\" ;			\n" ;
       echo     "         link_text    =Crypto_decode(link_text, link_key) ;	\n" ;
       echo     "	i_list_new     =document.createElement(\"li\") ;	\n" ;
       echo     "	i_link_new     =document.createElement(\"a\") ;		\n" ;
       echo     "       i_link_new.href=\"".$link_href."\" ;			\n" ;
       echo     "       i_text_new     =document.createTextNode(link_text) ;	\n" ;
       echo     "       i_link_new.appendChild(i_text_new) ;			\n" ;
       echo     "       i_list_new.appendChild(i_link_new) ;			\n" ;
       echo     "       i_prescr  .appendChild(i_list_new) ;			\n" ;
     }
  }

     $res->close() ;

  }

//--------------------------- Обработка режима READ ONLY

  if($read_only)
  {
      echo     "  SetReadOnly() ;\n" ;
  }

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color=\"red\" ;      " ;
    echo  "i_error.innerHTML  =\"".$text."\" ;" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    " ;
    echo  "i_error.innerHTML  =\"Данные успешно сохранены!\" ;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Client Card</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;
    var  i_pages ;
    var  i_prescr ;
    var  i_check ;
    var  i_name_f ;
    var  i_name_i ;
    var  i_name_o ;
    var  i_remark ;
    var  i_error ;
    var  password ;
    var  page_key ;
    var  check_key ;

  function FirstField() 
  {
    var  i_list_new ;
    var  i_link_new ;
    var  i_text_new ;
    var  link_key ;
    var  link_text ;


       i_table =document.getElementById("Fields") ;
       i_pages =document.getElementById("Pages") ;
       i_prescr=document.getElementById("Prescriptions") ;
       i_check =document.getElementById("Check") ;
       i_name_f=document.getElementById("Name_F") ;
       i_name_i=document.getElementById("Name_I") ;
       i_name_o=document.getElementById("Name_O") ;
       i_remark=document.getElementById("Remark") ;
       i_error =document.getElementById("Error") ;

       i_name_f.focus() ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

       page_key= Crypto_decode( page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
	i_error.style.color="red" ;
	i_error.innerHTML  ="Ошибка расшифровки данных." ;
         return true ;
     }

       i_name_f.value=Crypto_decode(i_name_f.value, page_key) ;
       i_name_i.value=Crypto_decode(i_name_i.value, page_key) ;
       i_name_o.value=Crypto_decode(i_name_o.value, page_key) ;
       i_remark.value=Crypto_decode(i_remark.value, page_key) ;

         return true ;
  }

  function SetReadOnly() 
  {
    var  i_save1 ;
    var  i_access ;
    var  i_form ;
    var  i_pctrl ;

       i_save1 =document.getElementById("Save1") ;
       i_access=document.getElementById("Access") ;
       i_pages =document.getElementById("Pages") ;
       i_pctrl =document.getElementById("NewPage") ;

       i_name_f.readOnly=true ;
       i_name_i.readOnly=true ;
       i_name_o.readOnly=true ;
       i_remark.readOnly=true ;
       i_save1 .disabled=true ;
       i_access.disabled=true ;

       i_pages .removeChild(i_pctrl) ;
  }

  function SendFields() 
  {
     var  error_text ;

	error_text=""
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

       i_name_f.value=Crypto_encode(i_name_f.value, page_key) ;
       i_name_i.value=Crypto_encode(i_name_i.value, page_key) ;
       i_name_o.value=Crypto_encode(i_name_o.value, page_key) ;
       i_remark.value=Crypto_encode(i_remark.value, page_key) ;

                         return true ;         
  } 

  function NewPage() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("client_page.php"+"?Session="+v_session+"&NewPage=1") ;
  } 

  function PagesAccess() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("pages_access.php"+"?Session="+v_session) ;
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
        <b>КАРТА ПАЦИЕНТА</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Сохранить"  id="Save1"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="field"> Фамилия </td>
      <td> <input type="text" size=60 name="Name_F" id="Name_F"> </td>
    </tr>
    <tr>
      <td class="field"> Имя </td>
      <td> <input type="text" size=60 name="Name_I" id="Name_I"> </td>
    </tr>
    <tr>
      <td class="field"> Отчество </td>
      <td> <input type="text" size=60 name="Name_O" id="Name_O"> </td>
    </tr>
    <tr>
      <td class="field"> Примечание </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Remark" id="Remark"> </textarea>
      </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <input type="hidden" size=60 name="Check" id="Check"> </td>
    </tr>
    </tbody>
  </table>

  </form>

  <br>
  <table width="100%" class="cols2">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td width="50%" class="fieldC"> 
        <b>Дополнительные страницы</b>
        <input type="button" value="Предоставить доступ" onclick=PagesAccess()  id="Access">
      </td>
      <td width="1%"> 
      </td>
      <td width="49%" class="fieldC"> 
        <b> Назначения </b> 
      </td>
    </tr>
    <tr>
      <td width="50%">        
       <ul class="menu" name="Pages" id="Pages">
          <li  id="NewPage"><a href="#" onclick=NewPage() target="_self">Создать новый раздел</a></li> 
        </ul>
      </td>
      <td width="1%" class="v-line"> 
      </td>
      <td width="49%"> 
       <ul class="menu" name="Prescriptions" id="Prescriptions">
        </ul>
      </td>
    </tr>
    </tbody>
  </table>

</div>

</body>

</html>
