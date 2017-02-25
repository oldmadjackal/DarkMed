<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Help.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных данных в БД

function RegistryDB() {

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Запрос списка релизов

                     $sql="Select id, date(created), title, notes".
                          "  From releases".
                          " Order by created desc" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select RELEASES... : ".$db->error) ;
          InfoMsg("Ошибка на сервере. <br>Детали: ошибка получения списка релизов") ;
  }
  else {

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   AddRelease(".$fields[0].", '".$fields[1]."', '".$fields[2]."', '".$fields[3]."') ;	\n" ;
     }

                    $res->free() ;
  }

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg($session) {

    echo  "TransitContext('save', 'session', '".$session."') ;	\n" ;

    echo  "i_error.style.color='green' ;				\n" ;
    echo  "i_error.innerHTML  ='Авторизация успешно пройдена!' ;	\n" ;
}
//============================================== 

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Help</title>
<meta http-equiv="Content-Type" content="text/html" charset="windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
?>

    var  i_releases ;
    var  i_error ;    

  function FirstField() 
  {
       i_releases=document.getElementById("Releases") ;
       i_error   =document.getElementById("Error") ;

<?php
            RegistryDB() ;
?>

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;


        error_text="" ;
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                   return true ;         
  } 

  function AddRelease(p_id, p_date, p_name, p_link)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_lnk_new ;
     var  i_shw_new ;


       i_row_new = document.createElement("tr") ;
       i_row_new . id     ='Release_'+p_id ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "fieldC" ;
       i_txt_new = document.createTextNode(p_date) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "fieldL" ;
       i_lnk_new = document.createElement("a") ;
       i_lnk_new . href="#" ;
       i_lnk_new . onclick= function(e) {
					   window.open("releases/"+p_link) ;
					} ;
       i_txt_new = document.createTextNode(p_name) ;
       i_lnk_new . appendChild(i_txt_new) ;
       i_col_new . appendChild(i_lnk_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_releases. appendChild(i_row_new) ;

    return ;         
  }


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
        <b>ПОМОЩЬ И ИНСТРУКЦИИ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p>В верхнем левом углу каждой страницы находятся 2 кнопки, обозначенные символами <font color=red>[?]</font> и <font color=red>[!]</font></p>
  <p>При нажатии на кнопку с символом <font color=red>[?]</font> в отдельной вкладке откроется окно с информацией по использованию текущей диалоговой формы.</p>
  <p>При нажатии на кнопку с символом <font color=red>[!]</font> в отдельной вкладке откроется окно для формирования сообщения, которое
     будет направлено администрации портала и в котором Вы можете изложить свои замечания и предложения.
     <br>      
     Предложения и замечания других пользователей можно просмотреть на вкладке главного меню <b>"Сообщения пользователей"</b></p>
  <p>Ниже приведены документы, в которых описан весь процесс работы пользователя с порталом, в зависимости от его роли:</p>

  <ul class="menu">
    <li><a href="tutorial\DarkMed_doctor.docx"  target="section">Руководство пользователя для врача (docx-файл)</a></li> 
    <li><a href="tutorial\DarkMed_client.docx"  target="section">Руководство пользователя для пациента (docx-файл)</a></li> 
  </ul>

  <p>Кроме того, предлагаются видеокурсы по использованию портала:</p>

  <ul class="menu">
    <li><a href="tutorial\Doctor_1_prepare.mp4"      target="section">Видеокурс врача - основной портал, начало работы, подготовка комплексов упражнений (mp4-файл)</a></li> 
    <li><a href="tutorial\Doctor_2_appointment.mp4"  target="section">Видеокурс врача - основной портал, прием пациента и направление назначения (mp4-файл)</a></li> 
    <li><a href="tutorial\Client_1.mp4"              target="section">Видеокурс пациента - основной портал (mp4-файл)</a></li> 
    <li><a href="tutorial\Client_mobile_1.mp4"       target="section">Видеокурс пациента - мобильный портал (mp4-файл)</a></li> 
  </ul>

  <div class="error" id="Error"></div>

  <br>
  <form onsubmit="return SendFields();" method="POST">

  <div class="fieldC"id="Releases_intro"> 
    <b>Перечень последних обновлений(релизов).</b>
    <br>
    Для просмотра содержания обновления кликните по его названию - описание откроется в соседней вкладке.
    <br>
  </div>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Releases">
      <tr> 
       <td width="12%"> </td>
       <td> </td>
      </tr> 
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
