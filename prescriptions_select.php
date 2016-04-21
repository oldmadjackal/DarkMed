<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script ="Prescriptions_selector.php" ;
   $glb_log_off=true ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД

function ProcessDB() {

  global  $glb_options_a  ;

  global  $sys_deseases   ;

  global  $sys_dss_count  ;
  global  $sys_dss_name   ;
  global  $sys_dss_group  ;
  global  $sys_dss_gcode  ;
  global  $sys_dss_id     ;

  global  $sys_prs_count  ;
  global  $sys_prs_id     ;
  global  $sys_prs_type   ;
  global  $sys_prs_icon   ;
  global  $sys_prs_name   ;
  
//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение параметров

   if(isset($_POST["Deseases"]))  $deseases=$_POST["Deseases"] ;
   else                           $deseases=$_GET ["Deseases"] ;
                        
   if(isset($_POST["Type"    ]))  $type    =$_POST["Type"] ;
   else                           $type    =   "dummy" ;

   if(isset($_POST["Selected"]))  $selected=$_POST["Selected"] ;
   else                         
   if(isset($_GET ["Selected"]))  $selected=$_GET ["Selected"] ;
   
   if(isset($_POST["Exclude" ]))  $exclude =$_POST["Exclude"] ;
   if(isset($_POST["Common"  ]))  $common  =$_POST["Common"] ;

      FileLog("START", "    Type:".$type) ;
      FileLog("",      "Deseases:".$deseases) ;

  if(isset($selected))
      FileLog("",      "Selected:".$selected) ;

  if(isset($common ))
      FileLog("",      "  Common:".$common) ;

  if(isset($exclude))
      FileLog("",      " Exclude:".$exclude) ;

        $sys_deseases=$deseases ;

//--------------------------- Умолчания

  if(!isset($selected))  $selected="" ;
  if(!isset($common  ))  $common  ="false" ;
  if(!isset($exclude ))  $exclude ="false" ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
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
       echo "   a_types['dummy']='-- Все категории --' ;\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_types['".$fields[0]."']='".$fields[1]."' ;\n" ;
     }
  }

     $res->close() ;

      echo     "  SetType('".$type."') ;\n" ;

//--------------------------- Формирование списка всех заболеваний

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
        $sys_dss_count=$res->num_rows ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

         $sys_dss_name [$i]=$fields[0] ;
         $sys_dss_group[$i]=$fields[1] ;
         $sys_dss_gcode[$i]=$fields[2] ;
         $sys_dss_id   [$i]=$fields[3] ;
     }
  }

     $res->close() ;

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
       echo "    SetDeseaseSelection(true, dss_id, dss_name, dss_group, dss_gcode, 0) ;	\n" ;
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

                     $sql ="Select id, p.type, t.icon, p.name".
			   "  From prescriptions_registry p ".
                           "       inner join ref_prescriptions_types t on t.code=p.type and t.language='RU'".
                           " Where 1=1" ; 

  if($type!="dummy") $sql.="  and  p.type='$type'" ; 

  if($deseases!="" || $common=="true")
  {
                                 $sql.="  and (  1=0 \r\n" ;

    if($common=="true")          $sql.="       or deseases='' " ;

    if($deseases!="")
      foreach($a_dss as $value)  $sql.="       or instr(concat(' ', deseases, ' '), ' ".$value." ')>0 \r\n" ;
                                 $sql.="      )\r\n" ;
  }
                     $sql.=" Order by t.name, p.name" ;

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
        $sys_prs_count=$res->num_rows ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

           $sys_prs_id  [$i]=$fields[0] ;
           $sys_prs_type[$i]=$fields[1] ;
           $sys_prs_icon[$i]=$fields[2] ;
           $sys_prs_name[$i]=$fields[3] ;      
     }
  }

     $res->close() ;
  }
//--------------------------- Извлечение списка заболеваний

                        echo  "i_selected.value='".$selected."' ;	\n" ;
  if($exclude=="true")  echo  "i_exclude .checked=true ;		\n" ;
  
//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение списка назначений

function ShowPrescriptions() {

  global  $sys_prs_count  ;
  global  $sys_prs_id     ;
  global  $sys_prs_type   ;
  global  $sys_prs_icon   ;
  global  $sys_prs_name   ;


  for($i=0 ; $i<$sys_prs_count ; $i++)
  {
        $row=$i ;

     if($i%2==1)  $class="TableRowLBrawn_LC" ;   
     else         $class="TableRowLGreen_LC" ;
     
       echo  "<tr class='".$class."' id='Row_".$sys_prs_id[$i]."'> \n" ;
       echo  " <td> \n" ;
       echo  "  <input type='button' class='AddButton' value='<<' onclick=UsePrescription('".$sys_prs_id[$i]."')> \n" ;
       echo  " </td> \n" ;
       echo  " <td> \n" ;
       echo  "   <img src='".$sys_prs_icon[$i]."' height=30> \n" ; 
       echo  "   <div id='Type_".$sys_prs_id[$i]."' hidden>".$sys_prs_type[$i]."</div>\n" ;
       echo  " </td> \n" ;
       echo  " <td class='".$class."' id='".$sys_prs_id[$i]."'> \n" ;
       echo  htmlspecialchars(stripslashes($sys_prs_name[$i]), ENT_COMPAT, "windows-1251") ;
       echo  " </td> \n" ;
       echo  " <td> \n" ;
       echo  "  <input type='button' class='DetailsButton' value='?' onclick=GoToView('".$sys_prs_id[$i]."')>	\n" ;
       echo  " </td> \n" ;
       echo  "</tr> \n" ;
  }

}

//============================================== 
//  Отображение списка заболеваний

function ShowDeseases() {

  global  $sys_deseases  ;
  global  $sys_dss_count  ;
  global  $sys_dss_name   ;
  global  $sys_dss_group  ;
  global  $sys_dss_gcode  ;
  global  $sys_dss_id     ;


    $sys_deseases=" ".$sys_deseases." " ;

  for($i=0 ; $i<$sys_dss_count ; $i++)
  {

    if($sys_dss_gcode[$i]==0) 
    {
                     $text    =$sys_dss_group[$i] ;
                     $onclick ="ShowGroup(this.parentNode.id) ; " ;
                     $class   ="TableDeseasesGroup" ;
                     $disabled="" ;
    }
    else
    {
                     $text    =$sys_dss_name[$i] ;
                     $onclick ="window.open('desease_view.php?Id=".$sys_dss_id[$i]."'); " ;
                     $class   ="TableDeseaseItem" ;
                     $disabled="" ;
    }

    if(strpos($sys_deseases, " ".$sys_dss_id[$i]." ")===false)  $checked="" ;
    else                                                        $checked="checked" ;

    if($sys_dss_gcode[$i]!=0 && $checked=="")  $hidden="hidden" ;
    else                                       $hidden="" ;
   
       echo  "<tr class='".$class."' id='".$i."' ".$hidden."> \n" ;
       echo  " <td width='1%'> \n" ;
       echo  "  <input type='checkbox' id='Check_".$i."' class='".$class."' value='".$sys_dss_id[$i]."' ".$checked." ".$disabled ;
       echo        " onclick=\"DeseaseSet(this, '".$sys_dss_id[$i]."', '".$sys_dss_name[$i]."', '".$sys_dss_group[$i]."', '".$sys_dss_gcode[$i]."');\"> \n" ;
       echo  " </td> \n" ;
       echo  " <td onclick=\"".$onclick."\"> \n" ;
       echo   $text ;
       echo  " </td> \n" ;
       echo  "</tr> \n" ;
  }

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
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Messages Prescriptions Selector</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script type="text/javascript">
<!--

    var  i_type ;
    var  i_common ;
    var  i_deseases ;
    var  i_exclude ;
    var  i_selected ;
    var  i_error ;

    var  a_types ;

    var  gshow_start ;
    var  gshow_end ;


  function FirstField() 
  {

	i_type    =document.getElementById("Type") ;
	i_common  =document.getElementById("Common") ;
	i_deseases=document.getElementById("Deseases") ;
	i_exclude =document.getElementById("Exclude") ;
	i_selected=document.getElementById("Selected") ;
        i_error   =document.getElementById("Error") ;

           a_types=new Array() ;

<?php
            ProcessDB() ;
?>

          HideSelected() ;

         return true ;
  }
  
  function SendFields() 
  {
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
       i_row_new . id        =  v_id ;

       i_col_new = document.createElement("td") ;

   if(p_gcode==0)
   {
       i_col_new . className = "TableDeseasesGroup" ;
       i_txt_new = document.createTextNode(p_group) ;
       i_col_new . appendChild(i_txt_new) ;
   }
   else
   {
       i_col_new . className = "TableDeseaseItem" ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
   } 
       i_col_new . onclick= function(e) {  window.open("desease_view.php?Id="+p_id) ;  } ;
       i_row_new . appendChild(i_col_new) ;


   if(p_before!=null)  i_before=document.getElementById("Desease_"+p_before) ;
   else                i_before= null ;

       i_dss_list.insertBefore(i_row_new, i_before) ;

    return ;         
  } 

  function DeseaseSet(p_this, p_id, p_name, p_group, p_gcode) 
  {
    var  i_from ;
    var  i_chk ;
    var  id_before ;


         id_before=null ;

          i_from=p_this.id.replace("Check_", "") ;
          i_from++ ;

   if(p_this.checked) 
   {
     for(i=i_from ; ; i++) 
     {
           i_chk=document.getElementById("Check_"+i) ;
        if(i_chk          ==  null  )     break ;
        if(i_chk.checked  ==  true  ) {  id_before=i_chk.value ;  break ;  }
     }
   }

            SetDeseaseSelection(p_this.checked, p_id, p_name, p_group, p_gcode, id_before) ;

   if(p_this.className=="TableDeseasesGroup")
   {
     for(i=i_from ; ; i++) 
     {
           i_chk=document.getElementById("Check_"+i) ;
        if(i_chk          ==  null              )  break ;
        if(i_chk.className=="TableDeseasesGroup")  break ;

        if(p_this.checked)
        { 
          if(i_chk.checked==true)
                SetDeseaseSelection(false, i_chk.value, null, null, null, null) ;

             i_chk.checked =false ;
             i_chk.disabled=true ;
        }
        else
        {
             i_chk.disabled=false ;
        }
     }
   }

  }

  function UsePrescription(p_id) 
  {

    if(i_selected.value=="")  i_selected.value =p_id ;
    else                      i_selected.value+=','+p_id ;

    if(i_exclude.checked)  document.getElementById('Row_'+p_id).hidden=true ;
   
    v_type=document.getElementById("Type_"+p_id).innerHTML ;
    v_name=document.getElementById(        p_id).innerHTML ;

     parent.frames['section'].AddSelectedRow(p_id, v_type, v_name) ;
  }

  function HideSelected() 
  {
     var  elems ;
     var  i_row ;
          
     elems=i_selected.value.split(",") ;
  
    for(var i=0 ; i<elems.length ; i++) {
          i_row=document.getElementById('Row_'+elems[i]) ;
       if(i_row!=null)  i_row.hidden=i_exclude.checked ;
    }
  }
  
  function GoToView(p_id) 
  {
    window.open("prescription_view.php?Id="+p_id) ;
  }

  function LinkDesease() 
  {
     document.getElementById("Column1"       ).hidden=true ;
     document.getElementById("SelectDeseases").hidden=false ;

          i_error.innerHTML ="" ;
  } 

  function CallBack() 
  {
     document.getElementById("SelectDeseases").hidden=true ;
     document.getElementById("Column1"       ).hidden=false ;
  } 

  function ShowGroup(id_from) 
  {
    var  i_row ;
    var  i_checkbox ;


                    id_from++ ;

   if(gshow_start!="none")
     for(i=gshow_start ; i<=gshow_end ; i++) 
     {
           i_row=document.getElementById(i) ;
        if(i_row       ==null )  break ;

           i_checkbox=document.getElementById("Check_"+i) ;
        if(i_checkbox.checked==false)  i_row.hidden=true ;
     }

   if(id_from==gshow_start)  {  gshow_start="none" ;  return ;  }

        gshow_start="none" ;

     for(i=id_from ; ; i++) 
     {
           i_row=document.getElementById(i) ;
        if(i_row          ==  null              )  break ;
        if(i_row.className=="TableDeseasesGroup")  break ;

            gshow_start=id_from ; 
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

  <form onsubmit="return SendFields();" method="POST">

  <table width="100%">
    <tbody>
    <tr>
      <td id="Column1"> 
    
  <table class="Normal_CT" width="100%">
    <thead>
    <tr>
      <td>
         <select name="Type" id="Type" placeholder="Выбирете категорию" > 
         </select> 
      </td>
    </tr>
    <tr>
      <td>
        <input type="checkbox" name="Common" id="Common" value="true">Не отнесенные к каким-либо заболеваниям
        <table width="100%">
          <tbody  id="Deseases_list">
          </tbody>
        </table>
        <input type="button" value="Добавить/удалить заболевания" onclick=LinkDesease()>
        <input type="hidden" name="Deseases" id="Deseases">
      </td>
    </tr>
    <tr>
      <td>
        <input type="submit" class="GreenButton" value="Отобрать назначения">
      </td>
    </tr>
    <tr>
      <td>
        <input type="checkbox" name="Exclude" id="Exclude" value="true" onclick="HideSelected();">Не показывать уже отобранные назначения
        <input type="hidden" name="Selected" id="Selected">
      </td>
    </tr>
    <tr>
      <td>
        <div class="Error_CT " id="Error"></div>
      </td>
    </tr>

    </tbody>
  </table>

  </form>

  <table>
    <thead>
    </thead>
    <tbody id="Prescriptions">

<?php
            ShowPrescriptions() ;
?>

    
    </tbody>
  </table>

      <td id="SelectDeseases" hidden>
        <input type="button" value="Обратно" onclick=CallBack()>
        <table width="100%">
          <tbody id="AllDeseases">
<?php
            ShowDeseases() ;
?>
          </tbody>
        </table>
      </td>
    </tr>
  </table>

</body>

</html>
