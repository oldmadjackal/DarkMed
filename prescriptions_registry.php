<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescriptions_registry.php" ;

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

                        $session =$_GET ["Session"] ;
  if(!isset($session))  $session =$_POST["Session"] ;
                        $type    =$_POST["Type"] ;
                        $deseases=$_POST["Deseases"] ;
                        $common  =$_POST["Common"] ;

  FileLog("START", " Session:".$session) ;
  FileLog("",      "    Type:".$type) ;
  FileLog("",      "Deseases:".$deseases) ;
  FileLog("",      "  Common:".$common) ;

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

       if($glb_options_a["user"]=="Doctor"  )  $read_only=false ;
  else if($glb_options_a["user"]=="Executor")  $read_only=false ;
  else                                         $read_only=true ;

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
//--------------------------- Извлечение списка типов назначений

                     $sql="Select code, name".
			  "  From ref_prescriptions_types".
			  " Where language='RU'".
                          "  and  code<>'unregistered'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_PRESCRIPTIONS_TYPES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника типов назначений") ;
                         return ;
  }
  else
  {  
       echo "   a_types['dummy']='' ;\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_types['".$fields[0]."']='".$fields[1]."' ;\n" ;
     }
  }

     $res->close() ;

      echo     "  SetType('".$type."') ;\n" ;

//--------------------------- Извлечение списка заболеваний

                       echo  "i_deseases.value='".$deseases."' ;	\n" ;
  if($common=="true")  echo  "i_common  .checked=true ;			\n" ;

  if($deseases!="")
  {
             $deseases_list=str_replace(" ", ",", $deseases) ;

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
                            " Where id in (".$deseases_list.")".
                            " Order by grp, name" ;
       $res=$db->query($sql) ;
    if($res===false) {
            FileLog("ERROR", "Select DESEASES_REGISTRY... : ".$db->error) ;
                              $db->close() ;
           ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка связанных заболеваний") ;
                           return ;
    }

    for($i=0 ; $i<$res->num_rows ; $i++)
    {
	      $fields=$res->fetch_row() ;

       echo "    dss_name ='".$fields[0]."' ;	\n" ;
       echo "    dss_group='".$fields[1]."' ;	\n" ;
       echo "    dss_gcode='".$fields[2]."' ;	\n" ;
       echo "    dss_id   ='".$fields[3]."' ;	\n" ;

       echo "  SetDeseaseSelection(true, dss_id, dss_name, dss_group, dss_gcode, null) ;	\n" ;
    }

     $res->close() ;
  }
//------------------------- Определение списка заболеваний (раскрытие групп заболеваний)

  if($deseases!="")
  {
                      $list=str_replace(" ", ",", $deseases) ;

                       $sql="Select id".
			    "  From deseases_registry".
			    " Where id in (".$list.") ".
                            " UNION ALL ".
                            "Select type".
			    "  From deseases_registry".
			    " Where id in (".$list.")".
                            "  and  type>0" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select DESEASES_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка формирования фильтра по заболеваниям") ;
                         return ;
    }
    else
    {  
       for($i=0 ; $i<$res->num_rows ; $i++)
       {
	    $fields =$res->fetch_row() ;
	  $a_dss[$i]=$fields[0] ;
       }
    }

	$res->close() ;
  }
//--------------------------- Формирование списка назначений
//- - - - - - - - - - - - - - Проверка наличия фильтров
  if( ! ((isset($type)         &&
                $type!="dummy"   ) ||
         (isset($deseases)     &&
                $deseases!=""    )   )) {

                    InfoMsg("Укажите хотя бы один из фильтров для отбора назначений.") ;
  }
//- - - - - - - - - - - - - - Запрос данных
  else {
                     $sql ="Select id, t.name, p.name".
			   "  From prescriptions_registry p ".
                           "       inner join ref_prescriptions_types t on t.code=p.type and t.language='RU'".
                           " Where 1=1" ; 

  if($type!="dummy") $sql.="  and  p.type='$type'" ; 

  if($deseases!="" || $common=="true")
  {
                                 $sql.="  and (  1=0 \r\n" ;

    if($common=="true")          $sql.="       or deseases='' " ;

      foreach($a_dss as $value)  $sql.="       or instr(concat(' ', deseases, ' '), ' ".$value." ')>0 \r\n" ;
                                 $sql.="      )\r\n" ;
  }
                     $sql.=" Order by t.name, p.name" ;

  FileLog("DEBUG", $sql) ;


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
  }

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

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача информационного сообщения на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color='green' ;	\n" ;
    echo  "i_error.innerHTML  ='Выполнено.' ;	\n" ;
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

    var  i_type ;
    var  i_common ;
    var  i_deseases ;
    var  i_error ;

    var  a_types ;

    var  s_deseases_select_use ;

  function FirstField() 
  {
    var  msg_text ;

	i_type    =document.getElementById("Type") ;
	i_common  =document.getElementById("Common") ;
	i_deseases=document.getElementById("Deseases") ;
        i_error   =document.getElementById("Error") ;

           a_types=new Array() ;

<?php
            ProcessDB() ;
?>
         return true ;
  }

  function GoAway() 
  {
     if(s_deseases_select_use)
             parent.frames["details"].location.replace("start.html") ;
  }

  function SendFields() 
  {
     var  error_text ;

	error_text="" ;
     
     if(    i_type.value       =="dummy" &&
        i_deseases.value.trim()==""        )  error_text="Категория назначения или перечень заболеваний должны быть определены" ;

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

      return true ;
  } 

  function SetType(p_selected)
  {
     var  selected ;

    for(var elem in a_types)
    {
                             selected=false ;
       if(p_selected==elem)  selected=true ;

                      i_type.length++ ;
       i_type.options[i_type.length-1].text    =a_types[elem] ;
       i_type.options[i_type.length-1].value   =        elem ;
       i_type.options[i_type.length-1].selected=    selected ;
    }

    return ;         
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
       i_col_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="prescription_details_any.php" ;
						parent.frames["details"].location.replace(v_form+
                                                                                          "?Session="+v_session+
                                                                                          "&Id="+p_id) ;
					} ;
       i_txt_new = document.createTextNode(p_type) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="prescription_details_any.php" ;
						parent.frames["details"].location.replace(v_form+
                                                                                          "?Session="+v_session+
                                                                                          "&Id="+p_id) ;
					} ;
       i_txt_new = document.createTextNode(p_text) ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Полностью" ;
       i_shw_new . id     ="Details_"+p_id ;
       i_shw_new . onclick= function(e) {window.open("prescription_view.php?Id="+p_id) ;  } ;
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

       i_new=document.getElementById("AddPrescription") ;

       i_new.disabled=true ;
  }

  function NewPrescription() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("prescription_edit.php"+"?Session="+v_session) ;
  } 

  function LinkDesease() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        parent.frames["details"].location.replace("deseases_select.php"+"?Session="+v_session+"&Deseases="+i_deseases.value) ;

        s_deseases_select_use=true ;
  } 

  function SetDeseaseSelection(p_checked, p_id, p_name, p_group, p_gcode, p_before)
  {
     var  i_dss_list ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_before ;
     var  v_id ;


                   v_id="Desease_"+p_id ;
       i_deseases.value=" "+i_deseases.value+" " ;

       i_dss_list= document.getElementById("Deseases_list") ;

   if(p_checked==false)
   {
     i_deseases.value=i_deseases.value.replace(" "+p_id+" ", " ").trim() ;

     i_dss_list.removeChild(document.getElementById(v_id)) ;
         return ;
   }

   if(i_deseases.value.indexOf(" "+p_id+" ")<0)
   {
       i_deseases.value+=p_id ;
   }
       i_deseases.value =i_deseases.value.trim() ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;
       i_row_new . id        =  v_id ;

       i_col_new = document.createElement("td") ;

   if(p_gcode==0)
   {
       i_col_new . className = "tableG" ;
       i_txt_new = document.createTextNode(p_group) ;
       i_col_new . appendChild(i_txt_new) ;
   }
   else
   {
       i_col_new . className = "tableL" ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
   } 
       i_col_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="desease_details_any.php" ;
						parent.frames["details"].location.replace(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Id="+p_id) ;
					} ;
       i_row_new . appendChild(i_col_new) ;


   if(p_before!=null)  i_before=document.getElementById("Desease_"+p_before) ;
   else                i_before= null ;

       i_dss_list.insertBefore(i_row_new, i_before) ;

    return ;         
  } 

<?php
  require("common.inc") ;
?>

//-->
</script>

</head>

<body onload="FirstField();" onunload="GoAway();">

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

  <p class="error" id="Error"></p>

  <form onsubmit="return SendFields();" method="POST">

  <table>
    <thead>
    </thead>
    <tbody>
    <tr>
      <td>
        <input type="submit" value="Показать назначения для:">
      </td>
      <td class="field"> Категория </td>
      <td>
         <select name="Type" id="Type"> 
         </select> 
      </td>
    </tr>
    <tr>
      <td></td>
      <td class="field"> Заболевания </td>
      <td>
        <input type="checkbox" name="Common" id="Common" value="true">Не отнесенные к каким-либо заболеваниям
        <table width="100%">
          <thead>
          </thead>
          <tbody  id="Deseases_list">
          </tbody>
        </table>
        <input type="button" value="Добавить/удалить заболевания" onclick=LinkDesease()>
        <input type="hidden" name="Deseases" id="Deseases">
      </td>
    </tr>
    <tr>
      <td>
        <br> 
        <input type="button" value="Добавить назначение в регистр" onclick="NewPrescription();"  id="AddPrescription">
      </td>
    </tr>
    </tbody>
  </table>

  </form>

  <table class="table">
    <thead>
    </thead>
    <tbody id="Prescriptions">
    </tbody>
  </table>

</div>

</body>

</html>
