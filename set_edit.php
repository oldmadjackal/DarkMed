<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Set_edit.php" ;

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

                         $get_id=$_GET ["Id"] ;
                         $put_id=$_POST["Id"] ;

                         $after_save ="false" ;

  if( isset($put_id )) 
  {
                         $name       =$_POST["Name"] ;
                         $description=$_POST["Description"] ;
                         $deseases   =$_POST["Deseases"] ;
                         $after_save =$_POST["AfterSave"] ;
                         $count      =$_POST["Count"] ;

             $a_prescr=array() ;
             $a_remark=array() ;

           settype($count, "integer") ;
     for($prescr_count=0, $i=1 ; $i<=$count ; $i++) 
     {
 	         $id=$_POST["Id_".$i] ;
        if(isset($id))
        {
             $a_prescr[$prescr_count]=$_POST["Id_"    .$i] ;
             $a_remark[$prescr_count]=$_POST["Remark_".$i] ;
                       $prescr_count++ ;
        }
     }
  }

    FileLog("START", "    Session:".$session) ;

  if( isset($get_id )) 
  {
    FileLog("",      "     Get_Id:".$get_id) ;
  }

  if( isset($put_id )) 
  {
    FileLog("",      "     Put_Id:".$put_id) ;
    FileLog("",      "       Name:".$name) ;
    FileLog("",      "Description:".$description) ;
    FileLog("",      "   Deseases:".$deseases) ;
    FileLog("",      "      Count:".$count) ;

   for($i=0 ; $i<$prescr_count ; $i++) 
    FileLog("",      "Prescription:".$a_prescr[$i]." ".$a_remark[$i]) ;
  }
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

//--------------------------- Создание новой записи

  if(!isset($get_id) &&
     !isset($put_id)   ) 
  {
                       $sql="Insert into sets_registry(user, name)".
                            " Values('$user_', '#$session_#')" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Insert SETS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания комплекса назначений") ;
                         return ;
    }

            $db->commit() ;

                       $sql="Select max(id)".
                            "  From sets_registry".
                            " Where name='#$session_#'" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select id from SETS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка идентификации карточки назначения") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $put_id     =$fields[0] ;
                   $name       ='' ;
                   $description='' ;
                   $deseases   ='' ;

        FileLog("", "New prescription generated successfully") ;
  }
//--------------------------- Извлечение данных для отображения
  else
  if(!isset($put_id))
  {
          $get_id_=$db->real_escape_string($get_id) ;

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
            ErrorMsg("Редактирование комплекса назначений разрешено только владельцу.") ;
                         return ;
     }
  }
//--------------------------- Сохранение данных со страницы
  else
  {
          $put_id_     =$db->real_escape_string($put_id) ;

          $name_       =$db->real_escape_string($name) ;
          $description_=$db->real_escape_string($description) ;
          $deseases_   =$db->real_escape_string($deseases) ;

                       $sql="Update sets_registry".
                            " Set   name       ='$name_'".
                            "      ,description='$description_'".
                            "      ,deseases   ='$deseases_'".
                            " Where id='$put_id_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update SETS_REGISTRY... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

                       $sql="Delete from sets_elements".
                            " Where set_id=$put_id_" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Delete SETS_ELEMENTS... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

   for($i=0 ; $i<$prescr_count ; $i++) 
   {
          $prescr_=                        $a_prescr[$i] ;
          $remark_=$db->real_escape_string($a_remark[$i]) ;

                       $sql="Insert into ".
                            " sets_elements(set_id, order_num, prescription_id, remark)".
                            "        Values($put_id_, $i, $prescr_, '$remark_')" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Insert SETS_ELEMENTS... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

   }

          $db->commit() ;

        FileLog("", "Set data saved successfully") ;
     SuccessMsg() ;
  }
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

       echo "  SetDeseaseSelection(true, dss_id, dss_name, dss_group, dss_gcode, null) ;	\n" ;
    }

     $res->close() ;
  }
//--------------------------- Извлечение назначений комплекса

           $count  ='0' ;
           $put_id_=$db->real_escape_string($put_id) ;

//--------------------------- Извлечение состава комплекса

      echo     "  i_count.value='0'	;\n" ;

                     $sql="Select e.prescription_id, r.name, e.remark".
			  "  From sets_elements e left outer join prescriptions_registry r on e.prescription_id=r.id".
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

       echo "   AddListRow('".$fields[0]."', '".$fields[1]."', '".$fields[2]."', 0) ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Вывод данных на страницу

      echo     "  i_id         .value='".$put_id     ."' ;\n" ;
      echo     "  i_name       .value='".$name       ."' ;\n" ;
      echo     "  i_description.value='".$description."' ;\n" ;
      echo     "  i_after_save .value='".$after_save ."' ;\n" ;

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
//  Выдача информационного сообщения на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
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

    var  i_id ;
    var  i_count ;
    var  i_name ;
    var  i_description ;
    var  i_deseases ;
    var  i_after_save ;
    var  i_error ;

    var  s_prescription_list="" ;

    var  a_names=["Order","ShowOrder","Id","Name","Remark","Insert","Details","Delete","LiftUp"] ;

  function FirstField() 
  {
     var  i_category ;
     var  nl=new RegExp("@@","g") ;

	i_id         =document.getElementById("Id") ;
	i_count      =document.getElementById("Count") ;
	i_name       =document.getElementById("Name") ;
	i_description=document.getElementById("Description") ;
	i_deseases   =document.getElementById("Deseases") ;
	i_after_save =document.getElementById("AfterSave") ;
	i_error      =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

       i_description.value=i_description.value.replace(nl,"\n") ;

     if(i_after_save.value!="true")
        parent.frames["details"].location.replace("prescriptions_select.php?Deseases="+i_deseases.value+"&Selected="+s_prescription_list) ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;
     var  nl=new RegExp("\n","g") ;

	error_text="" ;
     
     if(i_name.value==''     )  error_text="Название комплекса должно быть задано" ;

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

          i_description.value=i_description.value.replace(nl,"@@") ;

           i_after_save.value="true" ;

                         return true ;         
  } 

  function AddListRow(p_id, p_name, p_remark, p_order)
  {
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_upp_new ;
     var  i_ins_new ;
     var  num_new ;


     if(p_id!='0')
     {
        if(s_prescription_list=="")  s_prescription_list =    p_id ;
        else                         s_prescription_list+=","+p_id ;
     }

     if(p_order==0)
     {
         num_new=parseInt(i_count.value)+1 ;
                          i_count.value=num_new ;

       i_set     = document.getElementById("Prescriptions") ;
       i_row_new = document.createElement("tr") ;
     }
     else
     {
                num_new=p_order ;
                
       i_row_new = document.getElementById("NewRow") ;
     }

       i_col_new = document.createElement("td") ;
       i_col_new . className = "Table_LT" ;

       i_fld_new = document.createElement("div") ;
       i_fld_new . id       ='ShowOrder_'+ num_new ;
       i_txt_new = document.createTextNode(num_new) ;
       i_fld_new . appendChild(i_txt_new) ;
       i_col_new . appendChild(i_fld_new) ;

       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Order_'+ num_new ;
       i_fld_new . type     ="hidden" ;
       i_fld_new . value    = num_new ;
       i_col_new . appendChild(i_fld_new) ;

       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "Table_LT" ;

       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Id_'+num_new ;
       i_fld_new . name     ='Id_'+num_new ;
       i_fld_new . type     ="hidden" ;
       i_fld_new . value    = p_id ;
       i_col_new . appendChild(i_fld_new) ;

       i_fld_new = document.createElement("div") ;
       i_fld_new . className = "Bold_LT" ;
       i_fld_new . id        = "Name_"+num_new ;
       i_txt_new = document.createTextNode(p_name) ;
       i_fld_new . appendChild(i_txt_new) ;
  if(p_id!='0' || p_order!=0)
  {
       i_txt_new = document.createElement("br") ;
       i_fld_new . appendChild(i_txt_new) ;
  }
       i_col_new . appendChild(i_fld_new) ;

       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Remark_'+num_new ;
       i_fld_new . name     ='Remark_'+num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . size     =  60 ;
       i_fld_new . value    = p_remark ;
       i_fld_new . onchange = function(e) {  this.parentNode.parentNode.id="" ;  }
       i_col_new . appendChild(i_fld_new) ;

       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "Table_LC" ;

       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . className ="DetailsButton" ;
       i_shw_new . value  ="?" ;
       i_shw_new . id     ='Details_'+num_new ;
       i_shw_new . onclick= function(e) {  ShowDetails(this.id) ;  }

     if(p_id=='0')
       i_shw_new . disabled= true ;

       i_del_new = document.createElement("input") ;
       i_del_new . type      ="button" ;
       i_del_new . className ="DeleteButton" ;
       i_del_new . value     ="X" ;
       i_del_new . id        ='Delete_'+ num_new ;
       i_del_new . onclick   = function(e) {  DeleteRow(this.id) ;  }
       i_upp_new = document.createElement("input") ;
       i_upp_new . type   ="button" ;
       i_upp_new . className ="UpButton" ;
       i_upp_new . value  ="^" ;
       i_upp_new . id     ='LiftUp_'+ num_new ;
       i_upp_new . onclick= function(e) {  LiftUpRow(this.id) ;  }
       i_ins_new = document.createElement("input") ;
       i_ins_new . type   ="button" ;
       i_ins_new . className ="InsertButton" ;
       i_ins_new . value  ="+" ;
       i_ins_new . id     ='Insert_'+ num_new ;
       i_ins_new . onclick= function(e) {  InsertNewRow(this.id) ;  }
       i_col_new . appendChild(i_ins_new) ;
       i_col_new . appendChild(i_upp_new) ;
       i_col_new . appendChild(i_del_new) ;
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

     if(p_order==0)
       i_set     . appendChild(i_row_new) ;

    return(num_new) ;         
  } 

  function InsertNewRow(p_id)
  {

    var  i_del  ;
    var  i_col  ;
    var  i_row  ;
    var  i_list  ;
    var  i_row  ;
    var  i_row_new  ;
    var  top  ;

    
    if(document.getElementById("NewRow")!=null)
    {
      alert("Сначала удалите ранее добавленную пустую строку") ;
        return ;
    }

	 i_elm =document.getElementById(p_id.replace("Insert","Order")) ;
           top =parseInt(i_elm.value) ;         
        bottom =parseInt(i_count.value) ;

         i_del =document.getElementById(p_id) ;
         i_col =   i_del.parentNode ;
         i_row =   i_col.parentNode ;
         i_list=   i_row.parentNode ;

     for(i=bottom ; i>=top ; i--)
     for(j in a_names) {
			           i_elm      =document.getElementById(a_names[j]+"_"+i) ;
			           i_elm.id   =a_names[j]+"_"+(i+1) ;
			           i_elm.name =a_names[j]+"_"+(i+1) ;
      if(a_names[j]=="Order"    )  i_elm.value= i+1 ;
      if(a_names[j]=="ShowOrder")  i_elm.innerHTML=i+1 ;
                       }  

       i_row_new = document.createElement("tr") ;
       i_row_new . id ="NewRow" ;

       i_list.insertBefore(i_row_new, i_row) ;

          i_count.value=parseInt(i_count.value)+1 ;

     AddListRow(0, "", "", top) ;
  }

  function AddSelectedRow(p_id, p_name)
  {
    var  order ;
    var  new_row ;


       new_row=document.getElementById("NewRow") ;

    if(new_row!=null)
    {
                  elems=new_row.getElementsByTagName('td')[0].getElementsByTagName('input') ;
       for(i=0; i<elems.length; i++)               
         if(elems[i].id.substr(0, 6)=="Order_") {  order=elems[i].id.substr(6) ;  break ;  } 
 
         document.getElementById("Id_"  +order).value    =p_id  ;
         document.getElementById("Name_"+order).innerHTML=p_name ;
         
      if(s_prescription_list=="")  s_prescription_list =    p_id ;
      else                         s_prescription_list+=","+p_id ;

             new_row.id="" ;
    }
    else
    {
       order=AddListRow(p_id, p_name, "", 0) ;
    }

      document.getElementById("Remark_"+order).focus() ;

    return ;         
  } 

  function DeleteRow(p_id)
  {
    var  i_del  ;
    var  i_col  ;
    var  i_row  ;
    var  i_list  ;
    var  i_elm  ;
    var  top  ;


	 i_elm =document.getElementById(p_id.replace("Delete","Order")) ;
           top =parseInt(i_elm.value) ;         
        bottom =parseInt(i_count.value) ;

         i_del =document.getElementById(p_id) ;
         i_col =   i_del.parentNode ;
         i_row =   i_col.parentNode ;
         i_list=   i_row.parentNode ;

         i_list.removeChild(i_row) ;

     for(i=top+1 ; i<=bottom ; i++) {
     for(j in a_names) {
			           i_elm      =document.getElementById(a_names[j]+"_"+i) ;
			           i_elm.id   =a_names[j]+"_"+(i-1) ;
			           i_elm.name =a_names[j]+"_"+(i-1) ;
      if(a_names[j]=="Order"    )  i_elm.value= i-1 ;
      if(a_names[j]=="ShowOrder")  i_elm.innerHTML=i-1 ;
                       }  
                                    } 

         i_count.value=i_count.value-1 ;

     return ;
  } 

  function LiftUpRow(p_id)
  {
    var  i_btn  ;
    var  i_col  ;
    var  i_row_1 ;
    var  i_row_2 ;
    var  i_list  ;
    var  i_elm  ;
    var  down ;
    var  up ;


          i_elm =document.getElementById(p_id.replace("LiftUp","Order")) ;
           down =parseInt(i_elm.value) ;         

    if(down<=1)  return ;

             up =down-1 ;

         i_btn  =document  .getElementById(p_id) ;
         i_col  =   i_btn  .parentNode ;
         i_row_2=   i_col  .parentNode ;
         i_list =   i_row_2.parentNode ;

         i_btn  =document  .getElementById("LiftUp_"+up) ;
         i_col  =   i_btn  .parentNode ;
         i_row_1=   i_col  .parentNode ;

         i_list.insertBefore(i_row_2, i_row_1) ;

     for(j in a_names) {
			 i_row_1      =document.getElementById(a_names[j]+"_"+up  ) ;
			 i_row_2      =document.getElementById(a_names[j]+"_"+down) ;
			 i_row_1.id   = a_names[j]+"_"+down ;
			 i_row_1.name = a_names[j]+"_"+down ;
			 i_row_2.id   = a_names[j]+"_"+up ;
			 i_row_2.name = a_names[j]+"_"+up ;
                          
      if(a_names[j]=="Order") {
                         i_row_1.value=down ;
                         i_row_2.value=up  ;
                              }
      if(a_names[j]=="ShowOrder") {
                         i_row_1.innerHTML=down ;
                         i_row_2.innerHTML=up  ;
                                  }
                       }  

     return ;
  } 

  function ShowDetails(p_id)
  {
    var  id_id ;
    var  i_id ;
    var  v_session ;
    var  v_form ;

             id_id=p_id.replace("Details","Id") ;
  	      i_id=document.getElementById(id_id) ;

         window.open("prescription_view.php?Id="+i_id.value) ;

    return ;         
  } 

  function LinkDesease() 
  {
        parent.frames["details"].location.replace("deseases_select.php?&Deseases="+i_deseases.value) ;

      document.getElementById("Deseases_select"     ).hidden=true ;
      document.getElementById("Prescriptions_select").hidden=false ;
  } 

  function LinkPrescriptions() 
  {
        parent.frames["details"].location.replace("prescriptions_select.php?Deseases="+i_deseases.value+"&Selected="+s_prescription_list) ;

      document.getElementById("Deseases_select"     ).hidden=false ;
      document.getElementById("Prescriptions_select").hidden=true ;
  } 

  function GoToPreview()
  {
     var  v_session ;

            v_session=TransitContext("restore","session","") ;
        window.open("set_view.php?Session="+v_session+"&Id="+i_id.value) ;          
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
       i_col_new . onclick= function(e) {  window.open("desease_view.php?Id="+p_id) ;  }
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

<body onload="FirstField();">

<noscript>
</noscript>

  <table width="90%">
    <thead>
    </thead>
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

  <form onsubmit="return SendFields();" method="POST">
  
  <table width="100%" >
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="Normal_CT">
          <br> 
          <input type="submit" value="Сохранить"   id="Save1"> 
          <input type="button" value="Просмотреть" id="Preview" onclick="GoToPreview();"> 
      </td>
      <td> </td>
    </tr>
    <tr>
      <td> <div class="Error_CT" id="Error"></div> </td>
    </tr>
    <tr>
    <td>

  <table>
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="Normal_RT"> Название </td>
      <td>
        <input type="text" size=60 name="Name" id="Name">
        <input type="hidden" name="Id" id="Id">
        <input type="hidden" name="Count" id="Count">
        <input type="hidden" name="Deseases" id="Deseases">
        <input type="hidden" name="AfterSave" id="AfterSave">
      </td>
    </tr>
    <tr>
      <td class="Normal_RT"> Описание </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Description" id="Description"> </textarea>
      </td>
    </tr>
    </tbody>
  </table>

      </td>
    </tr>
    <tr>
      <td>
        <table width="100%">
          <thead>
          </thead>
          <tbody  id="Deseases_list">
          </tbody>
        </table>
        <div class="Normal_CT">
          <input type="button"        value="Добавить/удалить заболевания" id="Deseases_select"      onclick=LinkDesease()>
          <input type="button" hidden value="Выбрать назначения"           id="Prescriptions_select" onclick=LinkPrescriptions()>
        </div> 
      </td>
    </tr>
    </tbody>
  </table>

  <table>
    <thead>
    </thead>
    <tbody  id="Prescriptions">
    </tbody>
  </table>
  
  </form>

</body>

</html>
