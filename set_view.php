<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Set_view.php" ;

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
                         $get_id=$_GET ["Id"] ;

    FileLog("START", "    Session:".$session) ;
    FileLog("",      "     Get_Id:".$get_id) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
  }

          $session_=$db->real_escape_string($session) ;
          $user_   =$db->real_escape_string($user) ;

          $get_id_=$db->real_escape_string($get_id) ;

//--------------------------- Извлечение данных комплекса

                       $sql="Select id, user, name, description, deseases".
                            "  From  sets_registry".
                            " Where  id='$get_id_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select * from SETS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $put_id     =$fields[0] ;
                   $owner      =$fields[1] ;
                   $name       =$fields[2] ;
                   $description=$fields[3] ;
                   $deseases   =$fields[4] ;

        FileLog("", "Set data selected successfully") ;

     if($user!=$owner) {
            ErrorMsg("Просмотр комплекса назначений разрешен только владельцу.") ;
                         return ;
     }
//--------------------------- Извлечение состава комплекса

      echo     "  i_count.value='0'	;\n" ;

          $put_id_=$db->real_escape_string($put_id) ;

                     $sql="Select e.prescription_id, t.name, r.name, e.remark".
			  "  From sets_elements e left outer join prescriptions_registry r on e.prescription_id=r.id".
                          "       left outer join ref_prescriptions_types t on t.code=r.type and t.language='RU'".
                          " Where e.set_id=$put_id_".
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

       echo "   AddListRow('".$fields[0]."', '".$fields[1]."', '".$fields[2]."', '".$fields[3]."') ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение списка связанных заболеваний

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

       echo "  AddDesease(dss_id, dss_name, dss_group, dss_gcode) ;	\n" ;
    }

     $res->close() ;
  }
//--------------------------- Вывод данных на страницу

      echo     "  i_name       .innerHTML='".$name       ."' ;\n" ;
      echo     "  i_description.innerHTML='".$description."' ;\n" ;

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

<title>DarkMed Prescription Set Card</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_table ;
    var  i_count ;
    var  i_name ;
    var  i_description ;
    var  i_error ;


  function FirstField() 
  {
     var  i_category ;
     var  nl=new RegExp("@@","g") ;

	i_table      =document.getElementById("Fields") ;
	i_count      =document.getElementById("Count") ;
	i_name       =document.getElementById("Name") ;
	i_description=document.getElementById("Description") ;
	i_error      =document.getElementById("Error") ;


<?php
            ProcessDB() ;
?>

       i_description.innerHTML=i_description.innerHTML.replace(nl,"<br>") ;

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

  function AddListRow(p_id, p_category, p_name, p_remark)
  {
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  num_new ;


       num_new=parseInt(i_count.value)+1 ;
                        i_count.value=num_new ;

       i_set     = document.getElementById("Prescriptions") ;
       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_txt_new = document.createTextNode(num_new) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_txt_new = document.createTextNode(p_category) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
  if(p_id!='0')
       i_txt_new = document.createTextNode(p_name) ;
  else i_txt_new = document.createTextNode(p_remark) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
  if(p_id!='0') {
       i_txt_new = document.createTextNode(p_remark) ;
       i_col_new . appendChild(i_txt_new) ;
                }
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_col_new . width     = "3%" ;
  if(p_id!='0') {
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Подробнее" ;
       i_shw_new . id     ='Details_'+ num_new ;
       i_shw_new . onclick= function(e) {  ShowDetails(p_id) ;  }
       i_col_new . appendChild(i_shw_new) ;
                }
       i_row_new . appendChild(i_col_new) ;

       i_set     . appendChild(i_row_new) ;

    return ;         
  } 

  function ShowDetails(p_id)
  {
    window.open("prescription_view.php?Id="+p_id) ;
  }

  function AddDesease(p_id, p_name, p_group, p_gcode)
  {
     var  i_dss_list ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  v_id ;


                   v_id="Desease_"+p_id ;

       i_dss_list= document.getElementById("Deseases_list") ;

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

       i_dss_list. appendChild(i_row_new) ;

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
        <b>КАРТОЧКА КОМПЛЕКСА НАЗНАЧЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">

  <table width="100%" >
    <thead>
    </thead>
    <tbody>
    <tr>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
    <td class="table">

  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> <b>Название</b> </td>
      <td> <dev name="Name" id="Name"><dev></td>
      <td> <input type="hidden" name="Count" id="Count"> </td>
    </tr>
    <tr>
      <td class="field"> <b>Описание</b> </td>
      <td> 
        <dev name="Description" id="Description"> </dev>
      </td>
    </tr>
    </tbody>
  </table>

      </td>
      <td class="table">
        <table width="100%">
          <thead>
          </thead>
          <tbody  id="Deseases_list">
          </tbody>
        </table>
      </td>
    </tr>
    </tbody>
  </table>

  <br>
  <table width="100%">
    <thead>
      <tr>
        <td class="fieldC"><b>N         </b></td>
        <td               ><b>Категория </b></td>
        <td               ><b>Название  </b></td>
        <td               ><b>Примечание</b></td>
      </tr>
    </thead>
    <tbody  id="Prescriptions">
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
