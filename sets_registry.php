<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Sets_registry.php" ;

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

  FileLog("START", "    Session:".$session) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

  if(!isset($session))  $session="" ;

                        $options="" ;

  if($session!="") {

       $user=DbCheckSession($db, $session, $options, $error) ;
                    FileLog("", "User Options:".$options) ;

    if($user===false) {
                    ErrorMsg($error) ;
                         return ;
    }
  }

       $user_=$db->real_escape_string($user ) ;

  if(strpos($options, "UserType=Doctor;")===false) {
	      echo     "  SetReadOnly() ;\n" ;
                    ErrorMsg("Данная секция доступна только для врачей") ;
                         return ;
  }
//--------------------------- Формирование списка комплексов назначений

                     $sql="Select id, name, description".
			  "  From sets_registry".
                          " Where user='$user_'".
                          "  and  name not like '#%#'".                      
                          " Order by name" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select SETS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра комплексов") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "Sets registry is empty") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "    set_id  ='".$fields[0]."' ;		\n" ;
       echo "    set_name='".$fields[1]."' ;		\n" ;
       echo "    set_desc='".$fields[2]."' ;		\n" ;
       echo "  AddNewRow(set_id, set_name, set_desc) ;	\n" ;
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

    echo  "i_error.style.color=\"red\" ;      " ;
    echo  "i_error.innerHTML  =\"".$text."\" ;" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    " ;
    echo  "i_error.innerHTML  =\"Выполнено.\" ;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Messages Sets Registry</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_table ;
    var  i_error ;

  function FirstField() 
  {
    var  msg_text ;

       i_error   =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function AddNewRow(p_id, p_name, p_desc)
  {
     var  i_sets ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_edt_new ;

       i_sets    = document.getElementById("Sets") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Подробнее" ;
       i_shw_new . id     ="Details_"+p_id ;
       i_shw_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="set_details_any.php" ;
						parent.frames["details"].location.assign(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Id="+p_id) ;
					} ;
       i_col_new . appendChild(i_shw_new) ;

       i_edt_new = document.createElement("input") ;
       i_edt_new . type   ="button" ;
       i_edt_new . value  ="Править" ;
       i_edt_new . id     ="Edit_"+p_id ;
       i_edt_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="set_edit.php" ;
 						          location.assign(v_form+"?Session="+v_session+
                                                                                 "&Id="+p_id) ;
					} ;
       i_col_new . appendChild(i_edt_new) ;

       i_row_new . appendChild(i_col_new) ;
       i_sets    . appendChild(i_row_new) ;

    return ;         
  } 

  function NewSet() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("set_edit.php"+"?Session="+v_session) ;
  } 

  function SetReadOnly() 
  {
    var  i_new ;

       i_new=document.getElementById("NewSet") ;

       i_new.disabled=true ;
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

<div>

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
        <b>КОМПЛЕКСЫ НАЗНАЧЕНИЙ И УПРАЖНЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div hight=20%><br></div>
  <p class="error" id="Error"></p>

  <input type="button" value="Новый комплекс назначений" onclick=NewSet()  id="NewSet">

  <table class="table" width="100%">
    <thead>
    </thead>
    <tbody id="Sets">
    </tbody>
  </table>

</div>

</body>

</html>
