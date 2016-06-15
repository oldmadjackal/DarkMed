<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script ="Client_prescr_pilot.php" ;

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
                       $db->close() ;
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

      echo     "   page_key='" .$fields[0]."' ;	\n" ;

//--------------------------- Извлечение данных страницы

                       $sql="Select p.title, p.remark, p.creator, CONCAT_WS(' ', d.name_f,d.name_i,d.name_o), p.presentation".
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

                   $title       =$fields[0] ;
                   $remark      =$fields[1] ;
                   $creator     =$fields[2] ;
                   $creator_n   =$fields[3] ;
                   $presentation=$fields[4] ;

        FileLog("", "User ".$owner." additional page ".$page_." presented successfully") ;

      echo     "  i_remark .innerHTML='".$remark."'	;\n" ;

//--------------------------- Извлечение списка назначений

                     $sql="Select prescription_id".
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

       echo "   a_plist_id[".($i+1)."]='".$fields[0]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Отображение данных на странице

      echo     "  creator='".$creator."'	;\n" ;
      
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

<title>DarkMed Client Prescriptions View</title>
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

    var  i_set ;
    var  i_error ;
    var  i_remark ;

    var  a_plist_id ;


  function FirstField() 
  {
    var  password ;
    var  page_key ;


       i_set   =document.getElementById("Prescriptions") ;
       i_error =document.getElementById("Error") ;
       i_remark=document.getElementById("Remark") ;

	a_plist_id    =new Array() ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;
       page_key= Crypto_decode( page_key, password) ;
       
         i_remark.innerHTML=Crypto_decode(i_remark.innerHTML, page_key) ;

       for(i in a_plist_id) {
             a_plist_id[i]=Crypto_decode(a_plist_id[i], page_key) ;
                        AddListRow_tiles(i, a_plist_id[i]) ;
       }

         return true ;
  }

  function AddListRow_tiles(p_order, p_id)
  {
     var  i_frm_new ;


	  i_frm_new = document.createElement("iframe") ;
	  i_frm_new . src         ="prescription_pilot.php?Id="+p_id+"&Size=80"+"&SelfId=Iframe_"+p_order ;
	  i_frm_new . id          ="Iframe_"+p_order ;
//	  i_frm_new . seamless    = true ;
	  i_frm_new . height      ="82" ;
	  i_frm_new . width       ="122" ;
	  i_frm_new . scrolling   ="no" ;
	  i_frm_new . frameborder ="0" ;
	  i_frm_new . marginheight="0" ;
	  i_frm_new . marginwidth ="0" ;
	  i_set     . appendChild(i_frm_new) ;

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

  <div class="Error_CT" id="Error"></div>
  
  <div id="Prescriptions"></div>
  <br>
  <em id="Remark"></em> 

</body>

</html>
