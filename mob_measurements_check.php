<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_measurements_check.php" ;

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

                         $session=$_GET["Session"] ;
                           $owner=$_GET["Owner"] ;
                            $page=$_GET["Page"] ;

    FileLog("START", "Session:".$session) ;
    FileLog("",      "  Owner:".$owner) ;
    FileLog("",      "   Page:".$page) ;

           $values_checked=0 ;

  foreach($_POST as $key => $value) 
   if(substr($key, 0, 6)=="Value_")
   {
     if($value!="") {  $a_values[substr($key, 6)]=$value ;
                                  $values_checked=  1 ;     }

        FileLog("", $key." : ".$value) ;
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
//--------------------------- Приведение параметров

          $owner_=$db->real_escape_string($owner) ;
          $user_ =$db->real_escape_string($user ) ;
          $page_ =$db->real_escape_string($page ) ;

//--------------------------- Сохранение введенных данных

  if($values_checked)
  {
//- - - - - - - - - - Определение идентификатора страницы
                     $sql="Select id".
			  "  From client_pages".
                          " Where owner='$owner_'".
                          "  and  page = $page_" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES(ID)... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса идентификатора назначения") ;
                         return ;
  }
	 $fields=$res->fetch_row() ;
                 $res->close() ;

	$page_id=$fields[0] ;
//- - - - - - - - - - - - - - Сохранение введенных данных
     foreach($a_values as $key => $value) 
     {
          $key_  =$db->real_escape_string($key  ) ;
          $value_=$db->real_escape_string($value) ;

			   $sql="Insert into ".
				"measurements( page_id,  measurement_id,  value  )".
				"      values($page_id, $key_,          '$value_')" ;
	   $res=$db->query($sql) ;
	if($res===false) {
               FileLog("ERROR", "Insert MEASUREMENTS... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                           return ;
        }

     }
//- - - - - - - - - - - - - - Возврат на страницу назначений
          $db->commit() ;

        FileLog("", "Measurements values saved successfully") ;
     SuccessMsg() ;

      echo     "  location.assign('mob_client_prescr_view.php?Session=".$session."&Owner=".$owner."&Page=".$page."') ;	\n" ;
//- - - - - - - - - - - - - -
  }
//--------------------------- Извлечение ключа страницы

                       $sql="Select  crypto".
                            "  From  access_list".
                            " Where `owner`='$owner_' ".
                            "  and  `login`='$user_' ".
                            "  and  `page` = $page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select ACCESS_LIST...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения ключа доступа") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_num  ='" .$page."' ;		\n" ;
      echo     "   page_owner='" .$owner."' ;		\n" ;
      echo     "   page_key  ='" .$fields[0]."' ;	\n" ;

//--------------------------- Извлечение списка назначений

                     $sql="Select prescription_id, name, remark, if(reference=0,id,reference)".
			  "  From prescriptions_pages".
                          " Where owner='$owner_'".
                          "  and  page = $page_".
                          "  and `type`= 'measurement'".
                          " Order by order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_PAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка назначений") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_plist_id    [".$i."]='".$fields[0]."' ;	\n" ;
       echo "   a_plist_name  [".$i."]='".$fields[1]."' ;	\n" ;
       echo "   a_plist_remark[".$i."]='".$fields[2]."' ;	\n" ;
       echo "   a_plist_ref   [".$i."]='".$fields[3]."' ;	\n" ;
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
//  Выдача информационного сообщения на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
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

<title>DarkMed-Mobile Measurements check</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_set ;
    var  i_error ;
    var  page_owner ;
    var  page_num ;

    var  a_plist_id ;
    var  a_plist_name ;
    var  a_plist_remark ;
    var  a_plist_ref ;

  function FirstField() 
  {
    var  password ;
    var  page_key ;
    var  prescr_id ;
    var  prescr_name ;
    var  prescr_remark ;

       i_set    =document.getElementById("Prescriptions") ;
       i_error  =document.getElementById("Error") ;

	a_plist_id    =new Array() ;
	a_plist_name  =new Array() ;
	a_plist_remark=new Array() ;
	a_plist_ref   =new Array() ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;

       page_key= Crypto_decode( page_key, password) ;

       for(i in a_plist_id) {
             prescr_id    =Crypto_decode(a_plist_id    [i], page_key) ;
             prescr_name  =Crypto_decode(a_plist_name  [i], page_key) ;
             prescr_remark=Crypto_decode(a_plist_remark[i], page_key) ;

          AddListRow(i, prescr_id, prescr_name, prescr_remark, a_plist_ref[i]) ;
       }

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

	 error_text=""

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;
  } 

  function AddListRow(p_order, p_id, p_name, p_remark, p_reference)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_fld_new ;
     var  i_div_new ;


	i_row_new = document.createElement("tr") ;
	i_row_new . className = "table" ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_txt_new = document.createTextNode(p_name) ;
	i_col_new . appendChild(i_txt_new) ;
	i_div_new = document.createElement("div") ;
	i_div_new . className = "fieldC" ;
	i_fld_new = document.createElement("input") ;
	i_fld_new . id       ='Value_'+p_reference ;
	i_fld_new . name     ='Value_'+p_reference ;
	i_fld_new . type     ="text" ;
	i_div_new . appendChild(i_fld_new) ;
	i_col_new . appendChild(i_div_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_txt_new = document.createTextNode(p_remark) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_set     . appendChild(i_row_new) ;

    return ;         
  } 

  function ShowDetails(p_id)
  {
    window.open("prescription_view.php?Id="+p_id) ;
  }

  function GoBack()
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

      location.assign("mob_client_prescr_view.php?Session="+v_session+"&Owner="+page_owner+"&Page="+page_num) ;
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
        <input type="button" value="!" hidden onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b>ВВОД ДАННЫХ КОНТРОЛЬНЫХ ИЗМЕРЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div class="error" id="Error"></div>
  <form onsubmit="return SendFields();" method="POST" id="Form">

  <br>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Prescriptions">
    </tbody>
  </table>

    <br>
  <div class="fieldC">
       <input type="submit" class="G_bttn" value="Сохранить" id="Save">
       <br>
       <br>
       <input type="button" class="R_bttn" value="Вернуться" id="Cancel" onclick=GoBack()>
  </div>

  </form>

</div>

</body>

</html>
