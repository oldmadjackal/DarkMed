<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescription_view.php" ;

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

                         $get_id=$_GET["Id"] ;

    FileLog("START", "    Id:".$get_id) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Извлечение данных для отображения

          $get_id_=$db->real_escape_string($get_id) ;

                       $sql="Select  r.id, r.user, t.name, r.name, r.reference, r.description, r.www_link".
                            "       ,d.name_f, d.name_i, d.name_o".
                            "  From  prescriptions_registry r".
                            "        inner join doctor_page_main d on d.owner=r.user".
                            "        inner join ref_prescriptions_types t on t.code=r.type and t.language='RU'".
                            " Where  r.id='$get_id_'" ; 
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
                   $owner_name =$fields[7]." ".$fields[8]." ".$fields[9]." (".$fields[1].")" ;
                   $type       =$fields[2] ;
                   $name       =$fields[3] ;
                   $reference  =$fields[4] ;
                   $description=$fields[5] ;
                   $www_link   =$fields[6] ;

        FileLog("", "Prescription data selected successfully") ;

//--------------------------- Вывод данных на страницу

      echo     "                  creator='".$owner      ."' ;\n" ;

      echo     "  i_id         .innerHTML='".$get_id     ."' ;\n" ;
      echo     "  i_owner      .innerHTML='".$owner_name ."' ;\n" ;
      echo     "  i_name       .innerHTML='".$name       ."' ;\n" ;
      echo     "  i_type       .innerHTML='".$type       ."' ;\n" ;
      echo     "  i_reference  .innerHTML='".$reference  ."' ;\n" ;
      echo     "  i_description.innerHTML='".$description."' ;\n" ;
      echo     "  i_www_link   .value    ='".$www_link   ."' ;\n" ;

//      echo     "  SetType('".$type."') ;\n" ;

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

<title>DarkMed Prescription Registry View</title>
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
    var  i_goto ;
    var  i_error ;

    var  creator ;

    var  a_types ;


  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;
     var  text ;
     var  pos ;

       i_table      =document.getElementById("Fields") ;
       i_id         =document.getElementById("Id") ;
       i_owner      =document.getElementById("Owner") ;
       i_type       =document.getElementById("Type") ;
       i_name       =document.getElementById("Name") ;
       i_reference  =document.getElementById("Reference") ;
       i_description=document.getElementById("Description") ;
       i_www_link   =document.getElementById("WWW_link") ;
       i_goto       =document.getElementById("GoToLink") ;
       i_error      =document.getElementById("Error") ;

	a_types=new Array() ;

<?php
            ProcessDB() ;
?>

       i_description.innerHTML=i_description.innerHTML.replace(nl,"\n") ;

                text=i_www_link.value ;
                 pos=text.indexOf("://") ;
    if(pos>=0)  text=text.substr(pos+3) ;
                 pos=text.indexOf("/") ;
    if(pos>=0)  text=text.substr(0, pos) ;

    if(i_www_link.value!='')  i_goto.innerHTML=text ;
    else                      i_goto.hidden   =true ;

         return true ;
  }

  function WhoIsIt()
  {
    window.open("doctor_view.php"+"?Owner="+creator) ;
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
        <b>КАРТОЧКА РЕГИСТРА НАЗНАЧЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="field"><b> Код: </b></td>
      <td> <dev id="Id"></dev> </td>
    </tr>
    <tr>
      <td class="field"><b> Создано: </b></td>
      <td> <span id="Owner"></span>
           <input type="button" value="Кто это?" onclick=WhoIsIt()></td>
      </td>
    </tr>
    <tr>
      <td class="field"><b> Категория: </b></td>
      <td> <div id="Type"></div> </td>
    </tr>
    <tr>
      <td class="field"><b> Название: </b></td>
      <td> <div id="Name"></div> </td>
    </tr>
    <tr>
      <td class="field"><b> Регистр: </b></td>
      <td> <div id="Reference"></div> </td>
    </tr>
    <tr>
      <td class="field"><b> Смотреть на: </b></td>
      <td>
         <a href="javascript:
                  window.open(document.getElementById('WWW_link').value) ;"
                     id="GoToLink">Смотреть на</a>
          <input type="hidden" maxlength=510 id="WWW_link"> 
      </td>
    </tr>
    <tr>
      <td class="field"><b> Описание: </b></td>
      <td> <div id="Description"></div> </td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
