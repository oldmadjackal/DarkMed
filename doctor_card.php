<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_card.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $glb_portrait ;

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

			  $speciality="" ;
  if(isset($spec_a))
     foreach($spec_a as $tmp) 
       if($tmp!="Dummy")  $speciality=$speciality.$tmp."," ;
  }

	FileLog("START", "    Session:".$session) ;

  if(isset($name_f)) {
	FileLog("",      "     Name_F:".$name_f) ;
	FileLog("",      "     Name_I:".$name_i) ;
	FileLog("",      "     Name_O:".$name_o) ;
	FileLog("",      " Speciality:".$speciality) ;
	FileLog("",      "     Remark:".$remark) ;
	FileLog("",      "     PageId:".$page_id) ;
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
             $glb_portrait=$fields[6] ;

        FileLog("", "Doctor main page presented successfully") ;

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
//  Отображение портрета

function PortraitView() {

  global  $glb_portrait ;

   if($glb_portrait=="")  echo "<img src=\"images/dummy.jpg\" height=200>" ;
   else                   echo "<img src=\"".$glb_portrait."\" height=200>" ; 
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
    var  i_error ;
    var  a_specialities ;

  function FirstField() 
  {
    var  i_list_new ;
    var  i_link_new ;
    var  i_text_new ;
    var  link_key ;
    var  link_text ;


       i_table   =document.getElementById("Fields") ;
       i_page_id =document.getElementById("PageId") ;
       i_name_f  =document.getElementById("Name_F") ;
       i_name_i  =document.getElementById("Name_I") ;
       i_name_o  =document.getElementById("Name_O") ;
       i_remark  =document.getElementById("Remark") ;
       i_portrait=document.getElementById("Portrait") ;
       i_p_file  =document.getElementById("PortraitFile") ;
       i_error   =document.getElementById("Error") ;

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
     var  error_text ;

	error_text=""

       var  nl=new RegExp("\n","g") ;

       i_remark.value=i_remark.value.replace(nl,"@@") ;

     if(i_p_file.value=="")  i_p_file.name=i_p_file.name+"_" ;
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

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
            <input type="submit" value="Сохранить"> 
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

  </form>

</div>

</body>

</html>
