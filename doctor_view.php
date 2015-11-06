<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_view.php" ;

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

                        $session=$_GET["Session"] ;
                        $owner  =$_GET["Owner"] ;

   FileLog("START", "    Session:".$session) ;
   FileLog("",      "      Owner:".$owner) ;

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

                       $sql="Select name_f, name_i, name_o, speciality, remark, portrait".
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

                 $name_fio=$fields[0]." ".$fields[1]." ".$fields[2] ;
               $speciality=$fields[3] ;
                   $remark=$fields[4] ;

             $glb_portrait=$fields[5] ;

        FileLog("", "Doctor main page presented successfully") ;

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

  global  $glb_portrait ;

   if($glb_portrait!="")  echo "<img src=\"".$glb_portrait."\" height=200>" ; 
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

</div>

</body>

</html>
