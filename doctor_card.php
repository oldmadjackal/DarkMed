<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_card.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $sys_portrait ;
  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_file   ;
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

                        $session=$_GET ["Session"] ;
  if(!isset($session))  $session=$_POST["Session"] ;

                         $name_f=$_POST["Name_F"] ;

  if(isset($name_f)) {
                         $name_i=$_POST["Name_I"] ;
                         $name_o=$_POST["Name_O"] ;
                         $spec_a=$_POST["Specialities"] ;
                         $remark=$_POST["Remark"] ;
                        $page_id=$_POST["PageId"] ;
                          $count=$_POST["Count"] ;
                         $delete=$_POST["Delete"] ;
                        $reorder=$_POST["ReOrder"] ;

			  $speciality="" ;
  if(isset($spec_a))
     foreach($spec_a as $tmp) 
       if($tmp!="Dummy")  $speciality=$speciality.$tmp."," ;
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


	FileLog("START", "    Session:".$session) ;

  if(isset($name_f)) {
	FileLog("",      "     Name_F:".$name_f) ;
	FileLog("",      "     Name_I:".$name_i) ;
	FileLog("",      "     Name_O:".$name_o) ;
	FileLog("",      " Speciality:".$speciality) ;
	FileLog("",      "     Remark:".$remark) ;
	FileLog("",      "     PageId:".$page_id) ;
        FileLog("",      "      Count:".$count) ;
        FileLog("",      "     Delete:".$delete) ;
        FileLog("",      "    ReOrder:".$reorder) ;
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

          $user_=$db->real_escape_string($user) ;

//--------------------------- Извлечение списка специальностей

                     $sql="Select code, name".
			  "  From `ref_doctor_specialities`".
			  " Where `language`='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_DOCTOR_SPECIALITIES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника специальностей") ;
                         return ;
  }
  else
  {  
       echo "   a_specialities[\"Dummy\"]=\"\" ;\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_specialities[\"".$fields[0]."\"]=\"".$fields[1]."\" ;\n" ;
     }
  }

     $res->close() ;

//--------------------------- Сохранение данных врача

  if(isset($name_f))
  {
//- - - - - - - - - - - - - - Сохранение реквизитов
          $name_f_=$db->real_escape_string($name_f) ;
          $name_i_=$db->real_escape_string($name_i) ;
          $name_o_=$db->real_escape_string($name_o) ;
          $spec_  =$db->real_escape_string($speciality) ;
          $remark_=$db->real_escape_string($remark) ;

                                    $confirmed='N' ;
    if($name_f!="" && $name_i!="")  $confirmed='Y' ;

                       $sql="Update doctor_page_main".
                            " Set   name_f    ='$name_f_'".
                            "      ,name_i    ='$name_i_'".
                            "      ,name_o    ='$name_o_'".
                            "      ,speciality='$spec_'".
                            "      ,remark    ='$remark_'".
                            "      ,confirmed =if(confirmed!='D','$confirmed',confirmed)".
                            " Where owner='$user'"  ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update DOCTOR_PAGE_MAIN... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }
//- - - - - - - - - - - - - - Сохранение файла портрета
                     $image="PortraitFile" ;

    if(isset($_FILES[$image])) {

      if($_FILES[$image]["error"]==0) {

           FileLog("", "Portrait file detected") ;

             $pos=strpos($_FILES[$image]["type"], "/") ;
             $ext=substr($_FILES[$image]["type"], $pos+1) ;
            $path=PrepareImagePath("doctor", $page_id, "portrait", $ext) ;

        if($path=="") {
             FileLog("ERROR", "IMAGE Portraite path form error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка резервирования места для файла портрета") ;
                         return ;
        }
 
        if(@move_uploaded_file($_FILES[$image]["tmp_name"], $path)==false) {
             FileLog("ERROR", "IMAGE Portraite save error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка сохранения файла портрета") ;
                         return ;
        }

                           $sql="Update doctor_page_main".
                                " Set   portrait='$path'".
                                " Where owner='$user'"  ;
           $res=$db->query($sql) ;
        if($res===false) {
             FileLog("ERROR", "Update DOCTOR_PAGE_MAIN (Portrait)... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка регистрации файла портрета") ;
                         return ;
        }

             FileLog("", "Portrait file successfully registered") ;
      }
      else {
             FileLog("ERROR", "IMAGE Portraite transmit error : ".$_FILES[$image]["error"]) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка получения файла портрета") ;
                         return ;
      }
    }
//- - - - - - - - - - - - - -
          $db->commit() ;

        FileLog("", "Doctor main page saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Извлечение данных врача

       $res=$db->query("Select id, name_f, name_i, name_o, speciality, remark, portrait".
                       " From  doctor_page_main".
                       " Where owner='$user_'" 
                      ) ;
    if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                  $page_id=$fields[0] ;
                   $name_f=$fields[1] ;
                   $name_i=$fields[2] ;
                   $name_o=$fields[3] ;
               $speciality=$fields[4] ;
                   $remark=$fields[5] ;
             $sys_portrait=$fields[6] ;

        FileLog("", "Doctor main page presented successfully") ;

//--------------------------- Манипуляции с дополнительными блоками

  if(isset($delete))
  {
//- - - - - - - - - - - - - - Удаление блоков
     if($delete!="") {
			$delete=                 substr($delete, 1) ;
                        $delete=$db->real_escape_string($delete) ;

                       $sql="Delete from doctor_page_ext".
                            " Where page_id=$page_id".
                            "  and  order_num in ($delete)" ;
          $res=$db->query($sql) ;
       if($res===false) {
             FileLog("ERROR", "Delete DOCTOR_PAGE_EXT... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка удаления дополнительных блоков") ;
                         return ;
       }

     }
//- - - - - - - - - - - - - - Перенумерация блоков
     if($reorder!="") {
			$reorder=                 substr($reorder, 1) ;
                        $reorder=$db->real_escape_string($reorder) ;

		$orders_a=explode(",", $reorder) ;	

	foreach($orders_a as $order)
        { 
                            $pair=explode("=", $order) ;

                             $sql="Update doctor_page_ext".
                                  "   Set order_num=$pair[1]".
                                  " Where page_id=$page_id".
                                  "  and  id     =$pair[0]" ;
             $res=$db->query($sql) ;
          if($res===false) {
                FileLog("ERROR", "Update DOCTOR_PAGE_EXT(Order_num)... : ".$db->error) ;
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

  if(isset($new_order))
  {
//- - - - - - - - - - - - - - Сохранение файла картинки или вложения
                     $image="FileNew" ;
                      $path="" ;
                     $spath="" ;

    if($new_file!="")
    if(isset($_FILES[$image])) {

      if($_FILES[$image]["error"]==0) {

           FileLog("", "Image/attachment file detected") ;

             $pos=strpos($new_file, ".") ;
          if($pos===false)  $ext="" ;
          else              $ext=substr($new_file, $pos+1) ;

        if($new_type=="Image")
              $path=PrepareImagePath("doctor", $page_id, "Image",    $ext) ;
        else  $path=PrepareImagePath("doctor", $page_id, "Document", $ext) ;

        if($path=="") {
             FileLog("ERROR", "IMAGE/FILE path form error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка резервирования места для файла") ;
                         return ;
        }
 
        if(@move_uploaded_file($_FILES[$image]["tmp_name"], $path)==false) {
             FileLog("ERROR", "IMAGE/FILE save error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка сохранения файла") ;
                         return ;
        }

             FileLog("", "Image/attachment file successfully registered") ;
      }
      else {
             FileLog("ERROR", "IMAGE/FILE transmit error : ".$_FILES[$image]["error"]) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка получения файла") ;
                         return ;
      }
//- - - - - - - - - - - - - - Создание уменьшенной копии картинки
            $spath=$path ;

      if($new_type=="Image") {

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
	             FileLog("ERROR", "Image to small for reduce") ;
					break ;
                }

                      $h_image_o= 200 ;
                      $w_image_o=$w_image_i*$h_image_o/$h_image_i ;
		 	$image_o=imagecreatetruecolor($w_image_o, $h_image_o) ;
				   imagecopyresampled($image_o, $image_i, 0, 0, 0, 0, 
							 $w_image_o, $h_image_o, $w_image_i, $h_image_i) ;

            $spath=PrepareImagePath("doctor", $page_id, "Image_short", $ext) ;

                if($fmt=="image/png"         )  imagepng ($image_o, $spath) ; 
           else if($fmt=="image/gif"         )  imagegif ($image_o, $spath) ; 
           else if($fmt=="image/jpeg"        )  imagejpeg($image_o, $spath) ; 
           else if($fmt=="image/vnd.wap.wbmp")  imagewbmp($image_o, $spath) ; 

                                             imagedestroy($image_o) ;
                                             imagedestroy($image_i) ;

        } while(false) ;           

      }
//- - - - - - - - - - - - - - Построение относительных путей
               $cur_folder=getcwd() ;

	$path =substr($path,  strlen($cur_folder)+1) ;
	$spath=substr($spath, strlen($cur_folder)+1) ;
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

                           $sql ="Update doctor_page_ext".
                                 "   Set remark='$new_remark_'" ;
 
        if($new_link!="")  $sql.="       ,www_link='$new_link_'" ;
        if($new_file!="")  $sql.="       ,file='$new_file_', short_file='$new_sfile_'" ;

                           $sql.=" Where page_id=$page_id".
                                 "  and  id     =$ext_edit_" ;
     }
     else
     {
           $sql="Insert into doctor_page_ext".
                "       ( page_id, order_num, type, remark, file, short_file, www_link)".
                " Values($page_id,'$new_order_','$new_type_','$new_remark_','$new_file_','$new_sfile_','$new_link_')" ;
     }

        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Insert/Update DOCTOR_PAGE_EXT... : ".$db->error) ;
                            $db->close() ;

         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка добавления/изменения блока") ;
                         return ;
     }

          $db->commit() ;

     if(isset($ext_edit))   echo  "  document.getElementById('LiftUp_".$new_order."').focus() ;	\n" ;
     else                   echo  "  document.getElementById('ExtensionType').focus() ;		\n" ;

        FileLog("", "Doctor card extension added successfully") ;
  }

//--------------------------- Извлечение дополнительных блоков

      echo     "  i_count    .value='0' ;	\n" ;
      echo     "  i_new_order.value='0' ;	\n" ;

                     $sql="Select e.id".
			  "      ,e.type, e.remark, e.file, e.short_file, e.www_link".
			  "  From doctor_page_ext e".
			  " Where e.page_id='$page_id'".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_EXT... : ".$db->error) ;
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

          $sys_ext_id    [$i]= $fields[0] ;
          $sys_ext_type  [$i]= $fields[1] ;
          $sys_ext_remark[$i]= $fields[2] ;
          $sys_ext_file  [$i]= $fields[3] ;
          $sys_ext_sfile [$i]= $fields[4] ;
          $sys_ext_link  [$i]= $fields[5] ;
     }

      echo     "  i_count    .value=".$res->num_rows." ;	\n" ;
      echo     "  i_new_order.value=".$res->num_rows." ;	\n" ;

  }

     $res->close() ;

//--------------------------- Отображение данных на форме

      echo     "  i_page_id.value='".$page_id."' ;	\n" ;
      echo     "  i_name_f .value='".$name_f ."' ;	\n" ;
      echo     "  i_name_i .value='".$name_i ."' ;	\n" ;
      echo     "  i_name_o .value='".$name_o ."' ;	\n" ;
      echo     "  i_remark .value='".$remark ."' ;	\n" ;

		$speciality_a=explode(",", $speciality) ;	
                  $spec_first= true ;

	foreach($speciality_a as $spec)
         if(strlen($spec)>1 or $spec_first)
         { 
             echo "  AddNewSpeciality('" .$spec."') ;\n" ;
                  $spec_first=false ;
         }
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
  global  $sys_ext_file   ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;


  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
        $row=$i ;

       echo  "  <tr class='table' id='Row_".$row."'>			\n" ;
       echo  "    <td  class='table'>					\n" ;

    if($sys_ext_type[$i]=="Image") {
       echo "<div class='fieldC'>					\n" ; 
       echo "<img src='".$sys_ext_sfile[$i]."' height=200		\n" ;
       echo " onclick=\"window.open('".$sys_ext_file[$i]."')\" ;	\n" ;
       echo ">								\n" ; 
       echo "</div>							\n" ; 
       echo "<br>							\n" ;
    }

       echo  "      <input type='hidden' id='Order_".$row."' value='".$row."'>			\n" ;
       echo  "      <input type='hidden' id='Ext_"  .$row."' value='".$sys_ext_id[$i]."'>	\n" ;
       echo  "      <input type='hidden' id='Type_" .$row."' value='".$sys_ext_type[$i]."'>	\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='table'>								\n" ;
       echo  "      <div id='Remark_".$row."'>							\n" ;
       echo  htmlspecialchars(stripslashes($sys_ext_remark[$i]), ENT_COMPAT, "windows-1251") ;
       echo  "      </div>									\n" ;
       echo  "    <br>										\n" ;

    if($sys_ext_type[$i]=="File") {
       echo  "  <a href='".$sys_ext_file[$i]."'>Ссылка на файл</a>	\n" ; 
    }

    if($sys_ext_type[$i]=="Link") {

                        $name=$sys_ext_link[$i] ;
                         $pos= strpos($name, "://") ;
      if($pos!==false)  $name= substr($name, $pos+3) ;
                         $pos= strpos($name, "/") ;
      if($pos!==false)  $name= substr($name, 0, $pos) ;

       echo  "  <a href='#' onclick=window.open('".$sys_ext_link[$i]."')>".$name."</a>	\n" ; 
       echo  "  <br>									\n" ;
    }

       echo  "    </td>						\n" ;
       echo  "    <td class='table' width='10%'>		\n" ;
       echo  "      <input type='button' value='Вверх'         id='LiftUp_".$row."' onclick=LiftUpRow('".$row."')>	\n" ;
       echo  "      <br>												\n" ;
       echo  "      <input type='button' value='Редактировать' id='Edit_".$row."'   onclick=EditRow('".$row."')>	\n" ;
       echo  "      <br>												\n" ;
       echo  "      <input type='button' value='Удалить'       id='Delete_".$row."' onclick=DeleteRow('".$row."')>	\n" ;
       echo  "    </td>						\n" ;
       echo  "  </tr>						\n" ;

  }

}

//============================================== 
//  Отображение портрета

function PortraitView() {

  global  $sys_portrait ;

   if($sys_portrait=="")  echo "<img src=\"images/dummy.jpg\" height=200>" ;
   else                   echo "<img src=\"".$sys_portrait."\" height=200>" ; 
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

<title>DarkMed Doctor Card</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_table ;
    var  i_page_id ;
    var  i_name_f ;
    var  i_name_i ;
    var  i_name_o ;
    var  i_remark ;
    var  i_portrait ;
    var  i_p_file ;
    var  i_ext_type ;
    var  i_count ;
    var  i_new_order ;
    var  i_delete ;
    var  i_reorder ;
    var  i_error ;
    var  a_specialities ;

  function FirstField() 
  {
    var  i_list_new ;
    var  i_link_new ;
    var  i_text_new ;
    var  link_key ;
    var  link_text ;


	i_table    =document.getElementById("Fields") ;
	i_page_id  =document.getElementById("PageId") ;
	i_name_f   =document.getElementById("Name_F") ;
	i_name_i   =document.getElementById("Name_I") ;
	i_name_o   =document.getElementById("Name_O") ;
	i_remark   =document.getElementById("Remark") ;
	i_portrait =document.getElementById("Portrait") ;
	i_p_file   =document.getElementById("PortraitFile") ;
	i_ext_type =document.getElementById("ExtensionType") ;
	i_count    =document.getElementById("Count") ;
	i_new_order=document.getElementById("NewOrder") ;
	i_delete   =document.getElementById("Delete") ;
	i_reorder  =document.getElementById("ReOrder") ;
	i_error    =document.getElementById("Error") ;

	i_name_f.focus() ;

	a_specialities=new Array() ;

<?php
            ProcessDB() ;
?>
       var  nl=new RegExp("@@","g") ;

       i_remark.value=i_remark.value.replace(nl,"\n") ;

         return true ;
  }

  function SendFields() 
  {
     var  i_new ;
     var  i_elm ;
     var  error_text ;
     var  nl=new RegExp("\n","g") ;


	error_text=""

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


     if(i_p_file.value=="")  i_p_file.name=i_p_file.name+"_" ;

       i_remark.value=i_remark.value.replace(nl,"@@") ;

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

  function AddNewSpeciality(p_selected)
  {
     var  i_specialities ;
     var  i_div_new ;
     var  i_select_new ;
     var  selected ;

       i_specialities   =document.getElementById("Specialities") ;
       i_div_new        =document.createElement("div") ;
       i_select_new     =document.createElement("select") ;
       i_select_new.name="Specialities[]" ;

    for(var elem in a_specialities)
    {
                             selected=false ;
       if(p_selected==elem)  selected=true ;

                            i_select_new.length++ ;
       i_select_new.options[i_select_new.length-1].text    =a_specialities[elem] ;
       i_select_new.options[i_select_new.length-1].value   =               elem ;
       i_select_new.options[i_select_new.length-1].selected=           selected ;
    }

       i_div_new     .appendChild(i_select_new) ;	
       i_specialities.appendChild(i_div_new   ) ;	

    return ;         
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
       i_fld_new = document.createElement("input") ;
       i_fld_new . id        ='OrderNew' ;
       i_fld_new . name      ='OrderNew' ;
       i_fld_new . type      ="text" ;
       i_fld_new . hidden    = true ;
       i_fld_new . value     =i_new_order.value ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id        ='TypeNew' ;
       i_fld_new . name      ='TypeNew' ;
       i_fld_new . type      ="text" ;
       i_fld_new . hidden    = true ;
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
        <b>ФОРМУЛЯР ВРАЧА</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST" enctype="multipart/form-data">

  <table width="100%" id="Fields">
  <thead>
  </thead>
  <tbody>
  <tr>
    <td width="70%">
      <table id="Fields">
        <thead>
        </thead>
        <tbody>
        <tr>
          <td class="field"> </td>
          <td> <br> <input type="submit" value="Сохранить"> </td>
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
          <td class="field"> <p> </p> </td>
        </tr>
        <tr>
          <td class="field"> Специальность </td>
          <td id="Specialities">
          </td>
        </tr>
        <tr>
          <td class="field"> </td>
          <td>
            <input type="button" value="Добавить специализацию" onclick="AddNewSpeciality('');"> 
          </td>
        </tr>
        <tr>
          <td class="field"> Примечание </td>
          <td> 
            <textarea cols=60 rows=7 wrap="soft" name="Remark" id="Remark"></textarea>
          </td>
        </tr>
          <td class="field"> </td>
          <td>
            <br> 
            <input type="hidden" name="PageId" id="PageId">
          </td>
        </tr>
        </tbody>
      </table>
    </td>
    <td width="2%">
    </td>
    <td width="28%">

<?php
            PortraitView() ;
?>

      <br>
      Выбор файла портрета: 
      <br>
      <input type="file" accept="image/*" name="PortraitFile" id="PortraitFile">  
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
      <option value="Text">Текстовой блок</option>
      <option value="Image">Картинка с пояснением</option>
      <option value="Link">Ссылка с пояснением</option>
      <option value="File">Файл с пояснением</option>
    </select> 
    <input type="button" value="Добавить" onclick=AddNewExtension() id="AddExtension">
    <input type="hidden" name="Count" id="Count">
    <input type="hidden" name="NewOrder" id="NewOrder">
    <input type="hidden" name="FileName" id="FileName">
    <input type="hidden" name="ReOrder" id="ReOrder">
    <input type="hidden" name="Delete" id="Delete">
  </div>
  <br>

  </form>

</div>

</body>

</html>
