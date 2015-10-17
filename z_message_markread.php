<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Z_message_markread.php" ;

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
                        $message=$_GET ["Message"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "    Message:".$message) ;

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

//--------------------------- Установка на сообщение метки "Прочитано"

       $message_=$db->real_escape_string($message) ;

                     $sql="Update messages ".
                          "   Set `read`='Y' ".
			  " Where receiver='$user_'".
			  "  and  id      = $message_" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Update MESSAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка изменения статуса сообщения") ;
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
    echo  "i_error.innerHTML  ='Сообщение помечено как прочитанное' ;	\n" ;
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

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
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
