<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_Client_card.php" ;

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

                          $owner=$_GET ["Owner"] ;

  FileLog("START", "Session:".$session) ;
  FileLog("",      "  Owner:".$owner) ;

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
//--------------------------- Определение владельца страницы

  if(!isset($owner))  $owner =$user ; 

  if($owner!=$user)  $read_only=true ; 
  else               $read_only=false ; 

                      $owner_=$db->real_escape_string($owner) ;
                      $user_ =$db->real_escape_string($user ) ;

//--------------------------- Формирование списка дополнительных страницы пациента

                     $sql="Select p.page, p.title, a.crypto".
			  "  From client_pages p, access_list a".
			  " Where p.owner='$user'".
			  "  and  p.page > 0".
			  "  and  a.owner='$user_'".
			  "  and  a.login='$user_'".
			  "  and  a.page =p.page".
                          "  and  p.type ='client'".
                          " Order by p.page" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES(type ='Client')... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса разделов") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No additional pages detected") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

		$link_href="mob_client_page.php?Session=".$session."&Page=".$fields[0] ;

       echo     "         link_key     ='".$fields[2]."' ;			\n" ;
       echo     "         link_key     =Crypto_decode(link_key, password) ;	\n" ;
       echo     "         link_text    ='".$fields[1]."' ;			\n" ;
       echo     "         link_text    =Crypto_decode(link_text, link_key) ;	\n" ;
       echo     "	i_list_new     =document.createElement('li') ;		\n" ;
       echo     "	i_link_new     =document.createElement('a') ;		\n" ;
       echo     "       i_link_new.href='".$link_href."' ;			\n" ;
       echo     "       i_text_new     =document.createTextNode(link_text) ;	\n" ;
       echo     "       i_link_new.appendChild(i_text_new) ;			\n" ;
       echo     "       i_list_new.appendChild(i_link_new) ;			\n" ;
       echo     "       i_pages   .appendChild(i_list_new) ;			\n" ;
       echo     "	i_text_new     =document.createElement('br') ;		\n" ;
       echo     "       i_pages   .appendChild(i_text_new) ;			\n" ;
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
    echo  "return ;" ;
}

//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed-Mobile Client Data</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css")
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_pages ;
    var  i_error ;
    var  password ;

  function FirstField() 
  {
    var  i_list_new ;
    var  i_link_new ;
    var  i_text_new ;
    var  link_key ;
    var  link_text ;


       i_pages =document.getElementById("Pages") ;
       i_error =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

     parent.frames['details'].location.replace('mob_footer_menu.html') ;

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

  function NewPage() 
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

        location.assign("mob_client_page.php"+"?Session="+v_session+"&NewPage=1") ;
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
        <input type="button" hidden value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b>ОБСЛЕДОВАНИЯ И АНАЛИЗЫ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">
  <br>
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="fieldC"> 
        <input type="button" value="Создать новый раздел" onclick=NewPage()>
      </td>
    </tr>
    </tbody>
  </table>

  <br>
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td width="10%"></td>
      <td>
        <ul class="menu" name="Pages" id="Pages">
        </ul>
      </td>
      <td width="10%"></td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
