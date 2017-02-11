<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_cert.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $sys_doc_count      ;
  global  $sys_doc_id         ;
  global  $sys_doc_kind       ;
  global  $sys_doc_issuer     ;
  global  $sys_doc_requicites ;
  global  $sys_doc_issue_date ;
  global  $sys_doc_desc       ;
  global  $sys_doc_www_link   ;
  global  $sys_doc_image_1    ;
  global  $sys_doc_image_1_s  ;
  global  $sys_doc_image_2    ;
  global  $sys_doc_image_2_s  ;

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

                          $count=$_POST["Count"] ;

  if(isset($count))
  {
                         $delete=$_POST["Delete"] ;
                        $reorder=$_POST["ReOrder"] ;
                      $new_order=$_POST["OrderNew"] ;
                       $ext_edit=$_POST["ExtEdit"] ;
  }
                       $new_file="" ;

  if( isset($new_order) ||
      isset($ext_edit )   )
  {
                       $new_kind      =$_POST["KindNew"] ;
                       $new_issuer    =$_POST["IssuerNew"] ;
                       $new_requicites=$_POST["RequicitesNew"] ;
                       $new_issue_date=$_POST["IssueDateNew"] ;
                       $new_desc      =$_POST["DescNew"] ;
                       $new_link      =$_POST["LinkNew"] ;
                       $new_file_1    =$_POST["File1NameNew"] ;
                       $new_file_2    =$_POST["File2NameNew"] ;
  }

	FileLog("START", "       Session:".$session) ;

  if(isset($count)) {
	FileLog("",      "         Count:".$count) ;
	FileLog("",      "        Delete:".$delete) ;
	FileLog("",      "       ReOrder:".$reorder) ;
  }

  if( isset($new_order))
  {
	FileLog("",      "      OrderNew:".$new_order) ;
	FileLog("",      "       ExtEdit:".$ext_edit) ;
	FileLog("",      "       KindNew:".$new_kind) ;
	FileLog("",      "     IssuerNew:".$new_issuer) ;
	FileLog("",      " RequicitesNew:".$new_requicites) ;
	FileLog("",      "  IssueDateNew:".$new_issue_date) ;
	FileLog("",      "       LinkNew:".$new_link) ;
	FileLog("",      "  File1NameNew:".$new_file_1) ;
	FileLog("",      "  File2NameNew:".$new_file_2) ;
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

//--------------------------- Извлечение списка видов квалификационных документов

                     $sql="Select code, name".
			  "  From ref_cert_kinds".
			  " Where language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_CERT_KINDS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника видов квалификационных документов") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_doc_kinds['".$fields[0]."']='".$fields[1]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение списка выдавателей квалификационных документов

                     $sql="Select code, name".
			  "  From ref_cert_issuers".
			  " Where language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_CERT_ISSUERS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника организаций") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_doc_issuers['".$fields[0]."']='".$fields[1]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Манипуляции со списком документов

  if(isset($delete))
  {
//- - - - - - - - - - - - - - Удаление блоков
     if($delete!="") {
			$delete=                 substr($delete, 1) ;
                        $delete=$db->real_escape_string($delete) ;

                       $sql="Delete from doctor_certificates".
                            " Where owner='$user_'".
                            "  and  order_num in ($delete)" ;
          $res=$db->query($sql) ;
       if($res===false) {
             FileLog("ERROR", "Delete DOCTOR_CERTIFICATES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка удаления документов") ;
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

                             $sql="Update doctor_certificates".
                                  "   Set order_num=$pair[1]".
                                  " Where owner='$user_'".
                                  "  and  id   =$pair[0]" ;
             $res=$db->query($sql) ;
          if($res===false) {
                FileLog("ERROR", "Update DOCTOR_CERTIFICATES(Order_num)... : ".$db->error) ;
                        $db->rollback();
                        $db->close() ;
               ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка перенумерации документов") ;
                            return ;
          }
        }
     }
//- - - - - - - - - - - - - -
		          $db->commit() ;
   }
//--------------------------- Сохранение документа

  if(isset($new_order))
  {
//- - - - - - - - - - - - - - Сохранение картинки 1
                     $image="File1New" ;
                    $path_1="" ;
                   $spath_1="" ;

    if($new_file_1!="")
    if(isset($_FILES[$image])) {

         $path_1=LoadFile($image, $new_file_1, "doctor", $user_, "Cert1", 
                             "create_short_image+relative_path", $spath_1, $error) ;
      if($path_1===false) {

             FileLog("ERROR", "IMAGE/FILE 1 : ".$error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ".$error) ;
                         return ;
      }
    }
//- - - - - - - - - - - - - - Сохранение картинки 2
                     $image="File2New" ;
                    $path_2="" ;
                   $spath_2="" ;

    if($new_file_2!="")
    if(isset($_FILES[$image])) {

         $path_2=LoadFile($image, $new_file_2, "doctor", $user_, "Cert2", 
                             "create_short_image+relative_path", $spath_2, $error) ;
      if($path_2===false) {

             FileLog("ERROR", "IMAGE/FILE 2 : ".$error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ".$error) ;
                         return ;
      }
    }
//- - - - - - - - - - - - - - Сохранение данных блока
          $new_order_     =$db->real_escape_string($new_order) ;
          $new_kind_      =$db->real_escape_string($new_kind) ;
          $new_issuer_    =$db->real_escape_string($new_issuer) ;
          $new_requicites_=$db->real_escape_string($new_requicites) ;
          $new_issue_date_=$db->real_escape_string($new_issue_date) ;
          $new_desc_      =$db->real_escape_string($new_desc) ;
          $new_link_      =$db->real_escape_string($new_link) ;
          $new_file_1     =$db->real_escape_string($path_1) ;
          $new_sfile_1    =$db->real_escape_string($spath_1) ;
          $new_file_2     =$db->real_escape_string($path_2) ;
          $new_sfile_2    =$db->real_escape_string($spath_2) ;

     if(isset($ext_edit))
     {     
          $ext_edit_=$db->real_escape_string($ext_edit) ;

                             $sql ="Update doctor_certificates".
                                   "   Set kind      ='$new_kind_'".
                                   "      ,issuer    ='$new_issuer_'".
                                   "      ,requicites='$new_requicites_'".
                                   "      ,issue_date='$new_issue_date_'".
                                   "      ,`desc`    ='$new_desc_'" ;
        if($new_link_ !="")  $sql.="      ,www_link  ='$new_link_'" ;
        if($new_file_1!="")  $sql.="      ,image_1   ='$new_file_1', image_1_s='$new_sfile_1'" ;
        if($new_file_2!="")  $sql.="      ,image_2   ='$new_file_2', image_2_s='$new_sfile_2'" ;
                             $sql.=" Where owner='$user_'".
                                   "  and  id   = $ext_edit_" ;
     }
     else
     {
           $sql="Insert into doctor_certificates".
                "       ( owner, order_num, kind, issuer, requicites, issue_date, `desc`, www_link".
                "        ,image_1, image_1_s, image_2, image_2_s)".
                " Values('$user_','$new_order_','$new_kind_','$new_issuer_','$new_requicites_','$new_issue_date_','$new_desc_','$new_link_'".
                "        ,'$new_file_1','$new_sfile_1','$new_file_2','$new_sfile_2')" ;
     }

        $res=$db->query($sql) ;
     if($res===false) {
          FileLog("ERROR", "Insert/Update DOCTOR_CERTIFICATES... : ".$db->error) ;
                            $db->close() ;

         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка добавления/изменения документа") ;
                         return ;
     }

          $db->commit() ;

     if(isset($ext_edit))   echo  "  document.getElementById('LiftUp_".$new_order."').focus() ;	\n" ;
     else                   echo  "  document.getElementById('AddRow').focus() ;		\n" ;

        FileLog("", "Documents added/updated successfully") ;
     SuccessMsg() ;
  }

//--------------------------- Извлечение списка документов

      echo     "  i_count    .value='0' ;	\n" ;
      echo     "  i_new_order.value='0' ;	\n" ;

                     $sql="Select id".
			  "      ,ifnull(k.name,c.kind), ifnull(i.name,c.issuer), requicites, issue_date, `desc`, www_link".
                          "      ,image_1, image_1_s, image_2, image_2_s".
			  "  From doctor_certificates c left outer join ref_cert_kinds   k on c.kind  =k.code and k.language='RU'".
			  "                             left outer join ref_cert_issuers i on c.issuer=i.code and i.language='RU'".
			  " Where owner='$user_'".
                          " Order by order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DOCTOR_CERTIFICATES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения документов") ;
                         return ;
  }
  else
  {  
          $sys_doc_count=$res->num_rows ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

          $sys_doc_id        [$i]= $fields[ 0] ;
          $sys_doc_kind      [$i]= $fields[ 1] ;
          $sys_doc_issuer    [$i]= $fields[ 2] ;
          $sys_doc_requicites[$i]= $fields[ 3] ;
          $sys_doc_issue_date[$i]= $fields[ 4] ;
          $sys_doc_desc      [$i]= $fields[ 5] ;
          $sys_doc_www_link  [$i]= $fields[ 6] ;
          $sys_doc_image_1   [$i]= $fields[ 7] ;
          $sys_doc_image_1_s [$i]= $fields[ 8] ;
          $sys_doc_image_2   [$i]= $fields[ 9] ;
          $sys_doc_image_2_s [$i]= $fields[10] ;
     }

      echo     "  i_count    .value=".$res->num_rows." ;	\n" ;
      echo     "  i_new_order.value=".$res->num_rows." ;	\n" ;

  }

     $res->close() ;

//--------------------------- Отображение данных на форме

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение списка документов

function ShowCertificates() {

  global  $sys_doc_count      ;
  global  $sys_doc_id         ;
  global  $sys_doc_kind       ;
  global  $sys_doc_issuer     ;
  global  $sys_doc_requicites ;
  global  $sys_doc_issue_date ;
  global  $sys_doc_desc       ;
  global  $sys_doc_www_link   ;
  global  $sys_doc_image_1    ;
  global  $sys_doc_image_1_s  ;
  global  $sys_doc_image_2    ;
  global  $sys_doc_image_2_s  ;


  for($i=0 ; $i<$sys_doc_count ; $i++)
  {
        $row=$i ;

       echo  "  <tr class='table' id='Row_".$row."'>			\n" ;
       echo  "    <td  class='table'>					\n" ;

    if($sys_doc_image_1_s[$i]!="" ||
       $sys_doc_image_2_s[$i]!=""   )
    {
       echo "<div class='fieldC'>					\n" ; 

      if($sys_doc_image_1_s[$i]!="") {
        echo "<img src='".$sys_doc_image_1_s[$i]."' height=200		\n" ;
        echo " onclick=\"window.open('".$sys_doc_image_1[$i]."')\" ;	\n" ;
        echo ">								\n" ; 
      }
      if($sys_doc_image_2_s[$i]!="") {
        echo "<img src='".$sys_doc_image_2_s[$i]."' height=200		\n" ;
        echo " onclick=\"window.open('".$sys_doc_image_2[$i]."')\" ;	\n" ;
        echo ">								\n" ; 
      }

       echo "</div>							\n" ; 
    }

       echo  "      <input type='hidden' id='Order_".$row."' value='".$row."'>			\n" ;
       echo  "      <input type='hidden' id='Ext_"  .$row."' value='".$sys_doc_id[$i]."'>	\n" ;
       echo  "    </td>										\n" ;
       echo  "    <td class='table'>								\n" ;
       echo  "      <b><div id='Kind_".$row."'>".
                    htmlspecialchars(stripslashes($sys_doc_kind[$i]), ENT_COMPAT, "windows-1251").
                   "</div></b>									\n" ;
       echo  "      <div id='Issuer_".$row."'>".
                    htmlspecialchars(stripslashes($sys_doc_issuer[$i]), ENT_COMPAT, "windows-1251").
                   "</div>									\n" ;
       echo  "      <div>									\n" ;
       echo  "      <span id='Requicites_".$row."'>".$sys_doc_requicites[$i]."</span>		\n" ;
       echo  "      от <span id='IssueDate_".$row."'>".$sys_doc_issue_date[$i]."</span>		\n" ;
       echo  "      </div>									\n" ;
       echo  "      <div id='Desc_".$row."'>".
              htmlspecialchars(stripslashes($sys_doc_desc[$i]), ENT_COMPAT, "windows-1251").
		   "</div>									\n" ;

    if($sys_doc_www_link[$i]!="")
    {
                        $name=$sys_doc_www_link[$i] ;
                         $pos= strpos($name, "://") ;
      if($pos!==false)  $name= substr($name, $pos+3) ;
                         $pos= strpos($name, "/") ;
      if($pos!==false)  $name= substr($name, 0, $pos) ;

       echo  "      <div> Контрольная ссылка -							\n" ;
       echo  "      <a href='#' onclick=window.open('".$sys_doc_www_link[$i]."')>".$name."</a>	\n" ; 
       echo  "      <br>									\n" ;
       echo  "      </div>									\n" ;
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
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		" ;
    echo  "i_error.innerHTML  ='".$text."' ;	" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;			" ;
    echo  "i_error.innerHTML  ='Данные успешно сохранены!' ;	" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Doctor Certificates</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script type="text/javascript">
<!--

    var  i_count ;
    var  i_new_order ;
    var  i_delete ;
    var  i_reorder ;
    var  i_error ;
    var  a_doc_issuers ;
    var  a_doc_kinds ;


  function FirstField() 
  {
    var  i_select ;


	i_count    =document.getElementById("Count") ;
	i_new_order=document.getElementById("NewOrder") ;
	i_delete   =document.getElementById("Delete") ;
	i_reorder  =document.getElementById("ReOrder") ;
	i_error    =document.getElementById("Error") ;

	a_doc_issuers=new Array() ;
	a_doc_kinds  =new Array() ;

<?php
            ProcessDB() ;
?>

       i_select=document.getElementById("Kind") ;

                        i_select.length++ ;
       i_select.options[i_select.length-1].text =" -- Выбeрите вид документа -- " ;
       i_select.options[i_select.length-1].value="Dummy" ;

    for(var elem in a_doc_kinds)
    {
                        i_select.length++ ;
       i_select.options[i_select.length-1].text =a_doc_kinds[elem] ;
       i_select.options[i_select.length-1].value=            elem ;
    }

                        i_select.length++ ;
       i_select.options[i_select.length-1].text ="Прочие" ;
       i_select.options[i_select.length-1].value="Other" ;

       i_select=document.getElementById("Issuer") ;

                        i_select.length++ ;
       i_select.options[i_select.length-1].text =" -- Выбeрите выдавшую организацию -- " ;
       i_select.options[i_select.length-1].value="Dummy" ;

    for(var elem in a_doc_issuers)
    {
                        i_select.length++ ;
       i_select.options[i_select.length-1].text =a_doc_issuers[elem] ;
       i_select.options[i_select.length-1].value=              elem ;
    }

                        i_select.length++ ;
       i_select.options[i_select.length-1].text ="Прочие" ;
       i_select.options[i_select.length-1].value="Other" ;

         return true ;
  }

  function SendFields() 
  {
     var  i_new ;
     var  i_elm ;
     var  error_text ;


	error_text=""

        i_elm=document.getElementById("KindNew") ;
     if(i_elm!=null)
      if(i_elm.value=='Dummy')   error_text="Не выбран вид квалификационного документа" ;

        i_elm=document.getElementById("IssuerNew") ;
     if(i_elm!=null)
      if(i_elm.value=='Dummy')   error_text="Не выбрана организация, выдавшая квалификационный документ" ;

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="") {
          i_new=document.getElementById("ErrorExt") ;
       if(i_new!=null) {  i_new.style.color="red" ;
			  i_new.innerHTML  = error_text ;  }
                              return false ;
     }

        i_new=document.getElementById("File1New") ;
     if(i_new!=null) {
           file_name=i_new.value ;
                 pos=file_name.lastIndexOf('\\') ;
             if(pos>=0)  file_name=file_name.substr(pos+1) ;

           document.getElementById("File1NameNew").value=file_name ;
     }

        i_new=document.getElementById("File2New") ;
     if(i_new!=null) {
           file_name=i_new.value ;
                 pos=file_name.lastIndexOf('\\') ;
             if(pos>=0)  file_name=file_name.substr(pos+1) ;

           document.getElementById("File2NameNew").value=file_name ;
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

  function AddNewRow()
  {
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_doc_new ;
     var  i_dev_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_add_new ;
     var  i_elm ;
     var  elems ;
     var  i ;


	i_doc_new =document.getElementById("TemplateCert").cloneNode(true) ;
	i_doc_new .hidden=false ;

	 elems    = i_doc_new.getElementsByTagName('*') ;
      for(var i=0; i<elems.length; i++)
        if(elems[i].id!='') {  elems[i].id  =elems[i].id+"New" ;
                               elems[i].name=elems[i].id ;        } 

	i_set     = document.getElementById("Certificates") ;

	i_row_new = document.createElement("tr") ;
	i_row_new . className = "table" ;
	i_row_new . id        = "RowNew" ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_fld_new = document.createElement("input") ;
	i_fld_new . id        ='OrderNew' ;
	i_fld_new . name      ='OrderNew' ;
	i_fld_new . type      ="text" ;
	i_fld_new . hidden    = true ;
	i_fld_new . value     =i_new_order.value ;
	i_col_new . appendChild(i_fld_new) ;
	i_fld_new = document.createElement("br") ;
	i_col_new . appendChild(i_fld_new) ;
	i_add_new = document.createElement("input") ;
	i_add_new . type      ="submit" ;
	i_add_new . className ="SaveButton" ;
	i_add_new . value     ="Сохранить" ;
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

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_fld_new = document.createElement("div") ;
	i_fld_new . id        ='ErrorExt' ;
	i_col_new . appendChild(i_fld_new) ;

	i_col_new . appendChild(i_doc_new) ;

	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_row_new . appendChild(i_col_new) ;

	i_set     . appendChild(i_row_new) ;

	document.getElementById("AddRow").disabled=true ;

    for(row=0 ; row<i_count.value ; row++) {
	 i_elm=document.getElementById("Delete_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
	 i_elm=document.getElementById("Edit_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
    }

	document.getElementById("KindNew").focus() ;

    return ;  
  } 

  function DeleteNew()
  {
    var  i_set  ;
    var  i_row  ;
    var  i_elm  ;

	i_set=document.getElementById("Certificates") ;
	i_row=document.getElementById("RowNew") ;

	i_set.removeChild(i_row) ;

	document.getElementById("AddRow" ).disabled=false ;

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
     var  i_set ;
     var  i_row_old ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_doc_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_add_new ;
     var  i_elm ;
     var  value ;
     var  elems ;
     var  i ;


	i_doc_new =document.getElementById("TemplateCert").cloneNode(true) ;
	i_doc_new .hidden=false ;

	 elems    = i_doc_new.getElementsByTagName('*') ;
      for(var i=0; i<elems.length; i++)
        if(elems[i].id!='') {

          if(elems[i].id=="Requicites")  elems[i].value=document.getElementById("Requicites_"+p_row).innerHTML ;
          if(elems[i].id=="IssueDate" )  elems[i].value=document.getElementById("IssueDate_" +p_row).innerHTML ;
          if(elems[i].id=="Desc"      )  elems[i].value=document.getElementById("Desc_"      +p_row).innerHTML ;

                               elems[i].id  =elems[i].id+"New" ;
                               elems[i].name=elems[i].id ;        
                            } 

	i_set     = document.getElementById("Certificates") ;
	i_row_old = document.getElementById("Row_"+p_row) ;

	i_row_new = document.createElement("tr") ;
	i_row_new . className = "table" ;
	i_row_new . id        = "RowNew" ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_txt_new = document.createTextNode("Редактирование предыдущей записи") ;
	i_col_new . appendChild(i_txt_new) ;
	i_fld_new = document.createElement("br") ;
	i_col_new . appendChild(i_fld_new) ;
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
	i_fld_new = document.createElement("br") ;
	i_col_new . appendChild(i_fld_new) ;
	i_add_new = document.createElement("input") ;
	i_add_new . type      ="submit" ;
	i_add_new . className ="SaveButton" ;
	i_add_new . value     ="Сохранить" ;
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

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_fld_new = document.createElement("div") ;
	i_fld_new . id        ='ErrorExt' ;

	i_col_new . appendChild(i_doc_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_row_new . appendChild(i_col_new) ;

    if(i_row_old.nextSibling!=null)
	   i_set.insertBefore(i_row_new, i_row_old.nextSibling) ;
    else   i_set.appnedChild (i_row_new) ;

	document.getElementById("AddRow").disabled=true ;

    for(row=0 ; row<i_count.value ; row++) {
	 i_elm=document.getElementById("LiftUp_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
	 i_elm=document.getElementById("Delete_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
	 i_elm=document.getElementById("Edit_"+row) ;
      if(i_elm!=null)  i_elm.disabled=true ;
    }

          value=document.getElementById("Kind_"+p_row).innerHTML ;
       i_select=document.getElementById("KindNew") ;
    for(i=0 ; i<i_select.length ; i++)
      if(i_select.options[i].text==value            ||
                          i      ==i_select.length-1  ) {  i_select.selectedIndex=i ;  break ;  }

         Event_SetKind(value) ;

          value=document.getElementById("Issuer_"+p_row).innerHTML ;
       i_select=document.getElementById("IssuerNew") ;
    for(i=0 ; i<i_select.length ; i++)
      if(i_select.options[i].text==value            ||
                          i      ==i_select.length-1  ) {  i_select.selectedIndex=i ;  break ;  }

         Event_SetIssuer(value) ;

	document.getElementById("DescNew").focus() ;

    return ;         
  } 

  function DeleteRow(p_row)
  {
    var  i_set  ;
    var  i_row  ;
    var  i_elm  ;
    var  order  ;


	i_set=document.getElementById("Certificates") ;
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


	i_set =document.getElementById("Certificates") ;
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

  function  Event_SetKind(p_value)
  {
     var  i_elem ;
     var  i_fld_new ;
     var  i_btn_new ;


       i_elem=document.getElementById("KindNew") ;
       i_elem.options[0].disabled=true ;

    if(i_elem.value=="Other")
    {
	i_fld_new = document.createElement("input") ;
	i_fld_new . id       ="KindNew" ;
	i_fld_new . name     ="KindNew" ;
	i_fld_new . type     ="text" ;
	i_fld_new . maxlength= 50 ;
	i_fld_new . size     = 50 ;
	i_fld_new . value    = p_value ;

	i_btn_new = document.createElement("input") ;
	i_btn_new . type   ="button" ;
	i_btn_new . id     ="KindRestoreSelect" ;
	i_btn_new . value  ="Вернуться к списку" ;
	i_btn_new . onclick= function(e) {  Restore_Select('Kind') ;  }

        i_elem.parentNode.replaceChild(i_fld_new, i_elem) ;

        i_elem=document.getElementById("KindNew") ;
        i_elem.parentNode.insertBefore(i_btn_new, null) ;

        i_fld_new.focus() ;
    }
  }

  function  Event_SetIssuer(p_value)
  {
     var  i_elem ;
     var  i_fld_new ;
     var  i_btn_new ;


       i_elem=document.getElementById("IssuerNew") ;
       i_elem.options[0].disabled=true ;

    if(i_elem.value=="Other")
    {
	i_fld_new = document.createElement("input") ;
	i_fld_new . id       ='IssuerNew' ;
	i_fld_new . name     ='IssuerNew' ;
	i_fld_new . type     ="text" ;
	i_fld_new . maxlength= 120 ;
	i_fld_new . size     = 70 ;
	i_fld_new . value    = p_value ;

	i_btn_new = document.createElement("input") ;
	i_btn_new . type   ="button" ;
	i_btn_new . id     ="IssuerRestoreSelect" ;
	i_btn_new . value  ="Вернуться к списку" ;
	i_btn_new . onclick= function(e) {  Restore_Select('Issuer') ;  }

        i_elem.parentNode.replaceChild(i_fld_new, i_elem) ;

        i_elem=document.getElementById("IssuerNew") ;
        i_elem.parentNode.insertBefore(i_btn_new, null) ;

        i_fld_new.focus() ;
    }
  }

  function  Restore_Select(p_elem)
  {
     var  i_elem ;
     var  i_btn ;
     var  i_fld_new ;


	i_elem    =document.getElementById(p_elem+"New") ;
	i_btn     =document.getElementById(p_elem+"RestoreSelect") ;

	i_fld_new=document.getElementById(p_elem).cloneNode(true) ;
	i_fld_new.hidden= false ;
	i_fld_new.id    =p_elem+"New" ;
	i_fld_new.name  =p_elem+"New" ;

	i_elem.parentNode.removeChild (i_btn) ;
        i_elem.parentNode.replaceChild(i_fld_new, i_elem) ;
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
        <b>КВАЛИФИКАЦИОННЫЕ ДОКУМЕНТЫ ВРАЧА</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST" enctype="multipart/form-data">

  <div class="fieldC" id="Error">
  </div>

  <div class="fieldC">
      <input type="submit" class="SaveButton" value="Сохранить">
  </div>
  <br>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Certificates">

<?php
            ShowCertificates() ;
?>

    </tbody>
  </table>

  <br>
  <div class="fieldC">
    <input type="button" value="Добавить документ" onclick=AddNewRow() id="AddRow">
    <input type="hidden" name="Count" id="Count">
    <input type="hidden" name="NewOrder" id="NewOrder">
    <input type="hidden" name="ReOrder" id="ReOrder">
    <input type="hidden" name="Delete" id="Delete">
  </div>
  <br>
    
  </form>

  <table width="100%" hidden id="TemplateCert">
    <thead>
    </thead>
    <tbody>
      <tr class="fieldL">
        <td><input type="text" size=12 value=" Вид документа:" disabled></td>
        <td>
          <select id="Kind" onchange=Event_SetKind('')></select>
        </td>
      </tr>
      <tr class="fieldL">
        <td><input type="text" size=12 value=" Кем выдан:" disabled></td>
        <td><select id="Issuer" onchange=Event_SetIssuer('')></select></td>
      </tr>
      <tr>
        <td><input type="text" size=12 value=" Серия,номер:" disabled></td>
        <td><input type="text" size=32 id="Requicites"></td>
      </tr>
      <tr>
        <td><input type="text" size=12 value=" Когда выдан:" disabled></td>
        <td><input type="text" size=12 id="IssueDate"></td>
      </tr>
      <tr>
        <td   class="fieldL"><input type="text" size=12 value=" Описание:" disabled></td>
        <td><textarea cols=60 rows=4 wrap="soft" id="Desc"></textarea></td>
      </tr>
      <tr>
        <td><input type="text" size=12 value=" Ссылка:" disabled></td>
        <td>
          <input type="text" size=50 maxlength=510 id="Link">
          <input type="button" value="Проверить" onclick=GoToLink('LinkNew')> 
        </td>
      </tr>
      <tr>
        <td   class="fieldL"><input type="text" size=12 value=" Страница 1:" disabled></td>
        <td>
          <input type="file" accept="image/*" id="File1">
          <input type="hidden" id="File1Name">
        </td> 
      </tr>
      <tr>
        <td   class="fieldL"><input type="text" size=12 value=" Страница 2:" disabled></td>
        <td>
          <input type="file" accept="image/*" id="File2">
          <input type="hidden" id="File2Name">
        </td> 
      </tr>
    </tbody>
  </table>

</div>

</body>

</html>
