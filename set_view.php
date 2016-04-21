<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script ="Set_view.php" ;
   $glb_log_off= true ;

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

                                 $session   =$_GET["Session"] ;
                                 $get_id    =$_GET["Id"] ;
  if(isset($_GET["ShortForm"]))  $short_form=$_GET["ShortForm"] ;
  if(isset($_GET["Select"   ]))  $select    =$_GET["Select"] ;
                         
       FileLog("START", "    Session:".$session) ;
       FileLog("",      "     Get_Id:".$get_id) ;

  if(isset($short_form))
       FileLog("",      "  ShortForm:".$short_form) ;

  if(isset($select))
       FileLog("",      "     Select:".$select) ;

//--------------------------- Умолчания

  if(!isset($short_form))  $short_form="false" ;
  if(!isset($select    ))  $select    ="false" ;

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

          $put_id_=$db->real_escape_string($put_id) ;

                     $sql="Select e.prescription_id, r.type, t.name, r.name, e.remark".
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

       echo "   a_plist_id    [".($i+1)."]='".$fields[0]."' ;	\n" ;
       echo "   a_plist_type  [".($i+1)."]='".$fields[1]."' ;	\n" ;
       echo "   a_plist_tname [".($i+1)."]='".$fields[2]."' ;	\n" ;
       echo "   a_plist_name  [".($i+1)."]='".$fields[3]."' ;	\n" ;
       echo "   a_plist_remark[".($i+1)."]='".$fields[4]."' ;	\n" ;
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

      echo     "    short_form           = ".$short_form ." ; \n" ;
      echo     "    select               = ".$select     ." ; \n" ;
      echo     "  i_name       .innerHTML='".$name       ."' ; \n" ;
      echo     "  i_description.innerHTML='".$description."' ; \n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;       \n" ;
    echo  "i_error.innerHTML  ='".$text."' ; \n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;                     \n" ;
    echo  "i_error.innerHTML  ='Данные успешно сохранены!' ; \n" ;
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
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script type="text/javascript">
<!--

    var  i_name ;
    var  i_description ;
    var  i_pres_list ;
    var  i_pres_title ;
    var  i_set ;
    var  i_error ;

    var  a_plist_id ;
    var  a_plist_type ;
    var  a_plist_tname ;
    var  a_plist_name ;
    var  a_plist_remark ;

    var  short_form=false ;
    var  select    =false ;


  function FirstField() 
  {
     var  i_category ;
     var  nl=new RegExp("@@","g") ;

	i_name       =document.getElementById("Name") ;
	i_description=document.getElementById("Description") ;
        i_set        =document.getElementById("Prescriptions") ;
        i_pres_list  =document.getElementById("List") ;
        i_pres_tiles =document.getElementById("Tiles") ;
	i_error      =document.getElementById("Error") ;

	a_plist_id    =new Array() ;
	a_plist_type  =new Array() ;
	a_plist_tname =new Array() ;
	a_plist_name  =new Array() ;
	a_plist_remark=new Array() ;

<?php
            ProcessDB() ;
?>

       i_description.innerHTML=i_description.innerHTML.replace(nl,"<br>") ;

    if(select)  document.getElementById("Select").hidden=false ;
            
        ShowPrescriptions() ;

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
   
  function ShowPrescriptions() 
  {
       
    if(i_pres_tiles.checked==true)  presentation="Tiles" ;
    else	                    presentation="List" ;

       for(i in a_plist_id) {
      	    i_row=document.getElementById("Row_"+a_plist_id[i]) ;
  	 if(i_row!=null)  i_set.removeChild(i_row) ;
       }

       for(i in a_plist_id)
         if(presentation=="Tiles")
                  AddListRow_tiles(i, a_plist_id[i], a_plist_type[i], a_plist_tname[i], a_plist_name[i], a_plist_remark[i]) ;
         else     AddListRow_list (i, a_plist_id[i], a_plist_type[i], a_plist_tname[i], a_plist_name[i], a_plist_remark[i]) ;

                  AddListRow_tiles(0) ;
  }

  function AddListRow_list(p_order, p_id, p_type, p_tname, p_name, p_remark)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  v_style ;


     if(p_order==0)  return ;

     if(p_type=="measurement")  v_style="TableMeasurement_LT" ; 
     else                       v_style="Table_LC" ;

                  i_row_new = document.createElement("tr") ;
		  i_row_new . id        = "Row_"+p_id ;

                  i_col_new = document.createElement("td") ;
                  i_col_new . className = v_style ;
                  i_txt_new = document.createTextNode(p_order) ;
                  i_col_new . appendChild(i_txt_new) ;
                  i_row_new . appendChild(i_col_new) ;

 if(short_form==false)
 {
                  i_col_new = document.createElement("td") ;
                  i_col_new . className = v_style ;
                  i_txt_new = document.createTextNode(p_tname) ;
                  i_col_new . appendChild(i_txt_new) ;
                  i_row_new . appendChild(i_col_new) ;
 }

                  i_col_new = document.createElement("td") ;
                  i_col_new . className = v_style ;
		  i_col_new . id = p_id ;
  if(p_id!="0")   i_col_new . onclick  = function(e) {  ShowDetails(this.id) ;  } ;
  if(p_id!='0')   i_txt_new = document.createTextNode(p_name) ;
  else            i_txt_new = document.createTextNode(p_remark) ;
                  i_col_new . appendChild(i_txt_new) ;
                  i_row_new . appendChild(i_col_new) ;

                  i_col_new = document.createElement("td") ;
                  i_col_new . className = v_style ;
  if(p_id!='0') {
                  i_txt_new = document.createTextNode(p_remark) ;
                  i_col_new . appendChild(i_txt_new) ;
                }
                  i_row_new . appendChild(i_col_new) ;

                  i_set     . appendChild(i_row_new) ;

    return ;         
  } 

     var  s_order =new Array(3) ;
     var  s_id    =new Array(3)   ;
     var  s_type  =new Array(3) ;
     var  s_name  =new Array(3) ;
     var  s_remark=new Array(3) ;
     var  s_num   = 0 ;

  function AddListRow_tiles(p_order, p_id, p_type, p_tname, p_name, p_remark)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_elm_new ;
     var  i_frm_new ;
     var  i_shw_new ;
     var  i_msr_new ;
     var  i_msr_list ;
     var  v_style ;
     var  v_id ;
     var  msr_flag ;
     var  col ;


     if(p_order  ==0) {
			if(s_num==0)  return ;	
                      }
     else             {
			  s_order [s_num]=p_order ;
			  s_id    [s_num]=p_id  ;
			  s_type  [s_num]=p_type  ;
			  s_name  [s_num]=p_name ;
			  s_remark[s_num]=p_remark ;
			           s_num++  ;

                  if(short_form   ==false &&
                         p_order%3!=   0    )  return ;
                      }

		  i_row_new = document.createElement("tr") ;
		  i_row_new . id        = "Row_"+s_id[0] ;

   for(col=0 ; col<s_num ; col++)
   {
              v_id=s_id[col] ;

     if(s_type[col]=="measurement") {  msr_flag=true ;
                                        v_style="TableMeasurement_LT" ;  }
     else                           {  msr_flag=false ;
                                        v_style="Table_LC" ;             }

		  i_col_new = document.createElement("td") ;
		  i_col_new . className = v_style ;
		  i_txt_new = document.createTextNode(s_order[col]) ;
		  i_col_new . appendChild(i_txt_new) ;
		  i_row_new . appendChild(i_col_new) ;

		  i_col_new = document.createElement("td") ;
		  i_col_new . className = v_style ;
		  i_col_new . id = s_id[col] ;
  if(v_id!="0")   i_col_new . onclick  = function(e) {  ShowDetails(this.id) ;  } ;

  if(v_id!="0") {
                  i_fld_new = document.createElement("div") ;
                  i_fld_new . className = "Bold_LT" ;
                  i_txt_new = document.createTextNode(s_name[col]) ;
                  i_fld_new . appendChild(i_txt_new) ;
		  i_col_new . appendChild(i_fld_new) ;
                }
  else		{
                  i_txt_new = document.createTextNode(s_remark[col]) ;
		  i_col_new . appendChild(i_txt_new) ;
                }

  if(v_id!="0") {
		  i_elm_new = document.createElement("br") ;
		  i_col_new . appendChild(i_elm_new) ;
		  i_txt_new = document.createTextNode(s_remark[col]) ;
		  i_col_new . appendChild(i_txt_new) ;
		}

		  i_row_new . appendChild(i_col_new) ;

		  i_col_new = document.createElement("td") ;
		  i_col_new . className = v_style ;
  if(msr_flag ) {
		}
  else
  if(v_id!="0") {
		  i_frm_new = document.createElement("iframe") ;
		  i_frm_new . src         ="prescription_pilot.php?Id="+s_id[col] ;
		  i_frm_new . seamless    = true ;
		  i_frm_new . height      ="202" ;
		  i_frm_new . scrolling   ="no" ;
		  i_frm_new . frameborder ="0" ;
		  i_frm_new . marginheight="0" ;
		  i_frm_new . marginwidth ="0" ;
		  i_col_new . appendChild(i_frm_new) ;
		}

		  i_row_new . appendChild(i_col_new) ;

  if(col!=s_num-1) {

		  i_col_new = document.createElement("td") ;
		  i_col_new . className = "TableGap" ;
		  i_col_new . width     = "1%" ;
		  i_row_new . appendChild(i_col_new) ;

		   }

   }

		  i_set.appendChild(i_row_new) ;

		  s_num=0 ;

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
       i_col_new . className = "TableDeseaseItem " ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
   } 
       i_col_new . onclick= function(e) {  window.open("desease_details_any.php?Id="+p_id) ;  }

       i_row_new . appendChild(i_col_new) ;

       i_dss_list. appendChild(i_row_new) ;

    return ;         
  } 

  function ExtReturn()
  {
      parent.frames['section'].ExtCallBack() ;
  }

  function ExtSelect()
  {
     for(i in a_plist_id) {
          parent.frames['section'].AddListRow(a_plist_id[i], a_plist_type[i], a_plist_name[i], a_plist_remark[i], "", 0) ;
                          }
                          
      parent.frames['section'].ExtCallBack() ;
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

  <table width="90%">
    <tbody>
    <tr>
      <td width="10%"> 
        <input type="button" class="HelpButton"     value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" class="CallBackButton" value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="FormTitle"> 
        <b>КАРТОЧКА КОМПЛЕКСА НАЗНАЧЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <table>
    <tbody>
    <tr>
      <td> <div class="Error_CT" id="Error"></div> </td>
    </tr>
    <tr id="Select" hidden>
      <td class="Normal_CT">
        <input type="button" class="GreenButton" value="Выбрать"   onclick=ExtSelect()> 
        <input type="button"                     value="Вернуться" onclick=ExtReturn()> 
      </td>
    </tr>
    <tr>
    <td class="table">

  <table>
    <tbody>
    <tr>
      <td class="Normal_RT"> <b>Название</b> </td>
      <td> <dev name="Name" id="Name"><dev></td>
      <td> <input type="hidden" name="Count" id="Count"> </td>
    </tr>
    <tr>
      <td class="Normal_RT"> <b>Описание</b> </td>
      <td> 
        <div name="Description" id="Description"> </div>
      </td>
    </tr>
    </tbody>
  </table>

      </td>
    </tr>
    <tr>
      <td class="table">
        <table width="100%">
          <tbody  id="Deseases_list">
          </tbody>
        </table>
      </td>
    </tr>
    </tbody>
  </table>

  <br>
  
  <table>
    <tbody>
      <tr>
        <td width="30%"></td>
        <td>
          <div> <input type="radio" name="Type[]" id="Tiles"          onclick=ShowPrescriptions()>'Плитка' назначений</div>
          <div> <input type="radio" name="Type[]" id="List"  checked  onclick=ShowPrescriptions()>Список назначений</div>
        </td>
    </tbody>
  </table>

  <br>

  <table>
    <tbody  id="Prescriptions">
    </tbody>
  </table>

</body>

</html>
