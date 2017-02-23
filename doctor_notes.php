<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_notes.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение параметров

          $session =$_GET ["Session"] ;
          $client  =$_GET ["Client"] ;

  if(isset($_POST["Check"]))
  {
          $check   =$_POST["Check"] ;
          $category=$_POST["Category"] ;
          $remark  =$_POST["Remark"] ;
          $deseases=$_POST["Deseases"] ;
          $anatomy =$_POST["Anatomy"] ;
  }

     FileLog("START", "Session  :".$session) ;
     FileLog("",      "Client   :".$client) ;
 
 if(isset($check))
 {
     FileLog("",      "Check    :".$check) ;
     FileLog("",      "Category :".$category) ;
     FileLog("",      "Remark   :".$remark) ;
     FileLog("",      "Deseases :".$deseases) ;
     FileLog("",      "Anatomy  :".$anatomy) ;
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

        $user_=$db->real_escape_string($user) ;

//--------------------------- Получение основного ключа шифрования

                       $sql="Select crypto ".
                            "  From access_list".
                            " Where owner='$user_' ".
                            "  and  login='$user_' ".
                            "  and  page =  0 " ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения ключа доступа") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_key='".$fields[0]."' ;	\n" ;

//--------------------------- Приведение данных

          $client_  =$db->real_escape_string($client) ;

  if(isset($check))
  {
          $category_=$db->real_escape_string($category) ;
          $remark_  =$db->real_escape_string($remark) ;
          $deseases_=$db->real_escape_string($deseases) ;
          $anatomy_ =$db->real_escape_string($anatomy) ;
  }
//--------------------------- Извлечение данных для отображения

  if(!isset($check))
  {
                       $sql="Select `check`, category, remark, deseases, anatomy".
                            " From  doctor_notes".
                            " Where owner='$user_' and client='$client_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select DOCTOR_NOTES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $check   =$fields[0] ;
                   $category=$fields[1] ;
                   $remark  =$fields[2] ;
                   $deseases=$fields[3] ;
                   $anatomy =$fields[4] ;

        FileLog("", "Doctor notes presented successfully") ;
  }
//--------------------------- Сохранение данных со страницы
  else
  {
                       $sql="Update doctor_notes".
                            " Set   category='$category_'".
                            "      ,remark  ='$remark_'".
                            "      ,deseases='$deseases_'".
                            "      ,anatomy ='$anatomy_'".
                            " Where owner='$user' and client='$client_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update DOCTOR_NOTES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "Doctor notes saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Вывод данных на страницу

      echo     "  i_client  .value='".$client.  "' ;	\n" ;
      echo     "  i_check   .value='".$check.   "' ;	\n" ;
      echo     "  i_category.value='".$category."' ;	\n" ;
      echo     "  i_remark  .value='".$remark.  "' ;	\n" ;
      echo     "  i_deseases.value='".$deseases."' ;	\n" ;
      echo     "  i_anatomy .value='".$anatomy. "' ;	\n" ;

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

       echo "    a_dss_name [".$i."]='".$fields[0]."' ;	\n" ;
       echo "    a_dss_group[".$i."]='".$fields[1]."' ;	\n" ;
       echo "    a_dss_gcode[".$i."]='".$fields[2]."' ;	\n" ;
       echo "    a_dss_id   [".$i."]='".$fields[3]."' ;	\n" ;
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

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;				\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;			\n" ;
    echo  "i_error.innerHTML  ='Данные успешно сохранены!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Doctor Notes</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;
    var  i_client ;
    var  i_check ;
    var  i_category ;
    var  i_remark ;
    var  i_deseases ;
    var  i_anatomy ;
    var  i_error ;

    var  a_dss_name ;
    var  a_dss_group ;
    var  a_dss_gcode ;
    var  a_dss_id   ;

    var  password ;
    var  page_key ;
    var  gshow_start ;
    var  gshow_end ;

  function FirstField() 
  {
     var  i_check ;
     var  dss_list ;
     var  words ;


       i_table    =document.getElementById("Fields") ;
       i_client   =document.getElementById("Client") ;
       i_check    =document.getElementById("Check") ;
       i_category =document.getElementById("Category") ;
       i_remark   =document.getElementById("Remark") ;
       i_deseases =document.getElementById("Deseases") ;
       i_anatomy  =document.getElementById("Anatomy") ;
       i_error    =document.getElementById("Error") ;

       a_dss_name =new Array() ;
       a_dss_group=new Array() ;
       a_dss_gcode=new Array() ;
       a_dss_id   =new Array() ;

       i_category.focus() ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

           page_key=Crypto_decode( page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
//	i_error.style.color="red" ;
//	i_error.innerHTML  ="Ошибка расшифровки данных." ;
//         return true ;
     }

       i_category.value=Crypto_decode(i_category.value, page_key) ;
       i_remark  .value=Crypto_decode(i_remark  .value, page_key) ;
       i_deseases.value=Crypto_decode(i_deseases.value, page_key) ;
       i_anatomy .value=Crypto_decode(i_anatomy .value, page_key) ;

        dss_list=i_deseases.value.split("@") ;

                 i_deseases.value="" ;

    for(i=0 ; i<dss_list.length ; i++) {

         if(dss_list[i]=="")  break ;

                                     words=dss_list[i].split("#") ;
           SetDeseaseSelection(true, words[0], words[1]) ;
                                       }

        i_deseases.value=i_deseases.value.trim() ;

    for(i=0 ; i<a_dss_id.length ; i++)
      AddNewRow(i, a_dss_name[i], a_dss_group[i], a_dss_gcode[i], a_dss_id[i]) ;

        parent.frames["anatomy"].location.replace('anatomia_main.html?groups='+i_anatomy.value+'&Doctor') ;

         return true ;
  }

  function SendFields() 
  {
     var  i_client_cat ;
     var  i_dss_name ;
     var  error_text ;
     var  words ;

	error_text=""
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

        i_client_cat=parent.frames['section'].document.getElementById(i_client.value+'_category') ;
    if(i_client_cat!=null)  i_client_cat.innerHTML=  i_category.value ;

       i_category.value=Crypto_encode(i_category.value, page_key) ;
       i_remark  .value=Crypto_encode(i_remark  .value, page_key) ;

        i_deseases.value=i_deseases.value.trim() ;
                   words=i_deseases.value.split(" ") ;

        i_deseases.value="" ;

    for(i=0 ; i<words.length ; i++) {
                 i_dss_name=document.getElementById("DssName_"+words[i]) ;
              if(i_dss_name==null)  continue ;
                 i_deseases.value+=words[i]+"#"+i_dss_name.innerHTML+"@" ;
                                    }

       i_deseases.value=Crypto_encode(i_deseases.value, page_key) ;

       i_anatomy .value=parent.frames["anatomy"].GetAnatomy() ;
       i_anatomy .value=Crypto_encode(i_anatomy .value, page_key) ;

                         return true ;         
  } 

  function SetAnatomy(p_groups)
  {
      parent.frames["anatomy"].SetGroups(p_groups) ;
  }

  function LinkDesease() 
  {
     document.getElementById("Column1"       ).hidden=true ;
     document.getElementById("Column2"       ).hidden=true ;
     document.getElementById("Column0"       ).hidden=false ;
     document.getElementById("SelectDeseases").hidden=false ;

          i_error.innerHTML ="" ;
  } 

  function CallBack() 
  {
     document.getElementById("SelectDeseases").hidden=true ;
     document.getElementById("Column0"       ).hidden=true ;
     document.getElementById("Column1"       ).hidden=false ;
     document.getElementById("Column2"       ).hidden=false ;
  } 

  function AddNewRow(p_num, p_name, p_group, p_gcode, p_id)
  {
     var  i_dsa_lst ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_chk_new ;
     var  v_disabled ;
     var  v_class ;
     var  v_checked ;
 

   if(p_gcode==0) 
   {
                     v_class   ="tableG" ;
                     v_disabled= true ;
                     v_checked = false ;
   }
   else
   {
                     v_class   ="tableL" ;
                     v_disabled= false ;

         i_deseases.value=" "+i_deseases.value+" " ;
      if(i_deseases.value.indexOf(" "+p_id+" ")<0)  v_checked=false ;
      else                                          v_checked=true ;

        i_deseases.value=i_deseases.value.trim() ;
   }

       i_dss_lst = document.getElementById("AllDeseases") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = v_class ;
       i_row_new . id        = p_num ;

   if(p_gcode!=0 && v_checked===false)
       i_row_new . hidden    = true ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_col_new . width="1%" ;

       i_chk_new = document.createElement("input") ;
       i_chk_new . type     ="checkbox" ;
       i_chk_new . className= v_class ;
       i_chk_new . id       ="Check_"+p_num ;
       i_chk_new . value    = p_id ;
       i_chk_new . checked  = v_checked ;
       i_chk_new . disabled = v_disabled ;
       i_chk_new . onclick  = function(e) {  DeseaseSet(this, p_id, p_name) ;  } ;

   if(p_gcode!=0)
       i_col_new . appendChild(i_chk_new) ;
   else
       i_col_new . onclick= function(e) { ShowGroup(this.parentNode.id) ; } ;

       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = v_class ;

   if(p_gcode==0)
   {
       i_txt_new = document.createTextNode(p_group) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . onclick= function(e) { ShowGroup(this.parentNode.id) ; } ;
   }
   else
   {
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . onclick= function(e) {  window.open("desease_view.php?Id="+p_id) ;  } ;
   } 
       i_row_new . appendChild(i_col_new) ;

       i_dss_lst . appendChild(i_row_new) ;

    return ;         
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
        if(i_row          ==  null  )  break ;
        if(i_row.className=="tableG")  break ;

            gshow_start=id_from ; 
           i_row.hidden=false ;
              gshow_end=i ; 
     }
  }

  function DeseaseSet(p_this, p_id, p_name) 
  {
     SetDeseaseSelection(p_this.checked, p_id, p_name) ;
  }

  function SetDeseaseSelection(p_checked, p_id, p_name)
  {
     var  i_dss_list ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  v_id ;

                  v_id ="Desease_"+p_id ;
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
       i_col_new . className = "tableL" ;
       i_col_new . id        =  "DssName_"+p_id ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . onclick= function(e) { window.open("desease_view.php?Id="+p_id) ; } ;
       i_row_new . appendChild(i_col_new) ;

       i_dss_list.insertBefore(i_row_new, null) ;

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

  <form onsubmit="return SendFields();" method="POST">

  <div class="error" id="Error"></div>
  <table>
    <tr>
      <td class="fieldC" id="Column0" hidden>
        <div class="fixT">
          <input type="button" value="Обратно" onclick=CallBack()>
        </div> 
        <div class="hidden">
          --------------
        </div> 
      </td>
      <td class="field"  id="Column1">
        <table width="80%" id="Fields">
          <tbody>
          <tr>
            <td class="fieldL"> <input type="submit" class="SaveButton" value="Сохранить"> </td>
            <td class="fieldL"> <input type="text" size=70 name="Category" id="Category" placeholder="Примечание"> </td>
          </tr>
          <tr>
            <td class="field"> Пoдробности </td>
            <td class="fieldL"> 
              <textarea cols=65 rows=4 wrap="soft" name="Remark" id="Remark"> </textarea>
            </td>
          </tr>
          <tr>
            <td>
              <input type="hidden" name="Client"   id="Client">
              <input type="hidden" name="Check"    id="Check">
              <input type="hidden" name="Deseases" id="Deseases">
              <input type="hidden" name="Anatomy"  id="Anatomy">
            </td>
          </tr>
          </tbody>
        </table>
      </td>
      <td class="field" id="Column2">
        <div class="fieldC">
          <input type="button" value="Добавить/удалить заболевания" onclick=LinkDesease()>
        </div> 
        <table width="100%">
          <tbody  id="Deseases_list">
          </tbody>
        </table>
      </td>
      <td id="SelectDeseases" hidden>
        <table width="100%">
          <tbody>
            <tr>
              <td>
                <table width="100%">
                  <thead>
                  </thead>
                  <tbody id="AllDeseases">
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </table>

  </form>

</body>

</html>
