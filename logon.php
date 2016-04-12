<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Logon.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных данных в БД

function RegistryDB() {

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение и анализ параметров

   $login   =$_POST["Login"   ] ;
   $password=$_POST["Password"] ;

     $completeness=0 ;

  if(isset($login   ))  $completeness++ ;
  if(isset($password))  $completeness++ ;

  if($completeness==0)  return ;

  if($completeness==0)  FileLog("START", "HandShake") ;
  else                  FileLog("START", "Login:".$login." Password:".$password) ;

//--------------------------- Вывод данных на экран

    echo     "   i_login.value='" .$login   ."' ;	\n" ;
    echo     "i_password.value='" .$password."' ;	\n" ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Верификация пользователя

   $login   =$db->real_escape_string($login   ) ;
   $password=$db->real_escape_string($password) ;

                     $sql="Select options from users Where Login='$login' and Password='$password'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select... : ".$db->error) ;
                     $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка проверки пользователя") ;
                         return ;
  }
  if($res->num_rows==0) {
                    $res->free() ;
                     $db->close() ;
          FileLog("CANCEL", "Login or password failed") ;
         ErrorMsg("Несоответствие логина и пароля пользователя") ;
                         return ;
  }

	      $fields=$res->fetch_row() ;
             $options=$fields[0] ;

                    $res->free() ;

//--------------------------- Выделение атрибутов пользователя

    $options_a=OptionsToArray($options) ;

     $user_type=$options_a["user"] ;

//--------------------------- Регистрация сессии

           $session=GetRandomString(16) ;

                     $sql="Insert into sessions(Login, Session) values('$login','$session')" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Insert SESSION... : ".$db->error) ;
                     $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
  }

     $db->commit() ;

        FileLog("", "Session record successfully inserted") ;

//--------------------------- Удаление старых сессий

                     $sql="Delete from sessions where started<Date_Sub(Now(), interval 24 hour)" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Insert DELETE... : ".$db->error) ;
          InfoMsg("Ошибка на сервере. <br>Детали: ошибка очистки таблицы сессий") ;
  }

     SuccessMsg($session) ;

//--------------------------- Запрос списка непрочитанных релизов

                     $sql="Select id, date(created), title, notes, user".
                          "  From releases r left outer join releases_read m on r.id=m.release_id and m.user='$login'".
                          " Where user is null".
                          "  and (types like '%$user_type%' or types is null)".
                          " Order by created" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select RELEASES... : ".$db->error) ;
          InfoMsg("Ошибка на сервере. <br>Детали: ошибка получения списка релизов") ;
  }
  else {

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "      i_rl_intro.hidden=false ;								\n" ;
       echo "   AddRelease(".$fields[0].", '".$fields[1]."', '".$fields[2]."', '".$fields[3]."') ;	\n" ;
     }

                    $res->free() ;
  }

//--------------------------- Запрос наличия непрочитанных сообщений

                    $msg_flag="0" ;

                     $sql="Select count(*)".
                          "  From messages".
                          " Where receiver='$login'".
                          "  and  `read` is null" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
          InfoMsg("Ошибка на сервере. <br>Детали: ошибка опеделения наличия непрочитанных сообщений") ;
  }
  else {
	      $fields=$res->fetch_row() ;

                $msg_flag=$fields[0] ;
           
                    $res->free() ;
  }
//--------------------------- Изменение конфигурации главного меню

        if($user_type=="Doctor"  )  echo  "parent.frames['menu'].ShowDoctor() ; " ;
   else if($user_type=="Executor")  echo  "parent.frames['menu'].ShowExecutor() ; " ;
   else                             echo  "parent.frames['menu'].ShowClient() ; " ;

//--------------------------- Автоматический переход на формы

   if($msg_flag=="0")
   {
      if($user_type=="Doctor"  ||
         $user_type=="Executor"  )
      {
      }
      else
      {
          echo  "  location.assign('client_card.php'+'?Session=".$session."') ;	\n" ;
      }
   }
   else
   {
          echo  "  location.assign('messages.php'+'?Session=".$session."') ;	\n" ;
   }
//--------------------------- Завершение

     $db->commit() ;
     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  Выдача сообщения об успешной регистрации

function SuccessMsg($session) {

    echo  "TransitContext('save', 'session', '".$session."') ;	\n" ;

    echo  "i_error.style.color='green' ;				\n" ;
	
	echo "
     var  v_session = TransitContext('restore','session','') ; 
	 parent.frames['title'].changeHiddenAuthBtns(v_session); ";

    echo  "i_error.innerHTML  ='Авторизация успешно пройдена!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Logon</title>
<meta http-equiv="Content-Type" content="text/html" charset="windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

<?php
  require("common.inc") ;
  require("md5.inc") ;
?>

    var  i_table ;
    var  i_login ;
    var  i_password ;
    var  i_releases ;
    var  i_rl_intro ;
    var  i_error ;    

  function FirstField() 
  {
       i_table   =document.getElementById("Fields") ;
       i_login   =document.getElementById("Login") ;
       i_password=document.getElementById("Password") ;
       i_releases=document.getElementById("Releases") ;
       i_rl_intro=document.getElementById("Releases_intro") ;
       i_error   =document.getElementById("Error") ;

       i_login.focus() ;

<?php
            RegistryDB() ;
?>

       i_password.value=TransitContext("restore", "password", "") ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

       i_table.rows[0].cells[0].style.color="black"   ;
       i_table.rows[1].cells[0].style.color="black"   ;

        error_text="" ;
     
     if(i_login.value=="") {
       i_table.rows[0].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Логин'" ;
     }

     if(i_password.value=="") {
       i_table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Пароль'" ;
     }

     if(error_text=="") {

       TransitContext("save", "password", i_password.value) ;

	i_password.value=MD5(i_password.value) ;
	i_password.value=i_password.value.substr(1,4) ;
     }

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

 
      return true ;         
  } 

  function AddRelease(p_id, p_date, p_name, p_link)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_lnk_new ;
     var  i_shw_new ;


       i_row_new = document.createElement("tr") ;
       i_row_new . id     ='Release_'+p_id ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "field" ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Прочитано" ;
       i_shw_new . id     ='Mark_'+p_id ;
       i_shw_new . onclick= function(e) {  MarkRead(p_id) ;  }
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "fieldC" ;
       i_txt_new = document.createTextNode(p_date) ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "fieldL" ;
       i_lnk_new = document.createElement("a") ;
       i_lnk_new . href="#" ;
       i_lnk_new . onclick= function(e) {
					   window.open("releases/"+p_link) ;
					} ;
       i_txt_new = document.createTextNode(p_name) ;
       i_lnk_new . appendChild(i_txt_new) ;
       i_col_new . appendChild(i_lnk_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_releases. appendChild(i_row_new) ;

    return ;         
  }

  function MarkRead(p_id)
  {
    var  i_release ;
    var  v_session ;

         i_release=document.getElementById("Release_"+p_id) ;
         i_release.style.textDecoration="line-through" ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["details"].location.assign("z_release_markread.php?Session="+v_session+
                                                                   "&Release="+p_id) ;
  }

//-->
</script>


</head>

<body onload="FirstField();">

<noscript>
</noscript>

<dev class="inputF">

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
        <b>АВТОРИЗАЦИЯ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> Логин </td>
      <td> <input type="text" size=20 name="Login" id="Login"> </td>
    </tr>
    <tr>
      <td class="field"> Пароль </td>
      <td> <input type="password" size=20 name="Password" id="Password"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Войти"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    </tbody>
  </table>

  <br>
  <div class="fieldC" hidden id="Releases_intro"> 
    <b>Для Портала выпущены обновления.</b>
    <br>
    Для просмотра содержания обновления кликните по его названию - описание откроется в соседней вкладке.
    <br>
    Полный список обновлений можно просмотреть на вкладке "Как пользоваться порталом?".
  </div>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Releases">
      <tr> 
       <td width="12%"> </td>
       <td width="12%"> </td>
       <td> </td>
      </tr> 
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
