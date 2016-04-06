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
  global  $glb_cfg_encrypt_file ;
  global  $glb_cfg_decrypt_file ;
  global  $glb_cfg_delete_file ;
  global  $glb_cfg_log_file ;
  global  $glb_cfg_errors ;
  global  $glb_cfg_images ;
  global  $glb_cfg_temporary ;
  global  $glb_cfg_email_forms ;


                   $date=date("Y_m_d") ;

     $file=fopen("config/config.cfg", "r") ;
  if($file===false)  return false ;

   while(!feof($file))
   {
        $row=fgets($file) ;     
     if($row===false)  break ;

	$words=explode("=", $row) ;
        $key  =trim($words[0]) ;
        $value=trim($words[1]) ;

     if($key=="debug"       )  $glb_cfg_debug       = $value ;
     if($key=="db_host"     )  $glb_cfg_db_host     = $value ;
     if($key=="db_user"     )  $glb_cfg_db_user     = $value ;
     if($key=="db_pswd"     )  $glb_cfg_db_pswd     = $value ;
     if($key=="db_name"     )  $glb_cfg_db_name     = $value ;
     if($key=="encrypt_file")  $glb_cfg_encrypt_file= $value ;
     if($key=="decrypt_file")  $glb_cfg_decrypt_file= $value ;
     if($key=="delete_file" )  $glb_cfg_delete_file = $value ;
     if($key=="images"      )  $glb_cfg_images      = $value ;
     if($key=="temporary"   )  $glb_cfg_temporary   = $value ;
     if($key=="email_forms" )  $glb_cfg_email_forms = $value ;
     if($key=="email_smtp"  )      $cfg_email_smtp  = $value ;
     if($key=="email_from"  )      $cfg_email_from  = $value ;
     if($key=="log_file"    )  $glb_cfg_log_file    =str_replace("#DATE#", $date, $value) ;
     if($key=="errors"      )  $glb_cfg_errors      =str_replace("#DATE#", $date, $value) ;
   } 

    error_reporting( E_ALL) ;
            ini_set("display_errors", "Off") ;
            ini_set("error_log",      $glb_cfg_errors) ;
            ini_set("log_errors",     "On") ;

            ini_set("SMTP",           $cfg_email_smtp) ;
            ini_set("sendmail_from",  $cfg_email_from) ;

   return true ;
}

//============================================== 
//  Раскладка строки аттрибутов пользователя OPTIONS в массив

function OptionsToArray($options)
{
                $words=explode(";", $options) ;

                              $keys["null"]=true ;
     foreach($words as $word) $keys[$word ]=true ;

                                     $options_a["null"   ]=true ;
     
       if(isset($keys["Tester"  ]))  $options_a["tester" ]=true ;
	  
       if(isset($keys["Client"  ]))  $options_a["user"   ]="Client" ;
  else if(isset($keys["Doctor"  ]))  $options_a["user"   ]="Doctor" ;
  else if(isset($keys["Executor"]))  $options_a["user"   ]="Executor" ;
  else                               $options_a["user"   ]="Anonimous" ;
	  
       if(isset($keys["Support" ]))  $options_a["support"]=true ;

	return($options_a);
}

//============================================== 
//  Выдача отладочного лога на WEB-страницу

function ShowLog($text) 
{
    echo  "document.getElementById('DebugLog').innerHTML+='<br>".$text."' ;" ;
}

//============================================== 
//  Выдача лога

function FileLog($category, $text) 
{
  global  $glb_script ;
  global  $glb_cfg_log_file ;

          $timestamp=date("Y-m-d H:i:s")."  " ;
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
  global  $glb_cfg_images ;


             $path =$glb_cfg_images ;
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
//  Формирование пути временного раздела

function PrepareTmpFolder($session)
{
  global  $glb_cfg_temporary ;


             $path =$glb_cfg_temporary ;
             $path.="/".$session ;

   if(is_dir($path)==false) {
              $status=mkdir($path, 0700) ;
           if($status==false)  return("") ;
                            }

  return($path) ;
}
//============================================== 
//  Очистка и удаление временного раздела

function RemoveTmpFolder($session)
{
  global  $glb_cfg_temporary ;


             $path =$glb_cfg_temporary ;
             $path.="/".$session ;

   if(is_dir($path)!==false)
   {

       $folder=opendir($path) ;

        while($file=readdir($folder))
	{
              $file=$path."/".$file ;
           if(is_file($file))  DeleteFile($file) ;
	}	

              closedir($folder) ;

                 rmdir($path) ;
   }

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
  global  $glb_options_a ;

	
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
          FileLog("CANCEL", "Unknown session detected") ;
            $error="Сессия устарела. Повторите авторизацию." ;
                         return(false) ;
  }

       $fields=$res->fetch_row() ;
               $res->close() ;
         $user=$fields[0] ;
      $options=$fields[1] ;

    $glb_options_a=OptionsToArray($options) ;
	  
  return($user) ;
}

//============================================== 
//  Шифрование файла

function EncryptFile($path, $password)
{
  global  $glb_cfg_encrypt_file ;

    $path_e =$path.".a" ;
    $command=$glb_cfg_encrypt_file ;
    $command=str_replace("#PASSWORD#", $password, $command) ;
    $command=str_replace("#IN#",       $path,     $command) ;
    $command=str_replace("#OUT#",      $path_e,   $command) ;

     $result=system($command) ;
  if($result===false)  return(false) ;

  return($path_e) ;
}

//============================================== 
//  Дешифрование файла

function DecryptFile($path, $password)
{
  global  $glb_cfg_decrypt_file ;

    $path_d =substr($path, 0, -2) ;
    $command=$glb_cfg_decrypt_file ;
    $command=str_replace("#PASSWORD#", $password, $command) ;
    $command=str_replace("#IN#",       $path,     $command) ;
    $command=str_replace("#OUT#",      $path_d,   $command) ;

     $result=system($command) ;
  if($result===false)  return(false) ;

  return($path_d) ;
}

//============================================== 
//  Удаление файла

function DeleteFile($path)
{
  global  $glb_cfg_delete_file ;

  if(       $glb_cfg_delete_file            == ""  ||
     strpos($glb_cfg_delete_file, "<none>")!==false  )
  {
       		unlink($path) ;
  }
  else
  { 
       $command=$glb_cfg_delete_file ;
       $command=str_replace("#PATH#", $path, $command) ;

       $result=system($command) ;
    if($result===false)  return(false) ;
  }

  return(true) ;
}

//============================================== 
//  Загрузка файла

function LoadFile($image, $file_name, $data_segment, $data_elem, $data_type, $options, &$spath, &$error)
{
//--------------------------- Сохранение файла

  if($_FILES[$image]["error"]==0) 
  {

        FileLog("", "Image/attachment file detected") ;

        $pos=strpos($file_name, ".") ;
     if($pos===false)  $ext="" ;
     else              $ext=substr($file_name, $pos+1) ;

        $path=PrepareImagePath($data_segment, $data_elem, $data_type, $ext) ;
     if($path=="") {
                      $error="Path form error" ;
                         return(false) ;
     }
 
     if(move_uploaded_file($_FILES[$image]["tmp_name"], $path)==false) {
                      $error="File save error" ;
                         return(false) ;
     }
  }
  else
  {
                      $error="Transmit error : ".$_FILES[$image]["error"] ;
                         return(false) ;
  }
//--------------------------- Создание уменьшенной копии картинки

  if(strpos($options, "create_short_image")!==false)
  {

            $spath=$path ;
    do 
    {
               $fmt=strtolower($_FILES[$image]["type"]) ;
            if($fmt=="image/png"         )  $image_i=imagecreatefrompng ($path) ; 
       else if($fmt=="image/gif"         )  $image_i=imagecreatefromgif ($path) ; 
       else if($fmt=="image/jpeg"        )  $image_i=imagecreatefromjpeg($path) ; 
       else if($fmt=="image/vnd.wap.wbmp")  $image_i=imagecreatefromwbmp($path) ; 
       else        break ;

       if(!$image_i) {
	                FileLog("ERROR", "Image file read error: ".$path) ;
					break ;
       }
		 
                      $w_image_i=imagesx($image_i) ;
                      $h_image_i=imagesy($image_i) ;

        if($h_image_i<200) {
                                      imagedestroy($image_i) ;
	                FileLog("INFO", "Image to small for reduce") ;
					break ;
        }

                      $h_image_o= 200 ;
                      $w_image_o=$w_image_i*$h_image_o/$h_image_i ;
		 	$image_o=imagecreatetruecolor($w_image_o, $h_image_o) ;
				   imagecopyresampled($image_o, $image_i, 0, 0, 0, 0, 
							 $w_image_o, $h_image_o, $w_image_i, $h_image_i) ;

             $spath=PrepareImagePath($data_segment, $data_elem, $data_type."_short", $ext) ;

            if($fmt=="image/png"         )  imagepng ($image_o, $spath) ; 
       else if($fmt=="image/gif"         )  imagegif ($image_o, $spath) ; 
       else if($fmt=="image/jpeg"        )  imagejpeg($image_o, $spath) ; 
       else if($fmt=="image/vnd.wap.wbmp")  imagewbmp($image_o, $spath) ; 

                                         imagedestroy($image_o) ;
                                         imagedestroy($image_i) ;

    } while(false) ;           

  }
//--------------------------- Построение относительных путей

  if(strpos($options, "relative_path")!==false)
  {

	   $cur_folder=getcwd() ;

		$path =substr($path,  strlen($cur_folder)+1) ;

    if(strpos($options, "create_short_image")!==false)
		$spath=substr($spath, strlen($cur_folder)+1) ;
  }
//---------------------------

  return($path) ;
}

//============================================== 
//  Отправка письма пользователю

function Email($db, $user, $subject, $text, &$error) 
{
//--------------------------- Извлечение адреса пользователя

                $user=$db->real_escape_string($user) ;

                     $sql="Select email From users Where login ='$user'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "User e-mail extract : ".$db->error) ;
                         return(false) ;
  }
  if($res->num_rows==0) {
          FileLog("ERROR", "Unknown user detected : ".$user) ;
                         return(false) ;
  }

       $fields=$res->fetch_row() ;
               $res->close() ;
     $receiver=$fields[0] ;

//--------------------------- Отправка сообщения

       $subject="=?windows-1251?B?".base64_encode($subject)."?=" ;

       $header ="Content-type: text/html; charset=windows-1251\r\n" ;
       $header.="From: GeneralPractice <blackjackal@hotmail.ru>" ;

       $body   = $text ;

       $status=mail($receiver, $subject, $body, $header) ;

//---------------------------

  return(true) ;
}

//============================================== 
//  Отправка пользователю уведомления о сообщении

function Email_msg_notification($db, $user, &$error) 
{
  global  $glb_cfg_email_forms ;

//--------------------------- Считывание файла сообщения

   $text=file_get_contents($glb_cfg_email_forms."/MessageNotification.html") ;
  
//--------------------------- Отправка сообщения

  $status=Email($db, $user, "GeneralPractice.ru - Вас ожидает сообщение", $text, $error) ;

//---------------------------

  return($status) ;
}

//============================================== 
//  Отправка пользователю уведомления о сделанном назначении

function Email_prs_notification($db, $user, &$error) 
{
  global  $glb_cfg_email_forms ;

//--------------------------- Считывание файла сообщения

   $text=file_get_contents($glb_cfg_email_forms."/PrescriptionNotification.html") ;
  
//--------------------------- Отправка сообщения

  $status=Email($db, $user, "GeneralPractice.ru - Вам сделано назначение", $text, $error) ;

//---------------------------

  return($status) ;
}

//============================================== 
//  Отправка пользователю уведомления о приглашении

function Email_inv_notification($db, $user, &$error) 
{
  global  $glb_cfg_email_forms ;

//--------------------------- Считывание файла сообщения

   $text=file_get_contents($glb_cfg_email_forms."/InviteNotification.html") ;

//--------------------------- Отправка сообщения

  $status=Email($db, $user, "GeneralPractice.ru - Вам направлено приглашение", $text, $error) ;

//---------------------------

  return($status) ;
}

//============================================== 
?>
