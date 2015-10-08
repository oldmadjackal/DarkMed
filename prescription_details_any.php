<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescription_details_any.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

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
                        $id     =$_GET ["Id"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "         Id:".$id) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Извлечение данных карточки назначения

                       $id_=$db->real_escape_string($id) ;

                       $sql="Select  r.user, t.name, r.name, r.reference, r.description, r.www_link".
                            "  From  prescriptions_registry r".
                            "        inner join ref_prescriptions_types t on t.code=r.type and t.language='RU'".
                            " Where  r.id=$id_" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select * from PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }
    else
    if($res->num_rows==0) {
          FileLog("ERROR", "No such prescription in DB : ".$id) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестное назначение") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $owner      =$fields[0] ;
                   $type       =$fields[1] ;
                   $name       =$fields[2] ;
                   $reference  =$fields[3] ;
                   $description=$fields[4] ;
                   $www_link   =$fields[5] ;

        FileLog("", "Prescription data selected successfully") ;

//--------------------------- Формирование данных страницы

      echo "  i_id         .innerHTML='".$id         ."' ;	\n" ;
      echo "  i_owner      .innerHTML='".$owner      ."' ;	\n" ;
      echo "  i_type       .innerHTML='".$type       ."' ;	\n" ;
      echo "  i_name       .innerHTML='".$name       ."' ;	\n" ;
      echo "  i_reference  .innerHTML='".$reference  ."' ;	\n" ;
      echo "  i_description.innerHTML='".$description."' ;	\n" ;
      echo "  i_www_link   .value    ='".$www_link.   "' ;	\n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
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

<title>DarkMed Prescription registry details any-form</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_id ;
    var  i_owner ;
    var  i_type ;
    var  i_name ;
    var  i_reference ;
    var  i_description ;
    var  i_www_link ;
    var  i_goto ;
    var  i_error ;

  function FirstField() 
  {
    var  text ;
    var  pos ;

       i_id         =document.getElementById("Id") ;
       i_owner      =document.getElementById("Owner") ;
       i_type       =document.getElementById("Type") ;
       i_name       =document.getElementById("Name") ;
       i_reference  =document.getElementById("Reference") ;
       i_description=document.getElementById("Description") ;
       i_www_link   =document.getElementById("WWW_link") ;
       i_goto       =document.getElementById("GoToLink") ;
       i_error      =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

                text=i_www_link.value ;
                 pos=text.indexOf("://") ;
    if(pos>=0)  text=text.substr(pos+3) ;
                 pos=text.indexOf("/") ;
    if(pos>=0)  text=text.substr(0, pos) ;

    if(i_www_link.value!='')  i_goto.innerHTML='Смотреть на '+text ;
    else                      i_goto.hidden   = true ;

         return true ;
  }

  function GoToView()
  {
    window.open("prescription_view.php?Id="+i_id.innerHTML) ;
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
      <td width="20%">
         <b>Код: <dev id="Id"> </dev></b>
         (<dev id="Owner"> </dev>)<br>
         <input type="button" value="Полностью" onclick=GoToView()><br>
         <dev id="Type"> </dev> <br>
         <dev id="Reference"> </dev> <br>
      </td>
      <td width="2%">
      </td>
      <td width="73%">
         <b><div id="Name"></div></b>
         <a href="javascript:
                  window.open(document.getElementById('WWW_link').value) ;"
                     Id="GoToLink">Смотреть на</a></b>
         <i><div id="Description"></div></i>
         <input type="hidden" Name="WWW_link" Id="WWW_link">
      </td>
    </tr>
    </tbody>
  </table>

</body>

</html>
