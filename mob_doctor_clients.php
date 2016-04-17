<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_Doctor_clients.php" ;

  require("stdlib.php") ;

//============================================== 
//  Работа с БД

function ProcessDB() {

  global  $glb_options_a ;
  
//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение параметров

                               $session   =$_GET ["Session"] ;

  if(isset($_GET ["Client"]))  $ext_client=$_GET ["Client"] ;

  if(isset($_POST["Client"]))
  {         
                               $client    =$_POST["Client"] ;
                               $category  =$_POST["Category"] ;
                               $remark    =$_POST["Remark"] ;
  }

        FileLog("START", "Session:".$session) ;

  if(isset($ext_client))
        FileLog("",      " ExtClient:".$ext_client) ;

  if(isset($client))
  {
        FileLog("",      "    Client:".$client) ;
        FileLog("",      "  Category:".$category) ;
        FileLog("",      "    Remark:".$remark) ;
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

       $user_=$db->real_escape_string($user ) ;

//--------------------------- Приведение данных

  if(isset($client))
  {
          $client_  =$db->real_escape_string($client) ;
          $category_=$db->real_escape_string($category) ;
          $remark_  =$db->real_escape_string($remark) ;
  }
//--------------------------- Сохранение данных со страницы

  if(isset($client))
  {
                       $sql="Update doctor_notes".
                            " Set   category='$category_'".
                            "      ,remark  ='$remark_'".
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
//--------------------------- Указание конкретного клиента

  if(isset($ext_client) ||
     isset($client)       ) {

       if(!isset($ext_client))   $ext_client=$client ;
     
        echo  "  ext_client='".$ext_client."' ;  \n  " ;
  }
//--------------------------- Получение основного ключа шифрования

                       $sql="Select crypto ".
                            "  From `access_list`".
                            " Where `owner`='$user_' ".
                            "  and  `login`='$user_' ".
                            "  and  `page` =  0 " ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения ключа доступа") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   user    ='" .$user."' ;                  	\n" ;
      echo     "   user_opt='" .$glb_options_a["user"]."' ;	\n" ;
      echo     "   main_key='" .$fields[0]."' ;          	\n" ;

//--------------------------- Формирование списка пациентов

                     $sql="Select  distinct a.owner, a2.crypto, c.name_f, c.name_i, c.name_o, n.category, n.remark".
			  "  From             `access_list`      a".
                          "        Inner join `client_page_main` c on c.owner=a.owner".
                          "        Inner join `doctor_notes` n on n.owner=a.login and n.client=a.owner".
                          "   left outer join `access_list` a2 on a.owner=a2.owner and a.login=a2.login and a2.page=0".
			  " Where  a.owner <> a.login".
			  "  and   a.login =  '$user_'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES owners ... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка пациентов") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No clients detected") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo     "               client =\"".$fields[0]."\" ;	\n" ;
       echo     "  a_client_key[client]=\"".$fields[1]."\" ;	\n" ;
       echo     "  a_client_f  [client]=\"".$fields[2]."\" ;	\n" ;
       echo     "  a_client_i  [client]=\"".$fields[3]."\" ;	\n" ;
       echo     "  a_client_o  [client]=\"".$fields[4]."\" ;	\n" ;
       echo     "  a_client_cat[client]=\"".$fields[5]."\" ;	\n" ;
       echo     "  a_client_rem[client]=\"".$fields[6]."\" ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Формирование списка страниц пациентов

                     $sql="Select a.owner, a.page, p.type, a.crypto, p.title, p.creator".
			  "  From             access_list  a".
                          "   left outer join client_pages p on a.owner=p.owner and a.page=p.page ".
			  " Where  a.owner <> a.login".
			  "  and   a.login = '$user_'".
                          " Order by a.owner, a.page" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGES pages... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка страниц пациентов") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No clients pages detected") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

	       echo     "                 client ='".$fields[0]."' ;	\n" ;
	       echo     "                 page   ='".$fields[1]."' ;	\n" ;
	       echo     "                 page   =page+':'+client ;	\n" ;
        if($fields[2]==""      ||
           $fields[2]=="client"  )
        {
	       echo     "  a_page_key    [page]  ='".$fields[3]."' ;	\n" ;
	       echo     "  a_page_title  [page]  ='".$fields[4]."' ;	\n" ;
        }
        if($fields[2]=="prescription")
        {
	       echo     "  a_prsc_key    [page]  ='".$fields[3]."' ;	\n" ;
	       echo     "  a_prsc_title  [page]  ='".$fields[4]."' ;	\n" ;
	       echo     "  a_prsc_creator[page]  ='".$fields[5]."' ;	\n" ;
        }
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

<title>DarkMed Mobile Doctors Clients</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css") ;
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_clients ;
    var  i_details ;
    var  i_editor ;
    var  i_category ;
    var  i_remark ;
    var  i_client ;
    var  i_error ;
    var  password ;
    var  main_key ;
    var  page_key ;
    var  ext_client ;
    var  user ;
    var  user_opt ;
    var  a_client_key ;
    var  a_client_f ;
    var  a_client_i ;
    var  a_client_o ;
    var  a_client_cat ;
    var  a_client_rem ;
    var  a_client_desc ;
    var  a_page_key ;
    var  a_page_title ;
    var  a_prsc_key ;
    var  a_prsc_title ;
    var  a_prsc_creator ;

    var  v_session ;


  function FirstField() 
  {
    var  client ;
    var  page ;
    var  page_key ;


    	 v_session=TransitContext("restore","session","") ;

       i_clients =document.getElementById("Clients") ;
       i_details =document.getElementById("Details") ;
       i_editor  =document.getElementById("Editor") ;
       i_client  =document.getElementById("Client") ;
       i_category=document.getElementById("Category") ;
       i_remark  =document.getElementById("Remark") ;
       i_error   =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

             a_client_key  =new Array() ;
             a_client_f    =new Array() ;
             a_client_i    =new Array() ;
             a_client_o    =new Array() ;
             a_client_cat  =new Array() ;
             a_client_desc =new Array() ;
             a_client_rem  =new Array() ;

             a_page_key    =new Array() ;
             a_page_title  =new Array() ;
             a_prsc_key    =new Array() ;
             a_prsc_title  =new Array() ;
             a_prsc_creator=new Array() ;

<?php
            ProcessDB() ;
?>

        main_key=Crypto_decode(main_key, password) ;

    for(var elem in a_client_key)
    {
               a_client_cat[elem]=Crypto_decode(a_client_cat[elem], main_key) ;
               a_client_rem[elem]=Crypto_decode(a_client_rem[elem], main_key) ;

	if(a_client_key[elem]!="")
        {
                   page_key      =Crypto_decode(a_client_key[elem], password) ;
               a_client_f  [elem]=Crypto_decode(a_client_f  [elem], page_key) ;
               a_client_i  [elem]=Crypto_decode(a_client_i  [elem], page_key) ;
               a_client_o  [elem]=Crypto_decode(a_client_o  [elem], page_key) ;

            a_client_desc[elem]=a_client_f[elem]+" "+a_client_i[elem]+" "+a_client_o[elem]+" ("+elem+")" ;
        }
        else
        {
            a_client_desc[elem]=elem ;
        }
    }

    for(var elem in a_page_key)
    {
	if(a_page_title[elem]!="")
        {
                     page_key =Crypto_decode(a_page_key  [elem], password) ;
            a_page_title[elem]=Crypto_decode(a_page_title[elem], page_key) ;
        }
        else
        {
            a_page_title[elem]="Главная страница" ;
        }
    }

    for(var elem in a_prsc_key)
    {
                     page_key =Crypto_decode(a_prsc_key  [elem], password) ;
            a_prsc_title[elem]=Crypto_decode(a_prsc_title[elem], page_key) ;
    }

    for(var elem in a_client_desc)  AddNewClient(elem) ;

     parent.frames["details"].location.replace("mob_doctor_clients_footer.php?Session="+v_session) ;

     i_clients.focus() ;

     if(ext_client!=null)  ShowPages(ext_client) ;
     
         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

	error_text=""
     
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

       i_category.value=Crypto_encode(i_category.value, main_key) ;
       i_remark  .value=Crypto_encode(i_remark  .value, main_key) ;


                         return true ;         
  } 

  function AddNewClient(p_client)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;

     
       i_row_new          =document.createElement("tr") ;
       i_row_new.className="table" ;
       i_row_new.id       ="Client_" + p_client ;
       i_row_new.onclick  =function(e) { ShowPages(p_client) ; } ;

       i_col_new          =document.createElement("td") ;
       i_col_new.className="table" ;
       i_txt_new          =document.createTextNode(a_client_desc[p_client]) ;
       i_col_new.appendChild(i_txt_new) ;
       i_row_new.appendChild(i_col_new) ;

       i_col_new          =document.createElement("td") ;
       i_col_new.className="table" ;
       i_txt_new          =document.createTextNode(a_client_cat[p_client]) ;
       i_col_new.appendChild(i_txt_new) ;
       i_row_new.appendChild(i_col_new) ;

       i_clients.appendChild(i_row_new) ;

    return ;         
  } 

  function ShowPages(p_client) 
  {
    var  i_list ;
    var  i_link_new ;
    var  i_text_new ;
    var  i_roww_new ;
    var  url ;


             ext_client=p_client ;

       i_clients.hidden=true ;
       i_details.hidden=false ;

       document.getElementById("FormTitle"   ).innerHTML=a_client_desc[p_client] ;
       document.getElementById("CategoryView").innerHTML=a_client_cat [p_client] ;
       document.getElementById("RemarkView"  ).innerHTML=a_client_rem [p_client] ;
                                    i_category.value    =a_client_cat [p_client] ;
                                    i_remark  .value    =a_client_rem [p_client] ;
                                    i_client  .value    =              p_client ;

       i_list=document.getElementById("ClientsPages") ;

    for(var elem in a_page_title)
    {
         words=elem.split(':') ;
      if(words[1]==p_client)
      {
        if(words[0]=="0")  url="client_card.php" ;
        else               url="client_page.php" ;

	  i_link_new        =document.createElement("a") ;
          i_link_new.id     =elem ;
          i_link_new.href   =url+"?Session="+v_session+"&Owner="+p_client+"&Page="+words[0] ;
          i_text_new        =document.createTextNode(a_page_title[elem]) ;
          i_link_new.appendChild(i_text_new) ;

	  i_roww_new        =document.createElement("li") ;
          i_roww_new.appendChild(i_link_new) ;

          i_list    .appendChild(i_roww_new) ;
      }
    }

       i_list=document.getElementById("PrescriptionsPages") ;

    for(var elem in a_prsc_title)
    {
         words=elem.split(':') ;
      if(words[1]==p_client)
      {
                 url="client_prescr_view.php" ;

          i_link_new=document.createElement("a") ;
          i_link_new.href=url+"?Session="+v_session+"&Owner="+p_client+"&Page="+words[0] ;
          i_text_new=document.createTextNode(a_prsc_title[elem]) ;
          i_link_new.appendChild(i_text_new) ;

	  i_roww_new=document.createElement("li") ;
          i_roww_new.appendChild(i_link_new) ;

          i_list    .appendChild(i_roww_new) ;
      }
    }
    
     parent.frames["details"].location.replace("mob_doctor_clients_footer.php?Session="+v_session+"&GoBack="+p_client) ;

    return ;
  }

  function GoToMail()
  {
	parent.frames["section"].location.replace("mob_chat.php?Session="+v_session+"&Sender="+ext_client) ;
  } 

  function Edit()
  {
       i_details.hidden=true ;
       i_editor .hidden=false ;
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
        <input type="button" value="?"        onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" value="!" hidden onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b id="FormTitle">СПИСОК ПАЦИЕНТОВ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>

  <table class="table" width="100%">
    <tbody id="Clients">
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    </tbody>
  </table>


  <div hidden id="Details"> 

    <div class="fieldC"> 
      <input type="button" value="Править"   onclick="Edit();">
      <input type="button" value="Переписка" onclick="GoToMail();">
      <br>
      <br>
      <div id="CategoryView"></div>
      <br>
      <div><i id="RemarkView"></i></div>
      <hr>
      <div><b>Данные клиента</b></div>
      <ul id="ClientsPages"></ul>
      <hr>
      <div><b>Назначения</b></div>
      <ul id="PrescriptionsPages"></ul>
    </div>
  
  </div>

  <form onsubmit="return SendFields();" method="POST">
  
  <div hidden id="Editor"> 

    <div class="fieldC"> 
      <input type="hidden" name="Client"     id="Client">
      <input type="submit" value="Сохранить" id="Save">
      <br>
      <br>
      <div><b>Краткий комментарий</b></div>
      <textarea cols=32 rows=3 maxlength=512 wrap="soft" name="Category" id="Category"> </textarea>
      <br>
      <br>
      <div><b>Заметки</b></div>
      <textarea cols=32 rows=7 maxlength=512 wrap="soft" name="Remark" id="Remark"> </textarea>
    </div>
  
  </div>

  </form>

</body>

</html>
