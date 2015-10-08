<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_page.php" ;

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
  if(!isset($session ))  $session=$_POST["Session"] ;

                        $new_page=$_GET ["NewPage"] ;
                           $owner=$_GET ["Owner"] ;

                            $page=$_GET ["Page"] ;
  if(!isset($page    ))     $page=$_POST["Page"] ;

                          $update=$_POST["Update"] ;
  if(isset($update   )) {
                          $crypto=$_POST["Crypto"] ;
                           $check=$_POST["Check"] ;
                           $title=$_POST["Title"] ;
                          $remark=$_POST["Remark"] ;
  }

    FileLog("START", "Session:".$session) ;
    FileLog("",      "NewPage:".$new_page) ;
    FileLog("",      "  Owner:".$owner) ;
    FileLog("",      "   Page:".$page) ;

  if(isset($update   )) {
    FileLog("",      " Update:".$update) ;
    FileLog("",      "  Check:".$check) ;
    FileLog("",      " Crypto:".$crypto) ;
    FileLog("",      "  Title:".$title) ;
    FileLog("",      " Remark:".$remark) ;
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
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Определение владельца страницы

  if(!isset($owner))  $owner=$user ; 

          $owner_=$db->real_escape_string($owner) ;

  if($owner!=$user)  $read_only=true ; 
  else               $read_only=false ; 

                        $owner_=$db->real_escape_string($owner) ;
                        $user_ =$db->real_escape_string($user ) ;
                        $page_ =$db->real_escape_string($page ) ;

//--------------------------- Отображение пустой новой страницы

  if( isset($new_page) &&
     !isset($check   )   ) 
  {
     $db->close() ;

      echo     "  i_update.value='insert' ;\n" ;

      FileLog("",     "New page template sent") ;     
      FileLog("STOP", "Done") ;     
         return ;
  }
//--------------------------- Приведение параметров

          $crypto_=$db->real_escape_string($crypto) ;
          $check_ =$db->real_escape_string($check ) ;
          $title_ =$db->real_escape_string($title ) ;
          $remark_=$db->real_escape_string($remark) ;

//--------------------------- Первое сохранение новой страницы
//
//  Сохранение допускается только для владельца страницы

  if(!$read_only)
  if(isset($update)         && 
           $update=="insert"  )
  {
//- - - - - - - - - - - - - - Определяем номер новой страницы
                       $sql="Select count(*), max(Page)+1".
                            "  From client_pages".
                            " Where owner='$owner_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
               FileLog("ERROR", "Select new page number from Insert CLIENT_PAGES. : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения номера новой страницы") ;
                           return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

        if($fields[0]=="0")  $page_=  "1" ;
        else                 $page_=$fields[1] ;
//- - - - - - - - - - - - - - Добавляем новую страницу
                       $sql="Insert into ".
                            "`access_list`(`Owner`,  `Login`,  `Page`,      `Crypto`)".
                            "       values('$user_', '$user_', '$new_page', '$crypto_')" ;
       $res=$db->query($sql) ;
    if($res===false) {
               FileLog("ERROR", "Insert ACCESS_LIST... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 1") ;
                           return ;
    }

                       $sql="Insert into ".
                            "`client_pages`(`Owner`,  `Page`,      `Type`,   `Creator`, `Check`,   `Title`,   `Remark`  )".
                            "        values('$user_', '$new_page', 'client', '$user_',  '$check_', '$title_', '$remark_')" ;
       $res=$db->query($sql) ;
    if($res===false) {
               FileLog("ERROR", "Insert CLIENT_PAGES... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 2") ;
                           return ;
    }
//- - - - - - - - - - - - - -
        FileLog("", "User ".$owner." additional page ".$page_." successfully created") ;
  }
//--------------------------- Сохранение данных страницы
//
//  Сохранение допускается только для владельца страницы

  if(isset($check) && $read_only===false)
  {
                       $sql="Update  client_pages".
                            "   Set  title ='$title_'".
                            "       ,remark='$remark_'".
                            " Where `owner`='$owner_' ".
                            "  and   page  = $page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update CLIENT_PAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "User ".$owner." additional page ".$page_." saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Извлечение ключа страницы

                       $sql="Select  crypto ".
                            "  From `access_list` ".
                            " Where `owner`='$owner_' ".
                            "  and  `login`='$user_' ".
                            "  and  `page` =$page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select ACCESS_LIST...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения ключа доступа") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_key=\"" .$fields[0]."\" ;\n" ;

//--------------------------- Извлечение данных страницы

                       $sql="Select `check`, title, remark".
                            "  From `client_pages`".
                            " Where `owner`='$owner_'".
                            "  and  `page` =$page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select CLIENT_PAGES...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $check =$fields[0] ;
                   $title =$fields[1] ;
                   $remark=$fields[2] ;

        FileLog("", "User ".$owner." additional page ".$page." presented successfully") ;

//--------------------------- Отображение данных на странице

      echo     "  i_check .value='".$check. "' ;\n" ;
      echo     "  i_title .value='".$title. "' ;\n" ;
      echo     "  i_remark.value='".$remark."' ;\n" ;
      echo     "  i_update.value='update' ;	\n" ;

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

    echo  "i_error.style.color=\"red\" ;      \n" ;
    echo  "i_error.innerHTML  =\"".$text."\" ;\n" ;
    echo  "return ;\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    \n" ;
    echo  "i_error.innerHTML  =\"Данные успешно сохранены!\" ;\n" ;
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
    var  i_page ;
    var  i_check ;
    var  i_crypto ;
    var  i_title ;
    var  i_remark ;
    var  i_update ;
    var  i_error ;
    var  password ;
    var  page_key ;
    var  check_key ;

  function FirstField() 
  {
       i_table =document.getElementById("Fields") ;
       i_page  =document.getElementById("Page") ;
       i_check =document.getElementById("Check") ;
       i_crypto=document.getElementById("Crypto") ;
       i_title =document.getElementById("Title") ;
       i_remark=document.getElementById("Remark") ;
       i_update=document.getElementById("Update") ;
       i_error =document.getElementById("Error") ;

       i_title.focus() ;

           page_key="" ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;

    if(page_key!="")
    {
       page_key= Crypto_decode( page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
	i_error.style.color="red" ;
	i_error.innerHTML  ="Ошибка расшифровки данных." ;
         return true ;
     }

       i_title .value=Crypto_decode(i_title .value, page_key) ;
       i_remark.value=Crypto_decode(i_remark.value, page_key) ;
    }

         return true ;
  }

  function SetReadOnly() 
  {
    var  i_save1 ;
    var  i_save2 ;
    var  i_form ;
    var  i_pctrl ;

       i_save1 =document.getElementById("Save1") ;
       i_save2 =document.getElementById("Save2") ;
       i_form  =document.getElementById("Form") ;
       i_pctrl =document.getElementById("PageControl") ;

       i_title .readOnly=true ;
       i_remark.readOnly=true ;
       i_save1 .disabled=true ;
       i_save2 .disabled=true ;

       i_form  .removeChild(i_pctrl) ;
  }

  function SendFields() 
  {
     var  error_text ;

	error_text=""

       i_table.rows[1].cells[0].style.color="black"   ;
     
     if(i_title.value=="") {
       i_table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Заголовок'" ;
     }

     if(error_text!="")
     {
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;
        return false ;
     }

     if(page_key=="") 
     {
//        page_key    =GetRandomString(64) ;
          page_key    ="PageKey_"+GetRandomString(32) ;  // DEBUG only
          check_key   =Check_generate() ;
        i_crypto.value=Crypto_encode( page_key, password) ;
        i_check.value =Crypto_encode(check_key, page_key) ;
     }
     else
     {
        i_crypto.value="" ;
     }

     if(               i_check .value  == "" ||
        Check_validate(i_check .value)===true  )
     {
             error_text=error_text+"<br>Ошибка крипто-системы. Попробуйте перезагрузить страницу." ;
     }

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

       i_title .value=Crypto_encode(i_title .value, page_key) ;
       i_remark.value=Crypto_encode(i_remark.value, page_key) ;

                         return true ;
  } 

  function NewPage() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("client_page.php"+"?Session="+v_session+"&NewPage=1") ;
  } 

  function MainPage() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("client_card.php"+"?Session="+v_session) ;
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
        <b>ДОПОЛНИТЕЛЬНЫЙ РАЗДЕЛ ПАЦИЕНТА</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div hight=20%><br></div>
  <form onsubmit="return SendFields();" method="POST" id="Form">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Сохранить" id="Save1"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="field"> Заголовок </td>
      <td> <input type="text" size=60 name="Title" id="Title"> </td>
    </tr>
    <tr>
      <td class="field"> Примечание </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Remark" id="Remark"></textarea>
      </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td>
        <input type="hidden" name="Page"   id="Page"> 
        <input type="hidden" name="Check"  id="Check"> 
        <input type="hidden" name="Crypto" id="Crypto">
        <input type="hidden" name="Update" id="Update">
      </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Сохранить" id="Save2"> </td>
    </tr>
    </tbody>
  </table>

  <ul class="menu" id="PageControl">
    <li><a href="#" onclick=NewPage()  target="_self">Создать новый раздел</a></li> 
    <li><a href="#" onclick=MainPage() target="_self">Вернуться в карточку пациента</a></li> 
  </ul>

  </form>

</div>

</body>

</html>
