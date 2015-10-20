<?php

//============================================== 
//  Чтение файла конфигурации

function ReadConfig() 
{
  global  $glb_cfg_debug ;
  global  $glb_cfg_db_host ;
  global  $glb_cfg_db_user ;
  global  $glb_cfg_db_pswd ;
  global  $glb_cfg_db_name ;
  global  $glb_cfg_log_file ;
  global  $glb_cfg_errors ;


     $file=fopen("config/config.cfg", "r") ;
  if($file===false)  return false ;

   while(!feof($file))
   {
        $row=fgets($file) ;     
     if($row===false)  break ;

	$words=explode("=", $row) ;
        $key  =trim($words[0]) ;
        $value=trim($words[1]) ;

     if($key=="debug"   )  $glb_cfg_debug   =$value ;
     if($key=="db_host" )  $glb_cfg_db_host =$value ;
     if($key=="db_user" )  $glb_cfg_db_user =$value ;
     if($key=="db_pswd" )  $glb_cfg_db_pswd =$value ;
     if($key=="db_name" )  $glb_cfg_db_name =$value ;
     if($key=="log_file")  $glb_cfg_log_file=$value ;
     if($key=="errors"  )  $glb_cfg_errors  =$value ;
   } 

    error_reporting( E_ALL) ;
            ini_set("display_errors", "Off") ;
            ini_set("error_log",      $glb_cfg_errors) ;
            ini_set("log_errors",     "On") ;

   return true ;
}


//============================================== 
//  Выдача отладочного лога на WEB-страницу

function ShowLog($text) 
{
    echo  "document.getElementById(\"DebugLog\").innerHTML+=\"<br>".$text."\" ;" ;
}

//============================================== 
//  Выдача лога

function FileLog($category, $text) 
{
  global  $glb_script ;
  global  $glb_cfg_log_file ;

          $timestamp=date("Y-m-d h:i:s")."  " ;
          $script   =substr($glb_script."                    ", 0, 25)."  " ;
          $port_id  =substr($_SERVER["REMOTE_PORT"]."      ", 0, 6)."  " ;
          $mark     =substr($category."       ", 0, 7)."  " ;

  file_put_contents($glb_cfg_log_file, $timestamp.$port_id.$script.$mark.$text."\r\n", FILE_APPEND | LOCK_EX) ;

}

//============================================== 
//  Выдача в лог содержимого GET и POST

function FileLog_GP() 
{
  foreach($_POST as $key => $value)  FileLog("POST", $key." : ".$value) ;
  foreach($_GET  as $key => $value)  FileLog("GET" , $key." : ".$value) ;
}

//============================================== 
//  Генерация случайной строки

function GetRandomString($length) 
{
    $validCharacters="abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ";
    $validCharNumber= strlen($validCharacters);
 
        $result="" ;
 
    for($i = 0 ; $i < $length ; $i++) {
        $index  =mt_rand(0, $validCharNumber-1) ;
        $result.=$validCharacters[$index] ;
    }
 
    return $result;
}
//============================================== 
//  Формирование пути для файла изображения

function PrepareImagePath($section, $object, $element, $ext) 
{
             $path ="pictures" ;
             $path.="/".$section ;

   if(is_dir($path)==false) {
              $status=mkdir($path, 0700) ;
           if($status==false)  return("") ;
                            }

             $path.="/".$object ;

   if(is_dir($path)==false) {
              $status=mkdir($path, 0700) ;
           if($status==false)  return("") ;
                            }

             $path.="/".$element."_".time().".".$ext ;

  return($path) ;
}
//============================================== 
//  Присоединение к Базе данных

function DbConnect(&$error) 
{
  global  $glb_cfg_db_host ;
  global  $glb_cfg_db_user ;
  global  $glb_cfg_db_pswd ;
  global  $glb_cfg_db_name ;


     $db=new mysqli($glb_cfg_db_host, $glb_cfg_db_user, $glb_cfg_db_pswd, $glb_cfg_db_name) ;
  if(mysqli_connect_errno()) {
          FileLog("ERROR", "DB connect() : ".mysqli_connect_errno()) ;
           $error="Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка соединения с базой данных" ;
                         return(false) ;
  }

     $res=$db->query("SET NAMES cp1251") ;
  if($res===false) {
          FileLog("ERROR", "SET NAMES cp1251 : ".$db->error) ;
           $error="Ошибка на сервере. Повторите попытку позже." ;
                         return(false) ;
  }

     $res=$db->autocommit(FALSE) ;
  if($res===false) {
          FileLog("ERROR", "DB autocommit(FALSE) : ".$db->error) ;
           $error="Ошибка на сервере. Повторите попытку позже." ;
                         return(false) ;
  }

  return($db) ;
}

//============================================== 
//  Идентификация сессии

function DbCheckSession($db, $session, &$options, &$error)
{
                $session=$db->real_escape_string($session) ;

     $res=$db->query("Select s.login, u.options". 
                     "  from sessions s, users u".
                     " Where s.login =u.login".
                     "  and  s.session='$session'") ;
  if($res===false) {
          FileLog("ERROR", "DB query(Select SESSIONS...) : ".$db->error) ;
                            $db->close() ;
            $error="Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка идентификации сессии" ;
                         return(false) ;
  }
  if($res->num_rows==0) {
                            $db->close() ;
          FileLog("CANCEL", "Unknown session detected") ;
            $error="Сессия устарела. Повторите авторизацию." ;
                         return(false) ;
  }

       $fields=$res->fetch_row() ;
               $res->close() ;
         $user=$fields[0] ;
      $options=$fields[1] ;

  return($user) ;
}

//============================================== 
?>
