<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script ="Client_page_pilot.php" ;
   $glb_log_off= true ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение параметров

                         $session=$_GET["Session"] ;
                            $page=$_GET["Page"] ;
                         $filekey=$_GET["Key"] ;

    FileLog("START", "Session:".$session) ;
    FileLog("",      "   Page:".$page) ;

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
//--------------------------- Приведение параметров

           $user_=$db->real_escape_string($user ) ;
           $page_=$db->real_escape_string($page ) ;

//--------------------------- Извлечение ключа страницы

                       $sql="Select  crypto ".
                            "  From `access_list` ".
                            " Where `owner`='$user_' ".
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

      echo     "   page_key='" .$fields[0]."' ;\n" ;

//--------------------------- Извлечение данных страницы

                       $sql="Select id, `check`, title, remark".
                            "  From client_pages".
                            " Where owner='$user_'".
                            "  and  page = $page_" ;
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

                   $page_id=$fields[0] ;
                   $check  =$fields[1] ;
                   $title  =$fields[2] ;
                   $remark =$fields[3] ;

        FileLog("", "User ".$user." additional page ".$page." presented successfully") ;

//--------------------------- Отображение данных на странице

//      echo     "  i_check .value='".$check. "' ;\n" ;

//--------------------------- Извлечение дополнительных блоков

        $tmp_folder=PrepareTmpFolder($session) ;
     if($tmp_folder=="") {
             FileLog("ERROR", "Temporary folder create error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания временной папки") ;
                         return ;
     }

//      echo     "  i_count.value='0' ;	\n" ;

                     $sql="Select e.id, e.type, e.remark, e.file, e.short_file, e.www_link".
			  "  From client_pages_ext e".
			  " Where e.page_id='$page_id'".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGE_EXT... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения расширенного описания") ;
                         return ;
  }
  else
  {  
          $sys_ext_count=$res->num_rows ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

               $spath="" ;

        if($fields[1]=="Image")
        {
                     $spath=$tmp_folder."/".basename($fields[4]) ;
                copy($fields[4], $spath) ;

               $cur_folder=getcwd() ;
                            chdir($tmp_folder) ;

	     $spath  =DecryptFile($spath, $filekey) ;

                            chdir($cur_folder) ;

          if($spath===false) {
               FileLog("ERROR", "IMAGE/FILE small image decrypt error") ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка дешифрования файла картинки") ;
                           return ;
          }

		$spath=substr($spath, strlen($cur_folder)+1) ;
        }

          $sys_ext_id    [$i]= $fields[0] ;
          $sys_ext_type  [$i]= $fields[1] ;
          $sys_ext_remark[$i]= $fields[2] ;
          $sys_ext_sfile [$i]= $spath ;
          $sys_ext_link  [$i]= $fields[5] ;
     }

//      echo     "  i_count.value=".$res->num_rows." ;	\n" ;
  }

     $res->close() ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение дополнительных блоков описания

function ShowExtensions() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;


  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
        $row=$i ;

    if($sys_ext_type[$i]=="Image") {
       echo "<img src='".$sys_ext_sfile[$i]."' height=150 id='Image_".$row."'>	\n" ;
    }

  }

}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;\n" ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;			\n" ;
    echo  "i_error.innerHTML  ='Данные успешно сохранены!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Client Page Pilot</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;    
    var  i_page ;
    var  i_check ;
    var  i_crypto ;
    var  i_count ;
    var  i_error ;
    var  session ;
    var  password ;
    var  page_key ;
    var  check_key ;

  function FirstField() 
  {
    var  v_session ;
    var  i_ext ;
    var  text ;
    
	i_page     =document.getElementById("Page") ;
	i_check    =document.getElementById("Check") ;
	i_crypto   =document.getElementById("Crypto") ;
	i_count    =document.getElementById("Count") ;
	i_error    =document.getElementById("Error") ;

           page_key="" ;

<?php
            ProcessDB() ;
?>

//	parent.frames["processor"].location.assign("z_clear_tmp.php?Session="+session) ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


         error_text=""

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

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

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form"> 
    <input type="hidden" name="Page"    id="Page"> 
    <input type="hidden" name="Check"   id="Check"> 
    <input type="hidden" name="Crypto"  id="Crypto">
    <input type="hidden" name="Count"   id="Count"> 
  </form>

<?php
            ShowExtensions() ;
?>

</body>

</html>
