<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Callback_list.php" ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД

function ProcessDB() {

  global  $glb_options_a ;

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

      $options="" ;

  if(isset($session) && $session!="")
  {

       $user=DbCheckSession($db, $session, $options, $error) ;
    if($user===false) {
                         $db->close() ;
                       ErrorMsg($error) ;
                         return ;
    }

       $user_=$db->real_escape_string($user ) ;
  }
//--------------------------- Анализ прав пользователя

                                        $readonly=true ;
  if(isset($glb_options_a["support"]))  $readonly=false ;
       
//--------------------------- Формирование списка сообщений

                     $sql="Select id, created, category, message, status, remark".
			  "  From callback_msg".
			  " Order by created desc" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CALLBACK_MSG... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка сообщений") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No messages detected") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

          if($readonly)  $id=   0 ;
          else           $id=$fields[0] ;

       echo "  AddNewMessage($id,'$fields[1]','$fields[2]','$fields[3]','$fields[4]','$fields[5]') ;	\n" ;
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
    echo  "i_error.innerHTML  =\"Доступ к указанным страницам предоставлен.\" ;" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Messages InBox</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_messages ;
    var  i_error ;
    var  password ;

  function FirstField() 
  {
       i_messages=document.getElementById("Messages") ;
       i_error   =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function SendFields() 
  {
         return true ;
  } 

  function AddNewMessage(p_id, p_created, p_category, p_message, p_status, p_remark)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  nl=new RegExp("@@","g") ;


       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_created) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_category) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_col_new . width     = "40%" ;
       i_txt_new = document.createTextNode(p_message) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . innerHTML=i_col_new.innerHTML.replace(nl,"<br>") ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_status) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_txt_new = document.createElement("br") ;
       i_col_new . appendChild(i_txt_new) ;

    if(p_id>0) 
    {
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Ответить" ;
       i_shw_new . id     ="Reply_"+p_id ;
       i_shw_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									                 v_form="callback_details.php" ;
						parent.frames["details"].location.assign(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Message="+p_id) ;
					} ;
       i_col_new . appendChild(i_shw_new) ;
    }
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_col_new . width     = "40%" ;
       i_txt_new = document.createTextNode(p_remark) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . innerHTML=i_col_new.innerHTML.replace(nl,"<br>") ;
       i_row_new . appendChild(i_col_new) ;

       i_messages.appendChild(i_row_new) ;

    return ;         
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
        <b>СООБЩЕНИЯ ПОЛЬЗОВАТЕЛЕЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p class="error" id="Error"></p>
  <table class="table" width="100%">
    <thead>
    </thead>
    <tbody id="Messages">
    </tbody>
  </table>

</div>

</body>

</html>
