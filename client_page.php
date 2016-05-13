<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_page.php" ;

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
  global  $sys_read_only  ;

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

                         $filekey=$_POST["FileKey"] ;

                          $update=$_POST["Update"] ;
  if(isset($update   )) {
                          $crypto=$_POST["Crypto"] ;
                          $extkey=$_POST["ExtKey"] ;
                           $check=$_POST["Check"] ;
                           $title=$_POST["Title"] ;
                          $remark=$_POST["Remark"] ;
                           $count=$_POST["Count"] ;
                          $delete=$_POST["Delete"] ;
                         $reorder=$_POST["ReOrder"] ;
  }

                         $new_order  =$_POST["OrderNew"] ;
                         $ext_edit   =$_POST["ExtEdit"] ;

                               $new_file  = "" ;
                               $new_link  = "" ;

  if( isset($new_order) ||
      isset($ext_edit )   )
  {
                               $new_type  =$_POST["TypeNew"] ;
                               $new_remark=$_POST["RemarkNew"] ;

    if($new_type=="Image" or
       $new_type=="File"    )  $new_file  =$_POST["FileName"] ;

    if($new_type=="Link"    )  $new_link  =$_POST["LinkNew"] ;
  }

    FileLog("START", "Session:".$session) ;
    FileLog("",      "NewPage:".$new_page) ;
    FileLog("",      "  Owner:".$owner) ;
    FileLog("",      "   Page:".$page) ;

  if(isset($update   )) {
    FileLog("",      " Update:".$update) ;
    FileLog("",      "  Check:".$check) ;
    FileLog("",      " Crypto:".$crypto) ;
    FileLog("",      " ExtKey:".$extkey) ;
    FileLog("",      "  Title:".$title) ;
    FileLog("",      " Remark:".$remark) ;
    FileLog("",      "  Count:".$count) ;
    FileLog("",      " Delete:".$delete) ;
    FileLog("",      "ReOrder:".$reorder) ;
  }

  if( isset($new_order))
  {
    FileLog("",      "   OrderNew:".$new_order) ;
    FileLog("",      "    ExtEdit:".$ext_edit) ;
    FileLog("",      "    TypeNew:".$new_type) ;
    FileLog("",      "  RemarkNew:".$new_remark) ;
    FileLog("",      "    FileNew:".$new_file) ;
    FileLog("",      "    LinkNew:".$new_link) ;
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
//--------------------------- Определение владельца страницы

  if(!isset($owner))  $owner=$user ; 

  if($owner!=$user)  $sys_read_only=true ; 
  else               $sys_read_only=false ; 

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
//--------------------------- Режим запроса ключа шифрования файла

  if(!isset($filekey)) {

                       $sql="Select  crypto, ext_key ".
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

      echo     "        page_key='" .$fields[0]."' ;	\n" ;
      echo     "         ext_key='" .$fields[1]."' ;	\n" ;
      echo     "  i_update.value='request' ;		\n" ;

      InfoMsg("Загружается информация...") ;

      FileLog("",     "File key request sent") ;     
      FileLog("STOP", "Done") ;     
         return ;
                       } 
//--------------------------- Приведение параметров

          $crypto_=$db->real_escape_string($crypto) ;
          $extkey_=$db->real_escape_string($extkey) ;
          $check_ =$db->real_escape_string($check ) ;
          $title_ =$db->real_escape_string($title ) ;
          $remark_=$db->real_escape_string($remark) ;

//--------------------------- Первое сохранение новой страницы
//
//  Сохранение допускается только для владельца страницы

  if(!$sys_read_only)
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
                            "`access_list`(`Owner`,  `Login`,  `Page`,   `Crypto`,   `Ext_key`)".
                            "       values('$user_', '$user_', '$page_', '$crypto_', '$extkey_')" ;
       $res=$db->query($sql) ;
    if($res===false) {
               FileLog("ERROR", "Insert ACCESS_LIST... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 1") ;
                           return ;
    }

                       $sql="Insert into ".
                            "`client_pages`(`Owner`,  `Page`,   `Type`,   `Creator`, `Check`,   `Title`  )".
                            "        values('$user_', '$page_', 'client', '$user_',  '$check_', '$title_')" ;
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

  if(!$sys_read_only)
  if(isset($update)          && 
           $update!="request"  )
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

             echo     "   i_page.value='".$page_."' ;	\n" ;

        FileLog("", "User ".$owner." additional page ".$page_." saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Извлечение ключа страницы

                       $sql="Select  crypto, ext_key ".
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

      echo     "   page_key='" .$fields[0]."' ;\n" ;
      echo     "    ext_key='" .$fields[1]."' ;\n" ;

//--------------------------- Извлечение данных страницы

                       $sql="Select id, `check`, title, remark".
                            "  From client_pages".
                            " Where owner='$owner_'".
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

        FileLog("", "User ".$owner." additional page ".$page." presented successfully") ;

//--------------------------- Отображение данных на странице

      echo     "  i_check .value='".$check. "' ;\n" ;
      echo     "  i_title .value='".$title. "' ;\n" ;
      echo     "  i_remark.value='".$remark."' ;\n" ;
      echo     "  i_update.value='update' ;	\n" ;

  if($sys_read_only)
  {
      echo     "  SetReadOnly() ;\n" ;
  }

//--------------------------- Манипуляции с дополнительными блоками

  if(!$sys_read_only)
  if(isset($update)          && 
           $update!="request"  ) {
//- - - - - - - - - - - - - - Удаление блоков
     if($delete!="") {
			$delete=                 substr($delete, 1) ;
                        $delete=$db->real_escape_string($delete) ;

                       $sql="Select file, short_file".
                            "  From client_pages_ext".
                            " Where page_id='$page_id'".
                            "  and  order_num in ($delete)" ;
          $res=$db->query($sql) ;
       if($res===false) {
             FileLog("ERROR", "Select CLIENT_PAGES_EXT... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка выборки удаляемых файлов") ;
                         return ;
       }

       for($i=0, $j=0 ; $i<$res->num_rows ; $i++)
       {
	     $fields=$res->fetch_row() ;

          if($fields[0]!=""        ) {  $delete_file_a[$j]= $fields[0] ;  $j++ ;  }
          if($fields[1]!="" &&
             $fields[1]!=$fields[0]) {  $delete_file_a[$j]= $fields[1] ;  $j++ ;  }
       }

		$res->close() ;

                           $sql="Delete from client_pages_ext".
                                " Where page_id='$page_id'".
                                "  and  order_num in ($delete)" ;
           $res=$db->query($sql) ;
	if($res===false) {
             FileLog("ERROR", "Delete CLIENT_PAGES_EXT... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка удаления дополнительных блоков") ;
                         return ;
	}

	foreach($delete_file_a as $delete_file)
        { 
             DeleteFile($delete_file) ;
        }

            unset($delete_file_a) ;
     }
//- - - - - - - - - - - - - - Перенумерация блоков
     if($reorder!="") {
			$reorder=                 substr($reorder, 1) ;
                        $reorder=$db->real_escape_string($reorder) ;

		$orders_a=explode(",", $reorder) ;	

	foreach($orders_a as $order)
        { 
                            $pair=explode("=", $order) ;

                             $sql="Update client_pages_ext".
                                  "   Set order_num=$pair[1]".
                                  " Where page_id='$page_id'".
                                  "  and  id     = $pair[0]" ;
             $res=$db->query($sql) ;
          if($res===false) {
                FileLog("ERROR", "Update CLIENT_PAGES_EXT(Order_num)... : ".$db->error) ;
                        $db->rollback();
                        $db->close() ;
               ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка перенумерации дополнительных блоков") ;
                            return ;
          }
        }
     }
//- - - - - - - - - - - - - -
		          $db->commit() ;
                      }
//--------------------------- Сохранение дополнительного блока

  if(!$sys_read_only)
  if(isset($new_order))
  {
//- - - - - - - - - - - - - - Сохранение файла картинки или вложения
                     $image="FileNew" ;
                      $path="" ;
                     $spath="" ;

    if($new_file!="")
    if(isset($_FILES[$image]))
    {

           FileLog("", "Image/attachment file detected") ;

        if($new_type=="Image") {  $file_type="Image" ;
                                    $actions="create_short_image" ;
                               }
        else                   {  $file_type="Document" ;
                                    $actions="" ;
                               }

         $path_i=LoadFile($image, $new_file, "client_page", $page_id, $file_type, 
                             $actions, $spath_i, $error) ;
      if($path_i===false) {

             FileLog("ERROR", "IMAGE/FILE : ".$error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ".$error) ;
                         return ;
      }

	   $path  =EncryptFile($path_i, $filekey) ;
        if($path===false) {
               FileLog("ERROR", "IMAGE/FILE encrypt error") ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка шифрования файла") ;
                           return ;
        }

     if($spath_i!="")
     {
	  $spath  =EncryptFile($spath_i, $filekey) ;
       if($spath===false) {
                 FileLog("ERROR", "IMAGE/FILE small image encrypt error") ;
                         $db->rollback();
                         $db->close() ;
                ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка шифрования сокращенного файла") ;
                             return ;
       }

		DeleteFile($spath_i) ;
     }

		DeleteFile($path_i) ;

             FileLog("", "Image/attachment file successfully registered") ;
    }
//- - - - - - - - - - - - - - Сохранение данных блока
          $new_order_ =$db->real_escape_string($new_order) ;
          $new_type_  =$db->real_escape_string($new_type) ;
          $new_remark_=$db->real_escape_string($new_remark) ;
          $new_file_  =$db->real_escape_string($path) ;
          $new_sfile_ =$db->real_escape_string($spath) ;
          $new_link_  =$db->real_escape_string($new_link) ;

     if(isset($ext_edit))
     {
          $ext_edit_=$db->real_escape_string($ext_edit) ;

       if($new_file!="") 
       {
                             $sql="Select file, short_file".
                                  "  From client_pages_ext".
                                  " Where page_id='$page_id'".
                                  "  and       id= $ext_edit_" ;
             $res=$db->query($sql) ;
          if($res===false) {
               FileLog("ERROR", "Select CLIENT_PAGES_EXT for delete ... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка выборки удаляемых файлов") ;
                           return ;
          }

	     $fields=$res->fetch_row() ;

          if($fields[0]!=""        )  $delete_file_a[0]=$fields[0] ;
          if($fields[1]!="" &&
             $fields[1]!=$fields[0])  $delete_file_a[1]=$fields[1] ;

		$res->close() ;
       }

                           $sql ="Update client_pages_ext".
                                 "   Set remark='$new_remark_'" ;
 
        if($new_link!="")  $sql.="       ,www_link='$new_link_'" ;
        if($new_file!="")  $sql.="       ,file='$new_file_', short_file='$new_sfile_'" ;

                           $sql.=" Where page_id= $page_id".
                                 "  and  id     = $ext_edit_" ;


     }
     else
     {
           $sql="Insert into client_pages_ext".
                "       ( page_id, order_num, type, remark, file, short_file, www_link)".
                " Values($page_id,'$new_order_','$new_type_','$new_remark_','$new_file_','$new_sfile_','$new_link_')" ;
     }

        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Insert/Update CLIENT_PAGES_EXT... : ".$db->error) ;
                            $db->close() ;

         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка добавления/изменения блока") ;
                         return ;
     }

     if(isset($delete_file_a)) 
     {
	 foreach($delete_file_a as $delete_file)
         { 
             DeleteFile($delete_file) ;
         }
     }

	          $db->commit() ;

     if(isset($ext_edit))   echo  "  document.getElementById('LiftUp_".$new_order."').focus() ;	\n" ;
     else                   echo  "  document.getElementById('ExtensionType').focus() ;		\n" ;

        FileLog("", "Page extension added successfully") ;
  }
//--------------------------- Извлечение дополнительных блоков

        $tmp_folder=PrepareTmpFolder($session) ;
     if($tmp_folder=="") {
             FileLog("ERROR", "Temporary folder create error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания временной папки") ;
                         return ;
     }

      echo     "  i_count    .value='0' ;	\n" ;
      echo     "  i_new_order.value='0' ;	\n" ;

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

      echo     "  i_count    .value=".$res->num_rows." ;	\n" ;
      echo     "  i_new_order.value=".$res->num_rows." ;	\n" ;
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
  global  $sys_read_only  ;


  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
        $row=$i ;

       echo  "  <tr class='table' id='Row_".$row."'>				\n" ;
       echo  "    <td  class='table' width='10%'>				\n" ;

    if($sys_ext_type[$i]=="Image") {
       echo "<div class='fieldC'>						\n" ;
       echo "<img src='".$sys_ext_sfile[$i]."' height=200 id='Image_".$row."'>	\n" ;
       echo "</div>								\n" ;
       echo "<br>								\n" ;
    }
       echo  "      <input type='hidden' id='Order_".$row."' value='".$row."'>			\n" ;
       echo  "      <input type='hidden' id='Ext_"  .$row."' value='".$sys_ext_id[$i]."'>	\n" ;
       echo  "      <input type='hidden' id='Type_" .$row."' value='".$sys_ext_type[$i]."'>	\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='table'>								\n" ;
       echo  "      <div id='Remark_".$row."'>".$sys_ext_remark[$i]."</div>			\n" ;
       echo  "    <br>										\n" ;

    if($sys_ext_type[$i]=="File") {
       echo  "  <a href='#' id='File_".$row."'>Ссылка на файл</a>	\n" ; 
    }

    if($sys_ext_type[$i]=="Link") {
       echo  "  <a href='#' id='Link_".$row."' onclick=window.open('$LINK$')>".$sys_ext_link[$i]."</a>	\n" ; 
       echo  "  <br>											\n" ;
    }

       echo  "    </td>						\n" ;
       echo  "    <td class='table'>				\n" ;

    if($sys_read_only==false) {
       echo  "      <input type='button' value='Вверх'         id='LiftUp_".$row."' onclick=LiftUpRow('".$row."')>	\n" ;
       echo  "      <br>												\n" ;
       echo  "      <input type='button' value='Редактировать' id='Edit_".$row."'   onclick=EditRow('".$row."')>	\n" ;
       echo  "      <br>												\n" ;
       echo  "      <input type='button' value='Удалить'       id='Delete_".$row."' onclick=DeleteRow('".$row."')>	\n" ;
    }
       echo  "    </td>						\n" ;
       echo  "  </tr>						\n" ;

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

<title>DarkMed Client Card</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;    
    var  i_page ;
    var  i_check ;
    var  i_crypto ;
    var  i_ext_key ;
    var  i_file_key ;
    var  i_title ;
    var  i_remark ;
    var  i_ext_type ;
    var  i_count ;
    var  i_new_order ;
    var  i_delete ;
    var  i_reorder ;
    var  i_update ;
    var  i_error ;
    var  session ;
    var  password ;
    var  page_key ;
    var  ext_key ;
    var  check_key ;

  function FirstField() 
  {
    var  v_session ;
    var  i_ext ;
    var  text ;


	i_table    =document.getElementById("Fields") ;
	i_page     =document.getElementById("Page") ;
	i_check    =document.getElementById("Check") ;
	i_crypto   =document.getElementById("Crypto") ;
	i_ext_key  =document.getElementById("ExtKey") ;
	i_file_key =document.getElementById("FileKey") ;
	i_title    =document.getElementById("Title") ;
	i_remark   =document.getElementById("Remark") ;
	i_ext_type =document.getElementById("ExtensionType") ;
	i_count    =document.getElementById("Count") ;
	i_new_order=document.getElementById("NewOrder") ;
	i_delete   =document.getElementById("Delete") ;
	i_reorder  =document.getElementById("ReOrder") ;
	i_update   =document.getElementById("Update") ;
	i_error    =document.getElementById("Error") ;

	i_title.focus() ;

           page_key="" ;
            ext_key="" ;

<?php
            ProcessDB() ;
?>

    if(i_update.value=='insert')
    {
       document.getElementById("AddExtension").disabled=true ;
        i_error.style.color='blue' ;
        i_error.innerHTML  ='Создание дополнительных блоков будет доступно после первого сохранения' ;
    }

       password=TransitContext("restore", "password", "") ;
        session=TransitContext("restore", "session",  "") ;

    if(ext_key!="")
    {
            ext_key      =Crypto_decode(ext_key, password) ;
         i_file_key.value= ext_key ;

      if(i_update.value=='request') {
                                      document.forms[0].submit() ;
                                          return(true) ;
                                    }
    }

    if(page_key!="")
    {
           page_key=Crypto_decode(page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
	i_error.style.color="red" ;
	i_error.innerHTML  ="Ошибка расшифровки данных." ;
         return true ;
     }

       i_title .value=Crypto_decode(i_title .value, page_key) ;
       i_remark.value=Crypto_decode(i_remark.value, page_key) ;

      for(i=0 ; i<i_count.value ; i++) {
           i_ext          =document.getElementById("Remark_"+i) ;
           i_ext.innerHTML=Crypto_decode(i_ext.innerHTML.replace(/(^\s+|\s+$)/g,''), page_key) ;
           i_ext.innerHTML=              i_ext.innerHTML.replace(/\n+/g,'<br>') ;

           i_ext=document.getElementById("Link_"+i) ;
        if(i_ext!=null) {
              text          = i_ext.innerHTML ;
              text          =Crypto_decode(text, page_key) ;
             i_ext.innerHTML= text ;
             i_ext.onclick  =function(e) { window.open(text) ; } ;
        }

           i_ext=document.getElementById("Image_"+i) ;
        if(i_ext!=null) {
               i_ext.onclick=function(e) {
					    var  id=document.getElementById(this.id.replace("Image", "Ext")).value ;
					   window.open("client_page_image.php?Session="+session+"&Image="+id+"&Key="+ext_key) ; 
                                         } ;
        }

           i_ext=document.getElementById("File_"+i) ;
        if(i_ext!=null) {
               i_ext.onclick=function(e) {
					    var  id=document.getElementById(this.id.replace("File", "Ext")).value ;
					   window.open("client_page_file.php?Session="+session+"&File="+id+"&Key="+ext_key) ; 
                                         } ;
        }
      }  
    }

	 v_session=TransitContext("restore","session","") ;
	parent.frames["processor"].location.assign("z_clear_tmp.php?Session="+v_session) ;

         return true ;
  }

  function SetReadOnly() 
  {
    var  i_form ;
    var  i_pctrl ;

       i_form  =document.getElementById("Form") ;
       i_pctrl =document.getElementById("PageControl") ;
       i_form  .removeChild(i_pctrl) ;

       i_title   .readOnly=true ;
       i_remark  .readOnly=true ;
       i_ext_type.disabled=true ;

       document.getElementById("Save1"       ).disabled=true ;
       document.getElementById("AddExtension").disabled=true ;
  }

  function SendFields() 
  {
     var  i_new ;
     var  file_name ;
     var  pos ;
     var  error_text ;


      if(i_update.value=='request')  return(true) ;

	error_text=""

       i_table.rows[1].cells[0].style.color="black"   ;
     
     if(i_title.value=="") {
       i_table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Заголовок'" ;
     }

      i_new=document.getElementById("ExtEdit") ;
   if(i_new==null)
   {
        i_ext_type=document.getElementById("TypeNew") ;
     if(i_ext_type!=null) {

       if(i_ext_type.value=='Image') {
            i_new=document.getElementById("FileNew") ;
         if(i_new.value=="")    error_text="Не выбран файла изображения" ;
                                     }

       if(i_ext_type.value=='File') {
            i_new=document.getElementById("FileNew") ;
         if(i_new.value=="")    error_text="Не выбран прикрепляемый файл" ;
                                    }
     }
   }

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="") {
          i_new=document.getElementById("ErrorExt") ;
       if(i_new!=null) {  i_new.style.color="red" ;
			  i_new.innerHTML  = error_text ;  }
                              return false ;
     }

     if(page_key=="")
     {
//        page_key    =GetRandomString(64) ;
          page_key    ="PageKey_"+GetRandomString(32) ;  // DEBUG only
          check_key   =Check_generate() ;
        i_crypto.value=Crypto_encode( page_key, password) ;
        i_check.value =Crypto_encode(check_key, page_key) ;

//        ext_key      =GetRandomString(64) ;
          ext_key      ="ExtKey_"+GetRandomString(32) ;  // DEBUG only
        i_ext_key.value=Crypto_encode(ext_key, password) ;
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

        i_new=document.getElementById("RemarkNew") ;
     if(i_new!=null) 
        i_new.value=Crypto_encode(i_new.value, page_key) ;
 
        i_new=document.getElementById("LinkNew") ;
     if(i_new!=null) 
        i_new.value=Crypto_encode(i_new.value, page_key) ;
 
        i_new=document.getElementById("FileNew") ;
     if(i_new!=null) {
           file_name=i_new.value ;
                 pos=file_name.lastIndexOf('\\') ;
             if(pos>=0)  file_name=file_name.substr(pos+1) ;

           document.getElementById("FileName").value=file_name ;
     }

	i_delete .value="" ;
	i_reorder.value="" ;

    for(row=0 ; row<i_count.value ; row++) {

	      i_new=document.getElementById("Ext_"+row) ;
	      i_elm=document.getElementById("Order_"+row) ;

           if(i_elm      ==null)  i_delete .value+=","+row ;
      else if(i_elm.value!= row)  i_reorder.value+=","+i_new.value+"="+i_elm.value ;
    }

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

  function AddNewExtension()
  {
     var  ext_type ;
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_add_new ;
     var  i_elm ;


         ext_type=i_ext_type.value ;

       i_set     = document.getElementById("Extensions") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;
       i_row_new . id        = "ExtensionNew" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id        ='OrderNew' ;
       i_fld_new . name      ='OrderNew' ;
       i_fld_new . type      ="hidden" ;
       i_fld_new . value     =i_new_order.value ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id        ='TypeNew' ;
       i_fld_new . name      ='TypeNew' ;
       i_fld_new . type      ="hidden" ;
       i_fld_new . value     = ext_type ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("div") ;
       i_fld_new . id        ='ErrorExt' ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("textarea") ;
       i_fld_new . id        ='RemarkNew' ;
       i_fld_new . name      ='RemarkNew' ;
       i_fld_new . cols      = 60 ;
       i_fld_new . rows      =  7 ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;

     if(ext_type=="Image") {
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='FileNew' ;
       i_fld_new . name     ='FileNew' ;
       i_fld_new . type     ="file" ;
       i_fld_new . accept   ="image/*" ;
       i_col_new . appendChild(i_fld_new) ;
     }
     if(ext_type=="File") {
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='FileNew' ;
       i_fld_new . name     ='FileNew' ;
       i_fld_new . type     ="file" ;
       i_col_new . appendChild(i_fld_new) ;
     }
     if(ext_type=="Link") {
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='LinkNew' ;
       i_fld_new . name     ='LinkNew' ;
       i_fld_new . type     ="text" ;
       i_fld_new . maxlength=510 ;
       i_fld_new . size     = 50 ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type    ="button" ;
       i_shw_new . value   ="Проверить" ;
       i_shw_new . onclick = function(e) {  GoToLink('LinkNew') ;  }
       i_col_new . appendChild(i_fld_new) ;
       i_col_new . appendChild(i_shw_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
     }

       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_add_new = document.createElement("input") ;
       i_add_new . type   ="submit" ;
       i_add_new . value  ="Сохранить" ;
       i_col_new . appendChild(i_add_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="Удалить" ;
       i_del_new . onclick= function(e) {  DeleteNew() ;  }
       i_col_new . appendChild(i_del_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_set     . appendChild(i_row_new) ;

       document.getElementById("AddExtension" ).disabled=true ;
       document.getElementById("ExtensionType").disabled=true ;

    for(row=0 ; row<i_count.value ; row++) {
	 i_elm=document.getElementById("Delete_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
	 i_elm=document.getElementById("Edit_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
    }

       document.getElementById("RemarkNew").focus() ;

    return ;         
  } 

  function DeleteNew()
  {
    var  i_set  ;
    var  i_row  ;
    var  i_elm  ;

	i_set=document.getElementById("Extensions") ;
	i_row=document.getElementById("ExtensionNew") ;

        i_set.removeChild(i_row) ;

       document.getElementById("AddExtension" ).disabled=false ;
       document.getElementById("ExtensionType").disabled=false ;

    for(row=0 ; row<i_count.value ; row++) {
	 i_elm=document.getElementById("LiftUp_"+row) ;
      if(i_elm!=null)  i_elm.disabled=false ;
	 i_elm=document.getElementById("Delete_"+row) ;
      if(i_elm!=null)  i_elm.disabled=false ;
	 i_elm=document.getElementById("Edit_"+row) ;
      if(i_elm!=null)  i_elm.disabled=false ;
    }

     return ;
  } 

  function EditRow(p_row)
  {
     var  ext_type ;
     var  i_set ;
     var  i_row_old ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_add_new ;
     var  i_elm ;


        ext_type = document.getElementById("Type_"+p_row).value ;

       i_set     = document.getElementById("Extensions") ;
       i_row_old = document.getElementById("Row_"+p_row) ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;
       i_row_new . id        = "ExtensionNew" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_txt_new = document.createTextNode("Редактирование предыдущей записи") ;
       i_col_new . appendChild(i_txt_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id        ='ExtEdit' ;
       i_fld_new . name      ='ExtEdit' ;
       i_fld_new . type      ="hidden" ;
       i_fld_new . value     =document.getElementById("Ext_"+p_row).value ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id        ='OrderNew' ;
       i_fld_new . name      ='OrderNew' ;
       i_fld_new . type      ="hidden" ;
       i_fld_new . value     =document.getElementById("Order_"+p_row).value ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id        ='TypeNew' ;
       i_fld_new . name      ='TypeNew' ;
       i_fld_new . type      ="hidden" ;
       i_fld_new . value     = ext_type ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("div") ;
       i_fld_new . id        ='ErrorExt' ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("textarea") ;
       i_fld_new . id        ='RemarkNew' ;
       i_fld_new . name      ='RemarkNew' ;
       i_fld_new . cols      = 60 ;
       i_fld_new . rows      =  7 ;
       i_fld_new . value     = document.getElementById("Remark_"+p_row).innerHTML.trim() ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

     if(ext_type=="Image") {
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='FileNew' ;
       i_fld_new . name     ='FileNew' ;
       i_fld_new . type     ="file" ;
       i_fld_new . accept   ="image/*" ;
       i_col_new . appendChild(i_fld_new) ;
     }
     if(ext_type=="File") {
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='FileNew' ;
       i_fld_new . name     ='FileNew' ;
       i_fld_new . type     ="file" ;
       i_col_new . appendChild(i_fld_new) ;
     }
     if(ext_type=="Link") {
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='LinkNew' ;
       i_fld_new . name     ='LinkNew' ;
       i_fld_new . type     ="text" ;
       i_fld_new . maxlength=510 ;
       i_fld_new . size     = 50 ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type    ="button" ;
       i_shw_new . value   ="Проверить" ;
       i_shw_new . onclick = function(e) {  GoToLink('LinkNew') ;  }
       i_col_new . appendChild(i_fld_new) ;
       i_col_new . appendChild(i_shw_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
     }

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_add_new = document.createElement("input") ;
       i_add_new . type   ="submit" ;
       i_add_new . value  ="Сохранить" ;
       i_col_new . appendChild(i_add_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="Отменить" ;
       i_del_new . onclick= function(e) {  DeleteNew() ;  }
       i_col_new . appendChild(i_del_new) ;
       i_row_new . appendChild(i_col_new) ;

    if(i_row_old.nextSibling!=null)
          i_set.insertBefore(i_row_new, i_row_old.nextSibling) ;
    else  i_set.appnedChild (i_row_new) ;

       document.getElementById("AddExtension" ).disabled=true ;
       document.getElementById("ExtensionType").disabled=true ;

    for(row=0 ; row<i_count.value ; row++) {
	 i_elm=document.getElementById("LiftUp_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
	 i_elm=document.getElementById("Delete_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
	 i_elm=document.getElementById("Edit_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
    }

       document.getElementById("RemarkNew").focus() ;

    return ;         
  } 

  function DeleteRow(p_row)
  {
    var  i_set  ;
    var  i_row  ;
    var  i_elm  ;
    var  order  ;


	i_set=document.getElementById("Extensions") ;
	i_row=document.getElementById("Row_"+p_row) ;
	i_elm=document.getElementById("Order_"+p_row) ;
        order=parseInt(i_elm.value) ;

	i_set.removeChild(i_row) ;

    for(row=0 ; row<i_count.value ; row++) {

	 i_elm=document.getElementById("Order_"+row) ;
      if(i_elm!=null)
       if(parseInt(i_elm.value)>order)  i_elm.value=parseInt(i_elm.value)-1 ;
    }

        i_new_order.value=parseInt(i_new_order.value)-1 ;

     return ;
  } 

  function LiftUpRow(p_row)
  {
    var  i_set ;
    var  i_row1 ;
    var  i_row2 ;
    var  i_ord1 ;
    var  i_ord2 ;
    var  order ;


	i_set =document.getElementById("Extensions") ;
	i_row2=document.getElementById("Row_"+p_row) ;
	i_ord2=document.getElementById("Order_"+p_row) ;
        order =parseInt(i_ord2.value) ;

     if(order==0)  return ;  

    for(row=0 ; row<i_count.value ; row++) {

	 i_ord1=document.getElementById("Order_"+row) ;
      if(i_ord1!=null)
       if(parseInt(i_ord1.value)==order-1) {  i_row1=document.getElementById("Row_"+row) ; 
                                                         break ;                            }
    }

         i_ord1.value=order ; 
         i_ord2.value=order-1 ; 

         i_set .insertBefore(i_row2, i_row1) ;

     return ;
  } 

  function GoToLink(p_link) 
  {
    window.open(document.getElementById(p_link).value) ;
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

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form">

  <ul class="menu" id="PageControl">
    <li><a href="#" onclick=NewPage()  target="_self">Создать новый раздел</a></li> 
    <li><a href="#" onclick=MainPage() target="_self">Вернуться в карточку пациента</a></li> 
  </ul>

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
        <input type="hidden" name="Page"    id="Page"> 
        <input type="hidden" name="Check"   id="Check"> 
        <input type="hidden" name="Crypto"  id="Crypto">
        <input type="hidden" name="ExtKey"  id="ExtKey">
        <input type="hidden" name="Update"  id="Update">
        <input type="hidden" name="FileKey" id="FileKey">
      </td>
    </tr>
    </tbody>
  </table>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Extensions">

<?php
            ShowExtensions() ;
?>

    </tbody>
  </table>

  <br>
  <div class="fieldC">
    <select name="ExtensionType" id="ExtensionType"> 
      <option value="Image">Картинка с пояснением</option>
      <option value="Link">Ссылка с пояснением</option>
      <option value="File">Файл с пояснением</option>
      <option value="Text">Текстовой блок</option>
    </select> 
    <input type="button" value="Добавить" onclick=AddNewExtension() id="AddExtension">
    <input type="hidden" name="Count" id="Count">
    <input type="hidden" name="NewOrder" id="NewOrder">
    <input type="hidden" name="FileName" id="FileName">
    <input type="hidden" name="ReOrder" id="ReOrder">
    <input type="hidden" name="Delete" id="Delete">
  </div>
  <br>
  <br>

  </form>

</div>

</body>

</html>
