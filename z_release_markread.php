<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Z_release_markread.php" ;

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
                        $release=$_GET ["Release"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "    Release:".$release) ;

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

       $user_=$db->real_escape_string($user ) ;

//--------------------------- Проверка на повторную установку метки "Прочитано"

       $release_=$db->real_escape_string($release) ;

                     $sql="Select * ".
                          "  From releases_read".
                          " Where release_id=$release_".
                          "  and  user      ='$user_'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Update RELEASE_READ... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка изменения статуса релиза") ;
                         return ;
  }
  if($res->num_rows!=0) {
                           $res->free() ;
                            $db->close() ;
          FileLog("CANCEL", "Release already marked as read") ;
         ErrorMsg("Релиз уже помечен как прочитанный") ;
                         return ;
  }

                     $res->free() ;

//--------------------------- Установка на сообщение метки "Прочитано"

                     $sql="Insert into ".
                          "releases_read(release_id, user)". 
                          "       Values($release_, '$user_')" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Update RELEASE_READ... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка изменения статуса релиза") ;
                         return ;
  }

             $db->commit() ;

              SuccessMsg() ;

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
//  Выдача сообщения об успешном исполнении

function SuccessMsg() {

    echo  "i_error.style.color='green' ;				\n" ;
    echo  "i_error.innerHTML  ='Релиз помечен как прочитанный' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Message mark read processor</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_error ;

  function FirstField() 
  {
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

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

  <div class="error" id="Error"></div>

</body>

</html>
