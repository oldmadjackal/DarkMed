<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_doctors_list_footer.php" ;

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

  FileLog("START", "    Session:".$session) ;

//--------------------------- Завершение

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешном исполнении

function SuccessMsg() {

    echo  "i_error.style.color='green' ;				\n" ;
    echo  "i_error.innerHTML  ='Релиз помечен как прочитанный' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed-Mobile Doctors List Footer</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script type="text/javascript">
<!--

    var  i_error ;

  function FirstField() 
  {
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

         return true ;
  }

  function GoToList() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

     parent.frames['section'].location.assign("mob_messages.php"+"?Session="+v_session) ;
  }

  function GoToMenu()
  {
    var  v_user ;
    var  v_menu ;

	 v_user=TransitContext("restore","user","") ;

     if(v_user.indexOf("Doctor;")>=0)  v_menu='mob_menu_doctor.php' ;
     else                              v_menu='mob_menu_client.php' ;

      parent.frames['section'].location.assign(v_menu);
  }


  function GoToNextPage() 
  {
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

  <table width="100%">
    <thead>
    </thead>
    <tbody>
    <tr class="fieldC">
      <td width="34%"> 
       <input class="B_bttn" type="button" value="Список" onclick=GoToList()>
      </td> 
      <td> 
       <input class="G_bttn" type="button" value="Меню" onclick=GoToMenu()>
      </td> 
      <td width="34%">
      </td> 
    </tr>
    </tbody>
  </table>


</body>

</html>
