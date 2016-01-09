<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_z_accept_access.php" ;

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

                        $session=$_GET["Session"] ;
                        $message=$_GET["Message"] ;
                        $details=$_GET["Details"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "    Message:".$message) ;
  FileLog("",      "    Details:".$details) ;

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

       $user_=$db->real_escape_string($user) ;

//--------------------------- Регистрация новых доступов
//- - - - - - - - - - - - - - Перебор страниц, по которым предоставлен доступ
	$words=explode(" ", $details) ;

    for($i=0 ; $i<count($words) ; $i=$i+2)
    {
       if($words[$i]=="")  break ;

          $page   =$words[$i  ] ;
          $key    =$words[$i+1] ;
          $page_  =$db->real_escape_string($page) ;
          $key_   =$db->real_escape_string($key) ;
//- - - - - - - - - - - - - - Проверка повторного задания доступа
                       $sql="Select page ".
                            "from  `access_list` ".
                            "where `Owner`='$user_'".
                            " and  `Login`='$user_'".
                            " and  `Page` ='$page_'" ;
        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                       $db->rollback() ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка проверки повторного доступа") ;
                         return ;
     }
     if($res->num_rows!=0) {
			      $res->free() ;
        FileLog("", "Access already granted: ".$user.":".$page." for ".$user) ;
                                 continue ;
     }
//- - - - - - - - - - - - - - Создание записи о доступе
                       $sql="Insert into `access_list`".
                            "(`Owner`, `Login`, `Page`,  `Crypto`) ".
                            "values".
                            "('$user_','$user_','$page_','$key_')" ;
        $res=$db->query($sql) ;
     if($res===false) {
               FileLog("ERROR", "Insert ACCESS_LIST... : ".$db->error) ;
                       $db->rollback() ;
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                           return ;
     }

        FileLog("", "Access successfully granted: ".$user.":".$page." for ".$user) ;
//- - - - - - - - - - - - - - Перебор страниц, по которым предоставлен доступ
    }
//--------------------------- Установка на сообщение метки "Прочитано"

       $message_=$db->real_escape_string($message) ;

                     $sql="Update messages ".
                          "   Set `read`='Y' ".
			  " Where receiver='$user_'".
			  "  and  id      = $message_" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Update MESSAGES... : ".$db->error) ;
                            $db->rollback() ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка изменения статуса сообщения") ;
                         return ;
  }
//--------------------------- Завершение

             $db->commit() ;
	     $db->close() ;

              SuccessMsg() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='ER' ;		\n" ;
    echo  "i_text .value      ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешном исполнении

function SuccessMsg() {

    echo  "i_error.style.color='green' ;		\n" ;
    echo  "i_error.innerHTML  ='GP' ;			\n" ;
    echo  "i_text .value      ='Доступ предоставлен' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed=Mobile Message mark read processor</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script type="text/javascript">
<!--

    var  i_text ;
    var  i_error ;

  function FirstField() 
  {
       i_text =document.getElementById("Text" ) ;
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

         return true ;
  }

  function ShowText() 
  {
	alert(i_text.value) ;
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

<b>
<div color="green" id="Error" onclick=ShowText()>
<font color="green">
GP
</font>
</div>
</b>

<input type="hidden" id="Text"> 

</body>

</html>
