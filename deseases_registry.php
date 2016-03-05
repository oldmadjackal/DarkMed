<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Deseases_registry.php" ;

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
//--------------------------- Формирование списка заболеваний

                     $sql="Select name, grp, code, id".
                          "  from (".
                          "        Select ''name, d3.name grp, '0'code, d3.id".
                          "          From deseases_registry d3".
                          "         Where d3.type =  '0'".
                          "        union all".
                          "        Select d1.name, d2.name grp, d1.type code, d1.id".
                          "          From deseases_registry d1, deseases_registry d2".
                          "         Where d1.type = d2.id".
                          "        )list".
                          " Order by grp, name" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DESEASES_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра заболеваний") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "Deseases registry is empty") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "    dss_name ='".$fields[0]."' ;	\n" ;
       echo "    dss_group='".$fields[1]."' ;	\n" ;
       echo "    dss_gcode='".$fields[2]."' ;	\n" ;
       echo "    dss_id   ='".$fields[3]."' ;	\n" ;

       if($read_only)       
              echo "  AddNewRow(".$i.", dss_name, dss_group, dss_gcode, dss_id, 0) ;	\n" ;
       else   echo "  AddNewRow(".$i.", dss_name, dss_group, dss_gcode, dss_id, 1) ;	\n" ;
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

    echo  "i_error.style.color='red' ;		\r\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\r\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color='green' ;	\r\n" ;
    echo  "i_error.innerHTML  ='Выполнено.' ;	\r\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Messages Deseases Registry</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_error ;
    var  gshow_start ;
    var  gshow_end ;

  function FirstField() 
  {
    var  msg_text ;

       i_error   =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function AddNewRow(p_num, p_name, p_group, p_gcode, p_id, p_edit)
  {
     var  i_deseases ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_edt_new ;

       i_deseases= document.getElementById("Deseases") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;
       i_row_new . id        =  p_num ;

   if(p_gcode!=0)
       i_row_new . hidden    = true ;

       i_col_new = document.createElement("td") ;
   if(p_gcode==0)
   {
       i_col_new . className = "tableG" ;
       i_txt_new = document.createTextNode(p_group) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . onclick= function(e) { ShowGroup(this.parentNode.id) ; } ;
   }
   else
   {
       i_col_new . className = "tableL" ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="desease_details_any.php" ;
						parent.frames["details"].location.assign(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Id="+p_id) ;
					} ;
   } 
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Полностью" ;
       i_shw_new . id     ="Details_"+p_id ;
       i_shw_new . onclick= function(e) {  window.open("desease_view.php?Id="+p_id) ;  } ;
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
									      v_form="desease_edit.php" ;
						          location.assign(v_form+"?Session="+v_session+"&Id="+p_id) ;
					} ;
       i_col_new . appendChild(i_edt_new) ;
   }
       i_row_new . appendChild(i_col_new) ;
       i_deseases. appendChild(i_row_new) ;

    return ;         
  } 

  function SetReadOnly() 
  {
    var  i_new ;

       i_new=document.getElementById("NewDesease") ;

       i_new.disabled=true ;
  }

  function NewDesease() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("desease_edit.php"+"?Session="+v_session) ;
  } 

  function ShowGroup(id_from) 
  {
    var  i_row ;

                    id_from++ ;

     for(i=gshow_start ; i<=gshow_end ; i++) 
     {
           i_row=document.getElementById(i) ;
        if(i_row       ==null )  break ;

           i_row.hidden=true ;
     }

   if(id_from==gshow_start)  {  gshow_start="" ;  return ;  }

        gshow_start=id_from ; 

     for(i=id_from ; ; i++) 
     {
           i_row=document.getElementById(i) ;
        if(i_row       ==null )  break ;
        if(i_row.hidden==false)  break ;

           i_row.hidden=false ;
              gshow_end=i ; 
     }
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
        <b>РЕЕСТР ЗАБОЛЕВАНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p class="error" id="Error"></p>

  <input type="button" value="Добавить заболевание в реестр" onclick=NewDesease()  id="NewDesease">
  <br>
  <br>
  <div>Кликнете по группе для просмотра ее состава</div>

  <table width="100%">
    <thead>
    </thead>
    <tbody id="Deseases">
    </tbody>
  </table>

</div>

</body>

</html>
