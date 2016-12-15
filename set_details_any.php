<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Set_details_any.php" ;

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
                        $append =$_GET ["Append"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "         Id:".$id) ;
  FileLog("",      "     Append:".$append) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Извлечение данных карточки назначения

                       $id_=$db->real_escape_string($id) ;

                       $sql="Select  r.name, r.description".
                            "  From  sets_registry r".
                            " Where  r.id=$id_" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select * from SETS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }
    else
    if($res->num_rows==0) {
          FileLog("ERROR", "No such prescription in DB : ".$id) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестный комплекс") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $name       =$fields[0] ;
                   $description=$fields[1] ;

        FileLog("", "Set data selected successfully") ;

//--------------------------- Формирование данных страницы

      echo "  i_id         .value    ='".$id         ."' ;	\n" ;
      echo "  i_name       .innerHTML='".$name       ."' ;	\n" ;
      echo "  i_description.innerHTML='".$description."' ;	\n" ;

//--------------------------- Извлечение состава комплекса

                     $sql="Select e.prescription_id, r.type, e.remark".
			  "  From sets_elements e left outer join prescriptions_registry r on e.prescription_id=r.id".
                          " Where e.set_id=$id_".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select SETS_ELEMENTS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса элементов комплекса назначений") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_id    ['".$i."']='".$fields[0]."' ;	\n" ;
       echo "   a_type  ['".$i."']='".$fields[1]."' ;	\n" ;
       echo "   a_remark['".$i."']='".$fields[2]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
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
    echo  "i_error.innerHTML  ='Данные успешно сохранены!'	\r\n;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Set details any-form</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("tables.css") ;
  @import url("text.css") ;
</style>

<script type="text/javascript">
<!--

    var  i_id ;
    var  i_name ;
    var  i_description ;
    var  i_error ;
    var  a_id ;
    var  a_type ;
    var  a_remark ;

  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;

       i_id         =document.getElementById("Id") ;
       i_name       =document.getElementById("Name") ;
       i_description=document.getElementById("Description") ;
       i_error      =document.getElementById("Error") ;

       a_id    =new Array() ;
       a_type  =new Array() ;
       a_remark=new Array() ;

<?php
            ProcessDB() ;
?>

       i_description.innerHTML=i_description.innerHTML.replace(nl,"<br>") ;

         return true ;
  }

  function GoToView()
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

    window.open("set_view.php"+"?Session="+v_session+"&Id="+i_id.value) ;
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

  <div class="Error_LT" id="Error"></div>

  <div class="Normal_CT">
        <input type="button" value="Полностью" onclick=GoToView()>
  </div>
  <br>
  <div class="Bold_CT" id="Name"></div>

  <br>
  <i><div id="Description"></div></i>

  <input type="hidden" id="Id">

</body>

</html>
