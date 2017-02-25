<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_view.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $sys_portrait   ;
  global  $sys_ext_count  ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_file   ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;

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

          $owner=$_GET["Owner"] ;

   FileLog("START", " Owner:".$owner) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Извлечение списка специальностей

                     $sql="Select code, name".
			  "  From ref_doctor_specialities".
			  " Where language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_DOCTOR_SPECIALITIES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника специальностей") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       $spec_list[$fields[0]]=$fields[1] ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение данных врача

          $owner_=$db->real_escape_string($owner) ;

                       $sql="Select id, name_f, name_i, name_o, speciality, remark, portrait".
                            " From  doctor_page_main".
                            " Where owner='$owner_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                  $page_id=$fields[0] ;
                 $name_fio=$fields[1]." ".$fields[2]." ".$fields[3] ;
               $speciality=$fields[4] ;
                   $remark=$fields[5] ;

             $sys_portrait=$fields[6] ;

        FileLog("", "Doctor main page presented successfully") ;

//--------------------------- Извлечение дополнительных блоков

                     $sql="Select e.type, e.remark, e.file, e.short_file, e.www_link".
			  "  From doctor_page_ext e".
			  " Where e.page_id=$page_id".
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

          $sys_ext_type  [$i]=$fields[0] ;
          $sys_ext_remark[$i]=$fields[1] ;
          $sys_ext_file  [$i]=$fields[2] ;
          $sys_ext_sfile [$i]=$fields[3] ;
          $sys_ext_link  [$i]=$fields[4] ;
     }

  }

     $res->close() ;

//--------------------------- Извлечение списка квалификационных документов

                     $sql="Select id".
			  "      ,ifnull(k.name,c.kind), ifnull(i.name,c.issuer), requicites, issue_date, `desc`, www_link".
                          "      ,image_1, image_1_s, image_2, image_2_s".
			  "  From doctor_certificates c left outer join ref_cert_kinds   k on c.kind  =k.code and k.language='RU'".
			  "                             left outer join ref_cert_issuers i on c.issuer=i.code and i.language='RU'".
			  " Where owner='$owner_'".
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

  }

     $res->close() ;

//--------------------------- Отображение данных на форме

      echo     "  i_name_fio.innerHTML='".$name_fio."' ;	\n" ;
      echo     "  i_remark  .innerHTML='".$remark  ."' ;	\n" ;

		$speciality_a=explode(",", $speciality) ;

	foreach($speciality_a as $spec)     
        { 
          if($spec!="")  echo "  AddNewSpeciality('".$spec_list[$spec]."') ;\n" ;
        }
//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение портрета

function PortraitView() {

  global  $sys_portrait ;

   if($sys_portrait!="")  echo "<img src=\"".$sys_portrait."\" height=200>" ; 
}

//============================================== 
//  Отображение дополнительных блоков описания

function ShowExtensions() {

  global  $sys_ext_count  ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_file   ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;


  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
       echo  "  <tr class='table'>			\n" ;
       echo  "    <td  width='5%'></td>			\n" ;
       echo  "    <td  class='table' width='5%'>	\n" ;

    if($sys_ext_type[$i]=="Image") {
       echo "<div class='fieldC'>					\n" ; 
       echo "<img src='".$sys_ext_sfile[$i]."' height=200		\n" ;
       echo " onclick=\"window.open('".$sys_ext_file[$i]."')\" ;	\n" ;
       echo ">								\n" ; 
       echo "</div>							\n" ; 
       echo "<br>							\n" ;
    }

       echo  "    </td>						\n" ;
       echo  "    <td class='table'>				\n" ;
       echo  "      <div>					\n" ;
       echo  htmlspecialchars(stripslashes($sys_ext_remark[$i]), ENT_COMPAT, "windows-1251") ;
       echo  "      </div>					\n" ;
       echo  "    <br>						\n" ;

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

       echo  "    </td>				\n" ;
       echo  "    <td  width='5%'></td>		\n" ;
       echo  "  </tr>				\n" ;
  }

}

//============================================== 
//  Отображение квалификационных документов

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
       echo  "    <td  width='5%'></td>		\n" ;
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
       echo  "      <div id='Kind_".$row."'><b>							\n" ;
       echo  htmlspecialchars(stripslashes($sys_doc_kind[$i]), ENT_COMPAT, "windows-1251") ;
       echo  "      </b></div>									\n" ;
       echo  "      <div id='Issuer_".$row."'>							\n" ;
       echo  htmlspecialchars(stripslashes($sys_doc_issuer[$i]), ENT_COMPAT, "windows-1251") ;
       echo  "      </div>									\n" ;
       echo  "      <div>									\n" ;
       echo  "      <span id='Requicites_".$row."'>".$sys_doc_requicites[$i]."</span>		\n" ;
       echo  "      от <span id='IssueDate_".$row."'>".$sys_doc_issue_date[$i]."</span>		\n" ;
       echo  "      </div>									\n" ;
       echo  "      <div id='Desc_".$row."'>							\n" ;
       echo  htmlspecialchars(stripslashes($sys_doc_desc[$i]), ENT_COMPAT, "windows-1251") ;
       echo  "      </div>									\n" ;

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
       echo  "    <td  width='5%'></td>		\n" ;
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

<title>DarkMed Doctor View</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_table ;
    var  i_name_fio ;
    var  i_spec ;
    var  i_remark ;
    var  i_error ;

  function FirstField() 
  {
    var  i_list_new ;
    var  i_link_new ;
    var  i_text_new ;
    var  link_key ;
    var  link_text ;
    var  nl=new RegExp("@@","g") ;


       i_name_fio=document.getElementById("Name_FIO") ;
       i_spec    =document.getElementById("Specialities") ;
       i_remark  =document.getElementById("Remark") ;
       i_error   =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

       i_remark.innerHTML=i_remark.innerHTML.replace(nl,"<br>") ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

	error_text=""

       var  nl=new RegExp("\n","g") ;

     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;         
  } 

  function AddNewSpeciality(p_spec)
  {
     var  i_div_new ;
     var  i_txt_new ;

       i_div_new=document.createElement("div") ;
       i_txt_new=document.createTextNode(p_spec) ;
       i_div_new.appendChild(i_txt_new) ;	
       i_spec   .appendChild(i_div_new) ;	

    return ;         
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
        <b>ФОРМУЛЯР ВРАЧА (ПРОСМОТР)</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>

  <table width="100%" id="Fields">
  <thead>
  </thead>
  <tbody>
  <tr>
    <td width="78%">
      <table id="Fields">
        <thead>
        </thead>
        <tbody>
        <tr>
          <td class="field"> </td>
          <td> <div class="error" id="Error"></div> </td>
        </tr>
        <tr>
          <td class="field"><b>ФИО:</b></td>
          <td> <div id="Name_FIO"></div></td>
        </tr>
        <tr>
          <td class="field"><b>Специальность:</b></td>
          <td id="Specialities">
          </td>
        </tr>
        <tr>
          <td class="field"><b>Примечание:</b></td>
          <td> <div id="Remark"></div></td>
        </tr>
        </tbody>
      </table>
    </td>
    <td width="2%">
    </td>
    <td width="20%">

<?php
            PortraitView() ;
?>

    </td>
  </tr>
  </tbody>
  </table>
  <br>

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
  <br>

  <div class="fieldC">
    <b>Квалификационные документы</b>
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

</div>

</body>

</html>
