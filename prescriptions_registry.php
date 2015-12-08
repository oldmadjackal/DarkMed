<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescriptions_registry.php" ;

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
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
    }
  }

       if(strpos($options, "UserType=Doctor;"  )!==false)  $read_only=false ;
  else if(strpos($options, "UserType=Executor;")!==false)  $read_only=false ;
  else                                                     $read_only=true ;

//------------------------ Проверка "подтвержденности" врача

  if($read_only==false) {

                $user_=$db->real_escape_string($user) ;

       $res=$db->query("Select confirmed". 
                       "  from doctor_page_main".
                       " Where owner='$user_'".
                       "  and  confirmed='Y'") ;
    if($res===false) {
            FileLog("ERROR", "DB query(Select CONFIRMED...) : ".$db->error) ;
                              $db->close() ;
              $error="Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка идентификации прав доктора" ;
                           return(false) ;
    }

    if($res->num_rows==0)  $read_only=true ;

               $res->close() ;
  }
//--------------------------- Формирование списка назначений

                     $sql="Select id, t.name, p.name".
			  "  From prescriptions_registry p ".
                          "       inner join ref_prescriptions_types t on t.code=p.type and t.language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра назначений") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "Prescriptions registry is empty") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "    prs_id  ='".$fields[0]."' ;				\n" ;
       echo "    prs_type='".$fields[1]."' ;				\n" ;
       echo "    prs_text='".$fields[2]."' ;				\n" ;

       if($read_only)       
              echo "  AddNewRow(prs_id, prs_type, prs_text, 0) ;	\n" ;
       else   echo "  AddNewRow(prs_id, prs_type, prs_text, 1) ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Обработка специальных режимов

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

<title>DarkMed Messages Prescriptions Registry</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

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

  function AddNewRow(p_id, p_type, p_text, p_edit)
  {
     var  i_prescr ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_edt_new ;

       i_prescr  = document.getElementById("Prescriptions") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_id) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_type) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_text) ;
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
									      v_form="prescription_details_any.php" ;
						parent.frames["details"].location.assign(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Id="+p_id) ;
					} ;
       i_col_new . appendChild(i_shw_new) ;

   if(p_edit)
   {
       i_edt_new = document.createElement("input") ;
       i_edt_new . type   ="button" ;
       i_edt_new . value  ="Править" ;
       i_edt_new . id     ="Edit_"+p_id ;
       i_edt_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="prescription_edit.php" ;
 						          location.assign(v_form+"?Session="+v_session+
                                                                                 "&Id="+p_id) ;
					} ;
       i_col_new . appendChild(i_edt_new) ;
   }
       i_row_new . appendChild(i_col_new) ;
       i_prescr  .appendChild(i_row_new) ;

    return ;         
  } 

  function SetReadOnly() 
  {
    var  i_new ;

       i_new=document.getElementById("NewPrescription") ;

       i_new.disabled=true ;
  }

  function NewPrescription() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("prescription_edit.php"+"?Session="+v_session) ;
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
        <b>ОБЩИЙ РЕГИСТР НАЗНАЧЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p class="error" id="Error"></p>

  <input type="button" value="Добавить назначение в регистр" onclick=NewPrescription()  id="NewPrescription">

  <table class="table" width="100%">
    <thead>
    </thead>
    <tbody id="Prescriptions">
    </tbody>
  </table>

</div>

</body>

</html>
