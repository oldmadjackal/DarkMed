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

  if( isset($put_id )) 
  {
                         $name       =$_POST["Name"] ;
                         $description=$_POST["Description"] ;
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

        FileLog("", "New prescription generated successfully") ;
  }
//--------------------------- Извлечение данных для отображения
  else
  if(!isset($put_id))
  {
          $get_id_=$db->real_escape_string($get_id) ;

                       $sql="Select id, user, name, description".
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

                       $sql="Update sets_registry".
                            " Set   name       ='$name_'".
                            "      ,description='$description_'".
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
//--------------------------- Извлечение назначений комплекса

                   $count      ='0' ;

//--------------------------- Извлечение списка типов назначений

                     $sql="Select code, name".
			  "  From ref_prescriptions_types".
			  " Where `language`='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_PRESCRIPTIONS_TYPES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника типов назначений") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "                      i_category.length++ ;				\n" ;
       echo "   i_category.options[i_category.length-1].text	='".$fields[1]."' ;	\n" ;
       echo "   i_category.options[i_category.length-1].value	='".$fields[0]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение списка назначений

                     $sql="Select id, type, name".
			  "  From prescriptions_registry".
                          " Where type<>'dummy'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра назначений") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "                              i_c_".$fields[1].".length++ ;				\n" ;
       echo "   i_c_".$fields[1].".options[i_c_".$fields[1].".length-1].text ='".$fields[2]."' ;	\n" ;
       echo "   i_c_".$fields[1].".options[i_c_".$fields[1].".length-1].value='".$fields[0]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение состава комплекса

      echo     "  i_count.value='0'	;\n" ;

          $put_id_=$db->real_escape_string($put_id) ;

                     $sql="Select e.prescription_id, r.type, e.remark".
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

       echo "   AddListRow('".$fields[0]."', '".$fields[1]."', '".$fields[2]."') ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Вывод данных на страницу

      echo     "  i_id         .value='".$put_id     ."' ;\n" ;
      echo     "  i_name       .value='".$name       ."' ;\n" ;
      echo     "  i_description.value='".$description."' ;\n" ;

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
    var  i_id ;
    var  i_count ;
    var  i_name ;
    var  i_description ;
    var  i_error ;


  function FirstField() 
  {
     var  i_category ;
     var  nl=new RegExp("@@","g") ;

	i_table      =document.getElementById("Fields") ;
	i_id         =document.getElementById("Id") ;
	i_count      =document.getElementById("Count") ;
	i_name       =document.getElementById("Name") ;
	i_description=document.getElementById("Description") ;
	i_error      =document.getElementById("Error") ;

	i_category=document.getElementById("Category") ;
			   i_category.length++ ;
	i_category.options[i_category.length-1].text ='' ;
	i_category.options[i_category.length-1].value='dummy' ;

	i_c_exercise       =document.getElementById("C_exercise"       ) ;
	i_c_exploration    =document.getElementById("C_exploration"    ) ;
	i_c_operation      =document.getElementById("C_operation"      ) ;
	i_c_others         =document.getElementById("C_others"         ) ;
	i_c_pharmacotherapy=document.getElementById("C_pharmacotherapy") ;
	i_c_test           =document.getElementById("C_test"           ) ;
	i_c_treatment      =document.getElementById("C_treatment"      ) ;
	i_c_unregistered   =document.getElementById("C_unregistered"   ) ;

			            i_c_exercise.length++ ;
	i_c_exercise       .options[i_c_exercise.length   -1].text ='' ;
	i_c_exercise       .options[i_c_exercise.length   -1].value='0' ;
			            i_c_exploration.length++ ;
	i_c_exploration    .options[i_c_exploration.length-1].text ='' ;
	i_c_exploration    .options[i_c_exploration.length-1].value='0' ;
			            i_c_operation.length++ ;
	i_c_operation      .options[i_c_operation.length-1].text ='' ;
	i_c_operation      .options[i_c_operation.length-1].value='0' ;
			            i_c_others.length++ ;
	i_c_others         .options[i_c_others.length-1].text ='' ;
	i_c_others         .options[i_c_others.length-1].value='0' ;
			            i_c_pharmacotherapy.length++ ;
	i_c_pharmacotherapy.options[i_c_pharmacotherapy.length-1].text ='' ;
	i_c_pharmacotherapy.options[i_c_pharmacotherapy.length-1].value='0' ;
			            i_c_test.length++ ;
	i_c_test           .options[i_c_test.length-1].text ='' ;
	i_c_test           .options[i_c_test.length-1].value='0' ;
			            i_c_treatment.length++ ;
	i_c_treatment      .options[i_c_treatment.length-1].text ='' ;
	i_c_treatment      .options[i_c_treatment.length-1].value='0' ;
			            i_c_unregistered.length++ ;
	i_c_unregistered   .options[i_c_unregistered.length-1].text ='' ;
	i_c_unregistered   .options[i_c_unregistered.length-1].value='0' ;

<?php
            ProcessDB() ;
?>

       i_description.value=i_description.value.replace(nl,"\n") ;

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

                         return true ;         
  } 

  function SetCategory(p_id, p_category)
  {
     var  id_id ;
     var  prescription_id ;
     var  i_prescription ;
     var  i_template ;

           
          id_id            =p_id.replace("Category","Id") ;
          prescription_id  =p_id.replace("Category","Prescription") ;
  	i_prescription     =document.getElementById(prescription_id) ;
  	i_template         =document.getElementById("C_"+p_category).cloneNode(true) ;
        i_template.id      =prescription_id ;
        i_template.hidden  =false ;
        i_template.onchange=function(e) {                 this.options[0].disabled=true ;
                                          SetPrescription(this.id, this.options[this.selectedIndex].value) ;  } ;
        i_prescription.parentNode.replaceChild(i_template, i_prescription) ;

  	   document.getElementById(id_id).value='0' ;

    return ;         
  } 

  function SetPrescription(p_id, p_prescription)
  {
     var  v_session ;
     var  v_form ;
     var  id_id ;
     var  show_id ;
     var  i_id ;
     var  i_show ;

           
       id_id      =p_id.replace("Prescription","Id") ;
  	i_id      =document.getElementById(id_id) ;
        i_id.value=p_prescription ;

          show_id      =p_id.replace("Prescription","Details") ;
  	i_show         =document.getElementById(show_id) ;
        i_show.disabled=false ;

	 v_session=TransitContext("restore","session","") ;
            v_form="prescription_details_any.php" ;
	parent.frames["details"].location.assign(v_form+"?Session="+v_session+"&Id="+p_prescription) ;



    return ;         
  } 

  function AddListRow(p_id, p_category, p_remark)
  {
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_cat_new ;
     var  i_prs_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_upp_new ;
     var  num_new ;


     if(p_id=='0')  p_category='unregistered' ;

       num_new=parseInt(i_count.value)+1 ;
                        i_count.value=num_new ;

       i_cat_new = document.getElementById("Category").cloneNode(true) ;
       i_cat_new . id      ='Category_'+num_new ;
       i_cat_new . hidden  = false ;
       i_cat_new . onchange= function(e) {                      this.options[0].disabled=true ;
                                           SetCategory(this.id, this.options[this.selectedIndex].value) ;  } ;
       i_cat_new.options[0].disabled=true ;
       i_cat_new.options[0].selected=false ;

     for(i=1 ; i<i_cat_new.length ; i++)
       if(i_cat_new.options[i].value==p_category)  i_cat_new.options[i].selected=true ;

       i_prc_new         =document.getElementById("C_"+p_category).cloneNode(true) ;
       i_prc_new.id      ='Prescription_'+num_new ; ;
       i_prc_new.hidden  =false ;
       i_prc_new.onchange=function(e) {                 this.options[0].disabled=true ;
                                         SetPrescription(this.id, this.options[this.selectedIndex].value) ;  } ;

       i_prc_new.options[0].disabled=true ;
       i_prc_new.options[0].selected=false ;

     for(i=1 ; i<i_prc_new.length ; i++)
       if(i_prc_new.options[i].value==p_id)  i_prc_new.options[i].selected=true ;

       i_set     = document.getElementById("Prescriptions") ;
       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Order_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . disabled = true ;
       i_fld_new . style.textAlign="right" ;
       i_fld_new . size     =  1 ;
       i_fld_new . value    = num_new ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Id_'+ num_new ;
       i_fld_new . name     ='Id_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . hidden   = true ;
       i_fld_new . value    = p_id ;
       i_col_new . appendChild(i_fld_new) ;
       i_col_new . appendChild(i_cat_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_prc_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Remark_'+ num_new ;
       i_fld_new . name     ='Remark_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . size     =  80 ;
       i_fld_new . value    = p_remark ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Подробнее" ;
       i_shw_new . id     ='Details_'+ num_new ;
       i_shw_new . onclick= function(e) {  ShowDetails(this.id) ;  }

     if(p_id=='0')
       i_shw_new . disabled= true ;

       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="Удалить" ;
       i_del_new . id     ='Delete_'+ num_new ;
       i_del_new . onclick= function(e) {  DeleteRow(this.id) ;  }
       i_upp_new = document.createElement("input") ;
       i_upp_new . type   ="button" ;
       i_upp_new . value  ="Вверх" ;
       i_upp_new . id     ='LiftUp_'+ num_new ;
       i_upp_new . onclick= function(e) {  LiftUpRow(this.id) ;  }
       i_col_new . appendChild(i_upp_new) ;
       i_col_new . appendChild(i_del_new) ;
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_set     . appendChild(i_row_new) ;

    return ;         
  } 

  function AddNewRow()
  {
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_cat_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_upp_new ;
     var  num_new ;

       num_new=parseInt(i_count.value)+1 ;
                        i_count.value=num_new ;

       i_cat_new = document.getElementById("Category").cloneNode(true) ;
       i_cat_new . id      ='Category_'+ num_new ;
       i_cat_new . hidden  = false ;
       i_cat_new . onchange= function(e) {                      this.options[0].disabled=true ;
                                           SetCategory(this.id, this.options[this.selectedIndex].value) ;  } ;

       i_set     = document.getElementById("Prescriptions") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Order_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . disabled = true ;
       i_fld_new . style.textAlign="right" ;
       i_fld_new . size     =  1 ;
       i_fld_new . value    = num_new ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Id_'+ num_new ;
       i_fld_new . name     ='Id_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . hidden   = true ;
       i_col_new . appendChild(i_fld_new) ;
       i_col_new . appendChild(i_cat_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("select") ;
       i_fld_new . disabled = true ;
       i_fld_new . id       ='Prescription_'+ num_new ;

       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Remark_'+ num_new ;
       i_fld_new . name     ='Remark_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . size     =  80 ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type    ="button" ;
       i_shw_new . value   ="Подробнее" ;
       i_shw_new . id      ='Details_'+ num_new ;
       i_shw_new . disabled= true ;
       i_shw_new . onclick = function(e) {  ShowDetails(this.id) ;  }
       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="Удалить" ;
       i_del_new . id     ='Delete_'+ num_new ;
       i_del_new . onclick= function(e) {  DeleteRow(this.id) ;  }
       i_upp_new = document.createElement("input") ;
       i_upp_new . type   ="button" ;
       i_upp_new . value  ="Вверх" ;
       i_upp_new . id     ='LiftUp_'+ num_new ;
       i_upp_new . onclick= function(e) {  LiftUpRow(this.id) ;  }
       i_col_new . appendChild(i_upp_new) ;
       i_col_new . appendChild(i_del_new) ;
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_set     . appendChild(i_row_new) ;

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
    var  a_names ;


	 i_elm =document.getElementById(p_id.replace("Delete","Order")) ;
           top =parseInt(i_elm.value) ;         
        bottom =parseInt(i_count.value) ;

         i_del =document.getElementById(p_id) ;
         i_col =   i_del.parentNode ;
         i_row =   i_col.parentNode ;
         i_list=   i_row.parentNode ;

         i_list.removeChild(i_row) ;

       a_names=["Order","Id","Category","Prescription","Remark","Details","Delete","LiftUp"] ;

     for(i=top+1 ; i<=bottom ; i++) {
     for(j in a_names) {
			       i_elm      =document.getElementById(a_names[j]+"_"+i) ;
			       i_elm.id   =a_names[j]+"_"+(i-1) ;
			       i_elm.name =a_names[j]+"_"+(i-1) ;
      if(a_names[j]=="Order")  i_elm.value= i-1 ;
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
    var  a_names ;
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

       a_names=["Order","Id","Category","Prescription","Remark","Details","Delete","LiftUp"] ;

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

	 v_session=TransitContext("restore","session","") ;
	    v_form="prescription_details_any.php" ;

	parent.frames["details"].location.assign(v_form+"?Id="+i_id.value) ;

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
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Сохранить"  id="Save1"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="field"> Название </td>
      <td> <input type="text" size=60 name="Name" id="Name"> </td>
      <td> <input type="hidden" name="Id" id="Id"> </td>
      <td> <input type="hidden" name="Count" id="Count"> </td>
    </tr>
    <tr>
      <td class="field"> Описание </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Description" id="Description"> </textarea>
      </td>
    </tr>
    </tbody>
  </table>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Prescriptions">
    </tbody>
  </table>

      <input type="button" value="Добавить назначение" onclick=AddNewRow()> </td>

    <select hidden id="Category"         ></select>
    <select hidden id="C_exercise"       ></select>
    <select hidden id="C_exploration"    ></select>
    <select hidden id="C_operation"      ></select>
    <select hidden id="C_others"         ></select>
    <select hidden id="C_pharmacotherapy"></select>
    <select hidden id="C_test"           ></select>
    <select hidden id="C_treatment"      ></select>
    <select hidden id="C_unregistered"   ></select>
  
  </form>

</div>

</body>

</html>
