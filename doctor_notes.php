<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_notes.php" ;

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

          $session =$_GET ["Session"] ;
          $client  =$_GET ["Client"] ;

          $check   =$_POST["Check"] ;
          $category=$_POST["Category"] ;
          $remark  =$_POST["Remark"] ;

  FileLog("START", "Session  :".$session) ;
  FileLog("",      "Client   :".$client) ;
  FileLog("",      "Check    :".$check) ;
  FileLog("",      "Category :".$category) ;
  FileLog("",      "Remark   :".$remark) ;

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

//--------------------------- Получение основного ключа шифрования

                       $sql="Select crypto ".
                            "  From `access_list`".
                            " Where `owner`='$user_' ".
                            "  and  `login`='$user_' ".
                            "  and  `page` =  0 " ;
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

//--------------------------- Приведение данных

          $client_  =$db->real_escape_string($client) ;
          $category_=$db->real_escape_string($category) ;
          $remark_  =$db->real_escape_string($remark) ;

//--------------------------- Извлечение данных для отображения

  if(!isset($check))
  {
                       $sql="Select `check`, `category`, remark".
                            " From `doctor_notes`".
                            " Where `owner`='$user_' and `client`='$client_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select DOCTOR_NOTES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $check   =$fields[0] ;
                   $category=$fields[1] ;
                   $remark  =$fields[2] ;

        FileLog("", "Doctor notes presented successfully") ;
  }
//--------------------------- Сохранение данных со страницы
  else
  {
                       $sql="Update `doctor_notes`".
                            " Set   `category`='$category_'".
                            "      ,`remark`  ='$remark_'".
                            " Where `owner`='$user' and `client`='$client_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update DOCTOR_NOTES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "Doctor notes saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Вывод данных на страницу

      echo     "  i_client  .value=\"" .$client."\" ;\n" ;
      echo     "  i_check   .value=\"" .$check."\" ;\n" ;
      echo     "  i_category.value=\"" .$category."\" ;\n" ;
      echo     "  i_remark  .value=\"" .$remark."\" ;\n" ;

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

<title>DarkMed Doctor Notes</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;
    var  i_client ;
    var  i_check ;
    var  i_category ;
    var  i_remark ;
    var  i_error ;
    var  password ;
    var  page_key ;

  function FirstField() 
  {

       i_table   =document.getElementById("Fields") ;
       i_client  =document.getElementById("Client") ;
       i_check   =document.getElementById("Check") ;
       i_category=document.getElementById("Category") ;
       i_remark  =document.getElementById("Remark") ;
       i_error   =document.getElementById("Error") ;

       i_category.focus() ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

           page_key=Crypto_decode( page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
//	i_error.style.color="red" ;
//	i_error.innerHTML  ="Ошибка расшифровки данных." ;
//         return true ;
     }

       i_category.value=Crypto_decode(i_category.value, page_key) ;
       i_remark  .value=Crypto_decode(i_remark  .value, page_key) ;

         return true ;
  }

  function SendFields() 
  {
     var  i_client_cat ;
     var  error_text ;

	error_text=""
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

       i_client_cat          =parent.frames['section'].document.getElementById(i_client.value+'_category') ;
       i_client_cat.innerHTML=  i_category.value ;

       i_category.value=Crypto_encode(i_category.value, page_key) ;
       i_remark  .value=Crypto_encode(i_remark  .value, page_key) ;

                         return true ;         
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
  <form onsubmit="return SendFields();" method="POST">

  <div class="error" id="Error"></div>
  <table>
    <tr>
      <td class="field">
        <input type="submit" value="Сохранить">
      </td>
      <td class="field">
        <table width="80%" id="Fields">
          <thead>
          </thead>
          <tbody>
          <tr>
            <td class="field"> Категория </td>
            <td class="fieldL"> <input type="text" size=80 name="Category" id="Category"> </td>
          </tr>
          <tr>
            <td class="field"> Примечание </td>
            <td class="fieldL"> 
              <textarea cols=80 rows=4 wrap="soft" name="Remark" id="Remark"> </textarea>
            </td>
          </tr>
          <tr>
            <td>
              <input type="hidden" size=60 name="Client" id="Client">
              <input type="hidden" size=60 name="Check" id="Check">
            </td>
          </tr>
          </tbody>
        </table>
      </td>    
    </tr>
  </table>

  </form>

</div>

</body>

</html>
