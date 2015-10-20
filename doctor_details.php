<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_details.php" ;

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
                        $doctor =$_GET ["Doctor" ] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "     Doctor:".$doctor) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Извлечение данных врача

                 $doctor_=$db->real_escape_string($doctor) ;

                     $sql="Select name_f, name_i, name_o, speciality, remark, sign_p_key, portrait".
                          "  From doctor_page_main d, users u".
                          " Where d.owner='$doctor_'".
                          "  and  d.owner=u.login" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DOCTOR_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
  }
  if($res->num_rows==0) {
          FileLog("ERROR", "No such doctor in DB) : ".$doctor) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестный врач") ;
                         return ;
  }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

        FileLog("", "Doctor main page presented successfully") ;

//--------------------------- Расшифровка специальностей

	$speciality=$fields[3] ;

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

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
                $pair=$res->fetch_row() ;
          $speciality=str_replace($pair[0], $pair[1], $speciality) ;
     }

          $speciality=substr($speciality, 0, strlen($speciality)-1) ;
          $speciality=str_replace(",", ", ", $speciality) ;

//--------------------------- Формирование данных страницы

      echo     "  i_name      .innerHTML='".$fields[0] ." ".$fields[1]." ".$fields[2]."' ;\n" ;
      echo     "  i_speciality.innerHTML='".$speciality."' ;\n" ;
      echo     "  i_remark    .innerHTML='".$fields[4] ."' ;\n" ;

      echo     "  i_login     .value    ='".$doctor    ."' ;\n" ;
      echo     "  i_sign_key  .value    ='".$fields[5] ."' ;\n" ;

                           $glb_portrait=$fields[6] ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение портрета

function PortraitView() {

  global  $glb_portrait ;

   if($glb_portrait!="")  echo "<img src=\"pictures/".$glb_portrait."\" height=100>" ; 
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

<title>DarkMed Doctor details card</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;
    var  i_name ;
    var  i_speciality ;
    var  i_remark ;
    var  i_login ;
    var  i_sign_key ;
    var  i_error ;

  function FirstField() 
  {
       i_name      =document.getElementById("Name") ;
       i_speciality=document.getElementById("Speciality") ;
       i_remark    =document.getElementById("Remark") ;
       i_error     =document.getElementById("Error") ;
       i_login     =document.getElementById("Login") ;
       i_sign_key  =document.getElementById("Sign_key") ;

<?php
            ProcessDB() ;
?>

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

  <table border="0" width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr class="fieldL">
      <td width="35%">
         <b><dev id="Name"> </dev></b> 
         <p id="Speciality"> </p>
      </td>
      <td width="5%">
      </td>
      <td width="35%">
         <i><div id="Remark"></div></i>
      </td>
      <td width="5%">
      </td>
      <td width="20%">
<?php
            PortraitView() ;
?>
      </td>
    </tr>
    </tbody>
  </table>

<input type="hidden" name="Login"    id="Login"   >
<input type="hidden" name="Sign_key" id="Sign_key">

</body>

</html>
