<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescription_edit.php" ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД


function ProcessDB() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_owner  ;
  global  $sys_ext_user   ;
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

                         $get_id=$_GET ["Id"] ;
                         $put_id=$_POST["Id"] ;

  if( isset($put_id )) 
  {
                         $type       =$_POST["Type"] ;
                         $name       =$_POST["Name"] ;
                         $reference  =$_POST["Reference"] ;
                         $description=$_POST["Description"] ;
                         $www_link   =$_POST["WWW_link"] ;
                         $deseases   =$_POST["Deseases"] ;
                         $count      =$_POST["Count"] ;
                         $delete     =$_POST["Delete"] ;
                         $reorder    =$_POST["ReOrder"] ;
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

  if( isset($get_id )) 
  {
    FileLog("",      "     Get_Id:".$get_id) ;
  }

  if( isset($put_id )) 
  {
    FileLog("",      "     Put_Id:".$put_id) ;
    FileLog("",      "       Type:".$type) ;
    FileLog("",      "       Name:".$name) ;
    FileLog("",      "  Reference:".$reference) ;
    FileLog("",      "Description:".$description) ;
    FileLog("",      "   WWW_link:".$www_link) ;
    FileLog("",      "   Deseases:".$deseases) ;
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

          $session_=$db->real_escape_string($session) ;
          $user_   =$db->real_escape_string($user) ;

//--------------------------- Сохранение данных со страницы

  if(isset($put_id))
  {
          $put_id_     =$db->real_escape_string($put_id) ;
          $type_       =$db->real_escape_string($type) ;
          $name_       =$db->real_escape_string($name) ;
          $reference_  =$db->real_escape_string($reference) ;
          $description_=$db->real_escape_string($description) ;
          $www_link_   =$db->real_escape_string($www_link) ;
          $deseases_   =$db->real_escape_string($deseases) ;

                       $sql="Update prescriptions_registry".
                            " Set   type       ='$type_'".
                            "      ,name       ='$name_'".
                            "      ,reference  ='$reference_'".
                            "      ,description='$description_'".
                            "      ,www_link   ='$www_link_'".
                            "      ,deseases   ='$deseases_'".
                            " Where id='$put_id_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

                   $get_id=$put_id ;

        FileLog("", "Prescription data saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Манипуляции с дополнительными блоками

  if(isset($put_id)) {
//- - - - - - - - - - - - - - Удаление блоков
     if($delete!="") {
			$delete=                 substr($delete, 1) ;
                        $delete=$db->real_escape_string($delete) ;

                       $sql="Delete from prescriptions_ext".
                            " Where prescription_id='$put_id_'".
                            "  and  user           ='$user_'". 
                            "  and  order_num in ($delete)" ;
          $res=$db->query($sql) ;
       if($res===false) {
             FileLog("ERROR", "Delete PRESCRIPTIONS_EXT... : ".$db->error) ;
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

                             $sql="Update prescriptions_ext".
                                  "   Set order_num=$pair[1]".
                                  " Where prescription_id='$put_id_'".
                                  "  and  user           ='$user_'". 
                                  "  and  id             =$pair[0]" ;
             $res=$db->query($sql) ;
          if($res===false) {
                FileLog("ERROR", "Update PRESCRIPTIONS_EXT(Order_num)... : ".$db->error) ;
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
    if(isset($_FILES[$image])) 
    {

           FileLog("", "Image/attachment file detected") ;

        if($new_type=="Image") {  $file_type="Image" ;
                                    $actions="create_short_image:relative_path" ;
                               }
        else                   {  $file_type="Document" ;
                                    $actions="relative_path" ;
                               }

         $path=LoadFile($image, $new_file, "prescriptions_registry", $put_id, $file_type, 
                             $actions, $spath, $error) ;
      if($path===false) {

             FileLog("ERROR", "IMAGE/FILE : ".$error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ".$error) ;
                         return ;
      }

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

                           $sql ="Update prescriptions_ext".
                                 "   Set remark='$new_remark_'" ;
 
        if($new_link!="")  $sql.="       ,www_link='$new_link_'" ;
        if($new_file!="")  $sql.="       ,file='$new_file_', short_file='$new_sfile_'" ;

                           $sql.=" Where prescription_id= $put_id_".
                                 "  and  user           ='$user_'".
                                 "  and  id             = $ext_edit_" ;
     }
     else
     {
           $sql="Insert into prescriptions_ext".
                "       (prescription_id, order_num, user, type, remark, file, short_file, www_link)".
                " Values('$put_id_','$new_order_','$user_','$new_type_','$new_remark_','$new_file_','$new_sfile_','$new_link_')" ;
     }

        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Insert/Update PRESCRIPTIONS_EXT... : ".$db->error) ;
                            $db->close() ;

         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка добавления/изменения блока") ;
                         return ;
     }

          $db->commit() ;

     if(isset($ext_edit))   echo  "  document.getElementById('LiftUp_".$new_order."').focus() ;	\n" ;
     else                   echo  "  document.getElementById('ExtensionType').focus() ;		\n" ;

        FileLog("", "Prescription extension added successfully") ;
  }
//--------------------------- Создание новой записи

  if(!isset($get_id) and
     !isset($put_id)    ) 
  {
                       $sql="Insert into prescriptions_registry(type, user, name)".
                            " Values('dummy','$user_', '#$session_#')" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Insert PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания карточки назначения") ;
                         return ;
    }

            $db->commit() ;

                       $sql="Select max(id)".
                            "  From prescriptions_registry".
                            " Where name='#$session_#'" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select id from PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка идентификации карточки назначения") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $put_id     =$fields[0] ;
                   $owner      =$user ;
                   $type       ='dummy' ;
                   $name       ='' ;
                   $reference  ='' ;
                   $description='' ;
                   $www_link   ='' ;
                   $deseases   ='' ;

          $get_id_=$db->real_escape_string($put_id) ;

        FileLog("", "New prescription generated successfully") ;
  }
//--------------------------- Извлечение данных для отображения

  else
  {
          $get_id_=$db->real_escape_string($get_id) ;

                       $sql="Select id, user, type, name, reference, description, www_link, deseases".
                            "  From  prescriptions_registry".
                            " Where  id='$get_id_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select * from PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $put_id     =$fields[0] ;
                   $owner      =$fields[1] ;
                   $type       =$fields[2] ;
                   $name       =$fields[3] ;
                   $reference  =$fields[4] ;
                   $description=$fields[5] ;
                   $www_link   =$fields[6] ;
                   $deseases   =$fields[7] ;

        FileLog("", "Prescription data selected successfully") ;
  }
//--------------------------- Извлечение списка типов назначений

                     $sql="Select code, name".
			  "  From ref_prescriptions_types".
			  " Where language='RU'".
                          "  and  code<>'unregistered'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_PRESCRIPTIONS_TYPES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника типов назначений") ;
                         return ;
  }
  else
  {  
       echo "   a_types['dummy']='' ;\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_types['".$fields[0]."']='".$fields[1]."' ;\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение списка связанных заболеваний

  if($deseases!="")
  {
             $deseases_list=str_replace(" ", ",", $deseases) ;

                       $sql="Select name, grp, code, id".
                            "  from (".
                            "        Select ''name, d3.name grp, '0'code, d3.id".
                            "          From deseases_registry d3".
                            "         Where d3.type =  '0'".
                            "        union all".
                            "        Select d1.name, d2.name grp, d1.type code, d1.id".
                            "          From deseases_registry d1, deseases_registry d2".
                            "         Where d1.type = d2.id".
                            "        )list".
                            " Where id in (".$deseases_list.")".
                            " Order by grp, name" ;
       $res=$db->query($sql) ;
    if($res===false) {
            FileLog("ERROR", "Select DESEASES_REGISTRY... : ".$db->error) ;
                              $db->close() ;
           ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка связанных заболеваний") ;
                           return ;
    }

    for($i=0 ; $i<$res->num_rows ; $i++)
    {
	      $fields=$res->fetch_row() ;

       echo "    dss_name ='".$fields[0]."' ;	\n" ;
       echo "    dss_group='".$fields[1]."' ;	\n" ;
       echo "    dss_gcode='".$fields[2]."' ;	\n" ;
       echo "    dss_id   ='".$fields[3]."' ;	\n" ;

       echo "  SetDeseaseSelection(true, dss_id, dss_name, dss_group, dss_gcode, null) ;	\n" ;
    }

     $res->close() ;
  }
//--------------------------- Извлечение дополнительных блоков

      echo     "  i_count    .value='0' ;	\n" ;
      echo     "  i_new_order.value='0' ;	\n" ;

                     $sql="Select e.id".
                          "      ,CONCAT_WS(' ', d.name_f, d.name_i, d.name_o), e.user".
			  "      ,e.type, e.remark, e.file, e.short_file, e.www_link".
			  "  From prescriptions_ext e, doctor_page_main d".
			  " Where e.user=d.owner".
                          "  and  e.prescription_id='$get_id_'".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_EXT... : ".$db->error) ;
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
          $sys_ext_user  [$i]= $fields[1] ;
          $sys_ext_owner [$i]=($fields[2]==$user) ;
          $sys_ext_type  [$i]= $fields[3] ;
          $sys_ext_remark[$i]= $fields[4] ;
          $sys_ext_file  [$i]= $fields[5] ;
          $sys_ext_sfile [$i]= $fields[6] ;
          $sys_ext_link  [$i]= $fields[7] ;
     }

      echo     "  i_count    .value=".$res->num_rows." ;	\n" ;
      echo     "  i_new_order.value=".$res->num_rows." ;	\n" ;

  }

     $res->close() ;

//--------------------------- Вывод данных на страницу

      echo     "  i_id         .value='".$put_id     ."' ;\n" ;
      echo     "  i_owner      .value='".$owner      ."' ;\n" ;
      echo     "  i_name       .value='".$name       ."' ;\n" ;
      echo     "  i_reference  .value='".$reference  ."' ;\n" ;
      echo     "  i_description.value='".$description."' ;\n" ;
      echo     "  i_www_link   .value='".$www_link   ."' ;\n" ;
      echo     "  i_deseases   .value='".$deseases   ."' ;\n" ;

      echo     "  SetType('".$type."') ;\n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение дополнительных блоков описания

function ShowExtensions() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_owner  ;
  global  $sys_ext_user   ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_file   ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;


  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
        $row=$i ;

       echo  "  <tr class='table' id='Row_".$row."'>						\n" ;
       echo  "    <td  class='table' width='15%'>						\n" ;
       echo  "      <div>									\n" ;
       echo  $sys_ext_user[$i] ;
       echo  "      </div>									\n" ;
       echo  "      <input type='hidden' id='Order_".$row."' value='".$row."'>			\n" ;
       echo  "      <input type='hidden' id='Ext_"  .$row."' value='".$sys_ext_id[$i]."'>	\n" ;
       echo  "      <input type='hidden' id='Type_" .$row."' value='".$sys_ext_type[$i]."'>	\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='table'>								\n" ;
       echo  "      <div id='Remark_".$row."'>							\n" ;
       echo  htmlspecialchars(stripslashes($sys_ext_remark[$i]), ENT_COMPAT, "windows-1251") ;
       echo  "      </div>									\n" ;
       echo  "    <br>										\n" ;

    if($sys_ext_type[$i]=="Image") {
       echo "<div class='fieldC'>					\n" ; 
       echo "<img src='".$sys_ext_sfile[$i]."' height=200		\n" ;
       echo " onclick=\"window.open('".$sys_ext_file[$i]."')\" ;	\n" ;
       echo ">								\n" ; 
       echo "</div>							\n" ; 
       echo "<br>							\n" ;
    }

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
       echo  "    <td class='table'>				\n" ;

    if($sys_ext_owner[$i]===true) {
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

<title>DarkMed Prescription Registry Card</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_table ;
    var  i_id ;
    var  i_owner ;
    var  i_type ;
    var  i_name ;
    var  i_reference ;
    var  i_description ;
    var  i_www_link ;
    var  i_deseases ;
    var  i_ext_type ;
    var  i_count ;
    var  i_new_order ;
    var  i_delete ;
    var  i_reorder ;
    var  i_error ;

    var  a_types ;

    var  s_deseases_select_use ;

  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;

	i_table      =document.getElementById("Fields") ;
	i_id         =document.getElementById("Id") ;
	i_owner      =document.getElementById("Owner") ;
	i_type       =document.getElementById("Type") ;
	i_name       =document.getElementById("Name") ;
	i_reference  =document.getElementById("Reference") ;
	i_description=document.getElementById("Description") ;
	i_www_link   =document.getElementById("WWW_link") ;
	i_deseases   =document.getElementById("Deseases") ;
	i_ext_type   =document.getElementById("ExtensionType") ;
	i_count      =document.getElementById("Count") ;
	i_new_order  =document.getElementById("NewOrder") ;
	i_delete     =document.getElementById("Delete") ;
	i_reorder    =document.getElementById("ReOrder") ;
	i_error      =document.getElementById("Error") ;

	a_types=new Array() ;

        s_deseases_select_use=false ;

<?php
            ProcessDB() ;
?>

       i_description.value=i_description.value.replace(nl,"\n") ;

         return true ;
  }

  function GoAway() 
  {
     if(s_deseases_select_use)
             parent.frames["details"].location.replace("start.html") ;
  }

  function SendFields() 
  {
     var  i_new ;
     var  i_elm ;
     var  error_text ;
     var  file_name ;
     var  pos ;
     var  nl=new RegExp("\n","g") ;

	error_text="" ;
     
     if(i_name.value==''     )  error_text="Название назначения должно быть задано" ;
     if(i_type.value=='dummy')  error_text="Категория назначения должна быть определена" ;


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

          i_description.value=i_description.value.replace(nl,"@@") ;

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

          i_id   .disabled=false ;
          i_owner.disabled=false ;

                         return true ;         
  } 

  function SetType(p_selected)
  {
     var  selected ;

    for(var elem in a_types)
    {
                             selected=false ;
       if(p_selected==elem)  selected=true ;

                      i_type.length++ ;
       i_type.options[i_type.length-1].text    =a_types[elem] ;
       i_type.options[i_type.length-1].value   =        elem ;
       i_type.options[i_type.length-1].selected=    selected ;
    }

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
       i_txt_new = document.createTextNode("ФИО доктора") ;
       i_col_new . appendChild(i_txt_new) ;
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

  function LinkDesease() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        parent.frames["details"].location.replace("deseases_select.php"+"?Session="+v_session+"&Deseases="+i_deseases.value) ;

        s_deseases_select_use=true ;
  } 

  function SetDeseaseSelection(p_checked, p_id, p_name, p_group, p_gcode, p_before)
  {
     var  i_dss_list ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_before ;
     var  v_id ;


                   v_id="Desease_"+p_id ;
       i_deseases.value=" "+i_deseases.value+" " ;

       i_dss_list= document.getElementById("Deseases_list") ;

   if(p_checked==false)
   {
     i_deseases.value=i_deseases.value.replace(" "+p_id+" ", " ").trim() ;

     i_dss_list.removeChild(document.getElementById(v_id)) ;
         return ;
   }

   if(i_deseases.value.indexOf(" "+p_id+" ")<0)
   {
       i_deseases.value+=p_id ;
   }
       i_deseases.value =i_deseases.value.trim() ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;
       i_row_new . id        =  v_id ;

       i_col_new = document.createElement("td") ;

   if(p_gcode==0)
   {
       i_col_new . className = "tableG" ;
       i_txt_new = document.createTextNode(p_group) ;
       i_col_new . appendChild(i_txt_new) ;
   }
   else
   {
       i_col_new . className = "tableL" ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
   } 
       i_col_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="desease_details_any.php" ;
						parent.frames["details"].location.replace(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Id="+p_id) ;
					} ;
       i_row_new . appendChild(i_col_new) ;


   if(p_before!=null)  i_before=document.getElementById("Desease_"+p_before) ;
   else                i_before= null ;

       i_dss_list.insertBefore(i_row_new, i_before) ;

    return ;         
  } 


<?php
  require("common.inc") ;
?>


//-->
</script>

</head>

<body onload="FirstField();" onunload="GoAway();">

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
        <b>КАРТОЧКА РЕГИСТРА НАЗНАЧЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST" enctype="multipart/form-data">

  <table width="100%" >
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="fieldC">
          <br> 
          <input type="submit" value="Сохранить"  id="Save1"> 
      </td>
      <td> </td>
    </tr>
    <tr>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
    <td class="table">

  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> Код </td>
      <td> <input type="text" size=10 disabled name="Id" id="Id"> </td>
    </tr>
    <tr>
      <td class="field"> Создано </td>
      <td> <input type="text" size=10 disabled name="Owner" id="Owner"> </td>
    </tr>
    <tr>
      <td class="field"> Категория </td>
      <td>
         <select name="Type" id="Type"> 
         </select> 
      </td>
    </tr>
    <tr>
      <td class="field"> Название </td>
      <td> <input type="text" size=60 name="Name" id="Name"> </td>
    </tr>
    <tr>
      <td class="field"> Регистр </td>
      <td> <input type="text" size=60 name="Reference" id="Reference"> </td>
    </tr>
    <tr>
      <td class="field"> Смотреть на </td>
      <td>
          <input type="text" size=60 maxlength=510 name="WWW_link" id="WWW_link"> 
          <input type="button" value="Проверить" onclick=GoToLink('WWW_link')>
      </td>
    </tr>
    <tr>
      <td class="field"> Описание </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Description" id="Description"> </textarea>
      </td>
    </tr>
    </tbody>
  </table>

      </td>
      <td class="table">
        <div class="fieldC">
          <input type="button" value="Добавить/удалить заболевания" onclick=LinkDesease()>
        </div> 
        <table width="100%">
          <thead>
          </thead>
          <tbody  id="Deseases_list">
          </tbody>
        </table>
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
      <option value="Text">Текстовой блок</option>
      <option value="Link">Ссылка с пояснением</option>
      <option value="File">Файл с пояснением</option>
    </select> 
    <input type="button" value="Добавить" onclick=AddNewExtension() id="AddExtension">
    <input type="hidden" name="Deseases" id="Deseases">
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
