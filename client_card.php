<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_card.php" ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД

function ProcessDB() {

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

  if(isset($_GET ["Owner"]))  $owner=$_GET ["Owner"] ;

  if(isset($_POST["Name_F"])) 
  {
                         $name_f =$_POST["Name_F"] ;
                         $name_i =$_POST["Name_I"] ;
                         $name_o =$_POST["Name_O"] ;
                         $remark =$_POST["Remark"] ;
                         $anatomy=$_POST["Anatomy"] ;
                         $check  =$_POST["Check"] ;
  }

             FileLog("START", "Session:".$session) ;

  if(isset($owner))
             FileLog("",      "  Owner:".$owner) ;

  if(isset($name_f))
  {
             FileLog("",      "  Check:".$check) ;
             FileLog("",      " Name_F:".$name_f) ;
             FileLog("",      " Name_I:".$name_i) ;
             FileLog("",      " Name_O:".$name_o) ;
             FileLog("",      " Remark:".$remark) ;
             FileLog("",      "Anatomy:".$anatomy) ;
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

  if(!isset($owner))  $owner =$user ; 

  if($owner!=$user)  $read_only=true ; 
  else               $read_only=false ; 

  if($read_only)  echo  "   doctor='Doctor' ;\n" ;

                      $owner_=$db->real_escape_string($owner) ;
                      $user_ =$db->real_escape_string($user ) ;

//--------------------------- Извлечение ключа шифрования главной страницы

                       $sql="Select crypto ".
                            "  From access_list ".
                            " Where owner='$owner_' ".
                            "  and  login='$user_' " ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения ключа доступа") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_key=\"" .$fields[0]."\" ;\n" ;

//--------------------------- Извлечение данных для отображения
//
//  Сохранение допускается только для владельца страницы

  if(!isset($check) || $read_only)
  {
                       $sql="Select `check`, name_f, name_i, name_o, remark, anatomy".
                            "  From  client_page_main".
                            " Where  owner='$owner_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGE_MAIN... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $check  =$fields[0] ;
                   $name_f =$fields[1] ;
                   $name_i =$fields[2] ;
                   $name_o =$fields[3] ;
                   $remark =$fields[4] ;
                   $anatomy=$fields[5] ;

        FileLog("", "User main page selected successfully") ;
  }
//--------------------------- Сохранение данных со страницы
  else
  {
          $name_f_ =$db->real_escape_string($name_f) ;
          $name_i_ =$db->real_escape_string($name_i) ;
          $name_o_ =$db->real_escape_string($name_o) ;
          $remark_ =$db->real_escape_string($remark) ;
          $anatomy_=$db->real_escape_string($anatomy) ;

                       $sql="Update client_page_main".
                            " Set   name_f ='$name_f_'".
                            "      ,name_i ='$name_i_'".
                            "      ,name_o ='$name_o_'".
                            "      ,remark ='$remark_'".
                            "      ,anatomy='$anatomy_'".
                            " Where owner ='$user_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update CLIENT_PAGE_MAIN... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "User main page saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Вывод данных пациента на страницу

      echo     "  i_check .value=\"".$check ."\" ;\n" ;
      echo     "  i_name_f.value=\"".$name_f."\" ;\n" ;
      echo     "  i_name_i.value=\"".$name_i."\" ;\n" ;
      echo     "  i_name_o.value=\"".$name_o."\" ;\n" ;
      echo     "  i_remark.value=\"".$remark."\" ;\n" ;
      echo     "  i_anatomy.value='".$anatomy."' ;\n" ;

//--------------------------- Обработка режима READ ONLY

  if($read_only)
  {
      echo     "  SetReadOnly() ;\n" ;
  }

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;      " ;
    echo  "i_error.innerHTML  ='".$text."' ;" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;                    " ;
    echo  "i_error.innerHTML  ='Данные успешно сохранены!' ;" ;
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
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_check ;
    var  i_name_f ;
    var  i_name_i ;
    var  i_name_o ;
    var  i_remark ;
    var  i_anatomy ;
    var  i_error ;
    var  password ;
    var  page_key ;
    var  check_key ;
    var  doctor ;

  function FirstField() 
  {
    var  i_list_new ;
    var  i_link_new ;
    var  i_text_new ;
    var  link_key ;
    var  link_text ;


       i_check =document.getElementById("Check") ;
       i_name_f=document.getElementById("Name_F") ;
       i_name_i=document.getElementById("Name_I") ;
       i_name_o=document.getElementById("Name_O") ;
       i_remark=document.getElementById("Remark") ;
       i_anatomy=document.getElementById("Anatomy") ;
       i_error =document.getElementById("Error") ;

       i_name_f.focus() ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

       page_key= Crypto_decode( page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
	i_error.style.color="red" ;
	i_error.innerHTML  ="Ошибка расшифровки данных." ;
         return true ;
     }

       i_name_f .value=Crypto_decode(i_name_f.value, page_key) ;
       i_name_i .value=Crypto_decode(i_name_i.value, page_key) ;
       i_name_o .value=Crypto_decode(i_name_o.value, page_key) ;
       i_remark .value=Crypto_decode(i_remark.value, page_key) ;
       i_anatomy.value=Crypto_decode(i_anatomy.value, page_key) ;

     if(doctor=="Doctor")
     {
        parent.frames["details"].SetAnatomy(i_anatomy.value) ;
     }
     else
     {
        parent.frames["anatomy"].location.replace('anatomia_main.html?groups='+i_anatomy.value) ;
     }

         return true ;
  }

  function SetReadOnly() 
  {
       i_name_f.readOnly=true ;
       i_name_i.readOnly=true ;
       i_name_o.readOnly=true ;
       i_remark.readOnly=true ;

       document.getElementById("Save1"      ).hidden =true ; 
       document.getElementById("AnatomyText").hidden =true ; 
  }

  function SendFields() 
  {
     var  error_text ;

	error_text=""
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

       i_name_f.value=Crypto_encode(i_name_f.value, page_key) ;
       i_name_i.value=Crypto_encode(i_name_i.value, page_key) ;
       i_name_o.value=Crypto_encode(i_name_o.value, page_key) ;
       i_remark.value=Crypto_encode(i_remark.value, page_key) ;

       i_anatomy.value=parent.frames["anatomy"].GetGroups() ;
       i_anatomy.value=Crypto_encode(i_anatomy.value, page_key) ;

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

  <table width="90%">
    <tbody>
    <tr>
      <td width="10%"> 
        <input type="button" class="HelpButton"     value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" class="CallBackButton" value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="FormTitle"> 
        <b>КАРТА ПАЦИЕНТА</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">
  <table width="100%" id="Fields">
    <tbody>
    <tr>
      <td class="Normal_RT"> </td>
      <td> <br> <input type="submit" class="SaveButton" value="Сохранить"  id="Save1"> </td>
    </tr>
    <tr>
      <td class="Normal_RT"> </td>
      <td> <div class="Error_CT" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="Normal_RT"> Фамилия </td>
      <td> <input type="text" size=60 name="Name_F" id="Name_F"> </td>
    </tr>
    <tr>
      <td class="Normal_RT"> Имя </td>
      <td> <input type="text" size=60 name="Name_I" id="Name_I"> </td>
    </tr>
    <tr>
      <td class="Normal_RT"> Отчество </td>
      <td> <input type="text" size=60 name="Name_O" id="Name_O"> </td>
    </tr>
    <tr>
      <td class="Normal_RT"> Жалобы </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Remark" id="Remark"> </textarea>
      </td>
    </tr>
    <tr>
      <td class="Normal_RT"> </td>
      <td>
        <input type="hidden" name="Check"   id="Check"  >
        <input type="hidden" name="Anatomy" id="Anatomy">
      </td>
    </tr>
    </tbody>
  </table>

  </form>

  <br>
  <div class="Normal_CT" id="AnatomyText"> 
    <b>На анатомической схеме справа укажите болевые зоны</b>
  </div>

</body>

</html>
