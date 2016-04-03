<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescription_pilot.php" ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД

function ProcessDB() {

  global  $sys_ext_count  ;
  global  $sys_ext_type   ;
  global  $sys_ext_file   ;
  global  $sys_ext_sfile  ;

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

                       $sql="Select  r.name, r.description".
                            "  From  prescriptions_registry r".
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

                   $name       =$fields[0] ;
                   $description=$fields[1] ;

        FileLog("", "Prescription data selected successfully") ;

//--------------------------- Извлечение дополнительных блоков

                     $sql="Select e.type, e.file, e.short_file".
			  "  From prescriptions_ext e".
			  " Where e.prescription_id='$get_id_'".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_EXT... : ".$db->error) ;
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

          $sys_ext_type [$i]=$fields[0] ;
          $sys_ext_file [$i]=$fields[1] ;
          $sys_ext_sfile[$i]=$fields[2] ;
     }

  }

     $res->close() ;

//--------------------------- Вывод данных на страницу

      echo     "  i_name       .innerHTML='".$name       ."' ;\n" ;
      echo     "  i_description.innerHTML='".$description."' ;\n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}
//============================================== 
//  Отображение дополнительных блоков описания

function ShowExtensions() {

  global  $sys_ext_count  ;
  global  $sys_ext_type   ;
  global  $sys_ext_file   ;
  global  $sys_ext_sfile  ;

        $sys_ext_count=1 ;

  for($i=0 ; $i<$sys_ext_count ; $i++)
  {

    if($sys_ext_type[$i]=="Image") {
//     echo "<div class='fieldC'>					\n" ; 
       echo "<img src='".$sys_ext_sfile[$i]."' height=200		\n" ;
       echo " onclick=\"window.open('".$sys_ext_file[$i]."')\" ;	\n" ;
       echo ">								\n" ; 
//     echo "</div>							\n" ; 

          break ;
    }

  }

}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\r\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\r\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;			\r\n" ;
    echo  "i_error.innerHTML  ='Данные успешно сохранены!' ;	\r\n" ;
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

    var  i_name ;
    var  i_description ;
    var  i_error ;

    var  creator ;

    var  a_types ;


  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;
     var  text ;
     var  pos ;

       i_name       =document.getElementById("Name") ;
       i_description=document.getElementById("Description") ;
       i_error      =document.getElementById("Error") ;

	a_types=new Array() ;

<?php
            ProcessDB() ;
?>

       i_description.innerHTML=i_description.innerHTML.replace(nl,"<br>") ;

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

<div class="inputF">

  <table width="100%" id="Fields" hidden>
    <thead>
    </thead>
    <tbody>
    <tr>
      <td> <div id="Name"></div> </td>
    </tr>
    <tr>
      <td> <div id="Description"></div> </td>
    </tr>
    </tbody>
  </table>

<?php
            ShowExtensions() ;
?>

</div>

</body>

</html>
