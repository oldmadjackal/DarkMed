<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_prescr_view.php" ;

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
//--------------------------- Приведение параметров

          $owner_  =$db->real_escape_string($owner) ;
          $user_   =$db->real_escape_string($user ) ;
          $page_   =$db->real_escape_string($page ) ;

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

      echo     "   page_key=\"" .$fields[0]."\" ;\n" ;

//--------------------------- Извлечение данных страницы

                       $sql="Select p.title, p.remark, p.creator, CONCAT_WS(' ', d.name_f,d.name_i,d.name_o)".
                            "  From client_pages p, doctor_page_main d".
                            " Where d.owner=p.creator".
			    "  and  p.owner='$owner_'".
                            "  and  p.page = $page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select CLIENT_PAGES...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $title    =$fields[0] ;
                   $remark   =$fields[1] ;
                   $creator  =$fields[2] ;
                   $creator_n=$fields[3] ;

        FileLog("", "User ".$owner." additional page ".$page_." presented successfully") ;

//--------------------------- Извлечение списка назначений

          $put_id_=$db->real_escape_string($put_id) ;

                     $sql="Select prescription_id, name, remark".
			  "  From prescriptions_pages".
                          " Where owner='$owner_'".
                          "  and  page = $page_".
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

       echo "   a_plist_id    [".($i+1)."]='".$fields[0]."' ;	\n" ;
       echo "   a_plist_name  [".($i+1)."]='".$fields[1]."' ;	\n" ;
       echo "   a_plist_remark[".($i+1)."]='".$fields[2]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Отображение данных на странице

      echo     "    creator          ='".$creator  ."'	;\n" ;
      echo     "  i_title  .innerHTML='".$title    ."'	;\n" ;
      echo     "  i_creator.innerHTML='".$creator_n."'	;\n" ;
      echo     "  i_remark .innerHTML='".$remark   ."'	;\n" ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color=\"red\" ;      \n" ;
    echo  "i_error.innerHTML  =\"".$text."\" ;\n" ;
    echo  "return ;\n" ;
}

//============================================== 
//  Выдача информационного сообщения на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color=\"blue\" ;      \n" ;
    echo  "i_error.innerHTML  =\"".$text."\" ;\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color=\"green\" ;                    \n" ;
    echo  "i_error.innerHTML  =\"Данные успешно сохранены!\" ;\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Client Prescriptions View</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_title ;
    var  i_creator ;
    var  i_remark ;
    var  i_set ;
    var  i_error ;
    var  creator ;

    var  a_plist_id ;
    var  a_plist_name ;
    var  a_plist_remark ;


  function FirstField() 
  {
    var  password ;
    var  page_key ;
    var  prescr_id ;
    var  prescr_name ;
    var  prescr_remark ;

       i_title  =document.getElementById("Title") ;
       i_creator=document.getElementById("Creator") ;
       i_remark =document.getElementById("Remark") ;
       i_set    =document.getElementById("Prescriptions") ;
       i_error  =document.getElementById("Error") ;

	a_plist_id    =new Array() ;
	a_plist_name  =new Array() ;
	a_plist_remark=new Array() ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;

       page_key= Crypto_decode( page_key, password) ;

       i_title .innerHTML=Crypto_decode(i_title .innerHTML, page_key) ;
       i_remark.innerHTML=Crypto_decode(i_remark.innerHTML, page_key) ;

       for(i in a_plist_id) {
             prescr_id    =Crypto_decode(a_plist_id    [i], page_key) ;
             prescr_name  =Crypto_decode(a_plist_name  [i], page_key) ;
             prescr_remark=Crypto_decode(a_plist_remark[i], page_key) ;

          AddListRow(i, prescr_id, prescr_name, prescr_remark) ;
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

  function AddListRow(p_order, p_id, p_name, p_remark)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;


       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_txt_new = document.createTextNode(p_order) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_txt_new = document.createTextNode(p_remark) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Подробнее" ;
       i_shw_new . id     ='Details_'+ p_order ;
       i_shw_new . onclick= function(e) {  ShowDetails(p_id) ;  }
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_set     . appendChild(i_row_new) ;

    return ;         
  } 

  function ShowDetails(p_id)
  {
    window.open("prescription_view.php?Id="+p_id) ;
  }

  function WhoIsIt()
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

    window.open("doctor_view.php"+"?Session="+v_session+"&Owner="+creator) ;
  } 

  function ChatWith()
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	location.assign("messages_chat_lr.php?Session="+v_session+"&Sender="+creator) ;
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
        <b>СТРАНИЦА НАЗНАЧЕНИЙ (ПРОСМОТР)</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div class="error" id="Error"></div>
  <form onsubmit="return SendFields();" method="POST" id="Form">

  <b><div class="fieldC" id="Title"></div></b>
  <br>
  <div class="fieldC">
    <span><b>Врач: </b></span>
    <span id="Creator"></span>
    <input type="button" value="Кто это?" onclick=WhoIsIt()>
    <input type="button" value="Переписка" onclick=ChatWith()>
  </div>
  <br>
  <div left=5m id="Remark"></div> 
  <br>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Prescriptions">
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
