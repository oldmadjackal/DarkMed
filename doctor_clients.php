<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Doctor_clients.php" ;

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

                        $session=$_GET ["Session"] ;
  if(!isset($session))  $session=$_POST["Session"] ;

  FileLog("START", "Session:".$session) ;

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

                     $sql="Select  distinct a.owner, a2.crypto, c.name_f, c.name_i, c.name_o, n.category".
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

<title>DarkMed Client Card</title>
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

    var  i_clients ;
    var  i_error ;
    var  password ;
    var  page_key ;
    var  user ;
    var  user_opt ;
    var  a_client_key ;
    var  a_client_f ;
    var  a_client_i ;
    var  a_client_o ;
    var  a_client_cat ;
    var  a_client_desc ;
    var  a_page_key ;
    var  a_page_title ;
    var  a_prsc_key ;
    var  a_prsc_title ;
    var  a_prsc_creator ;

    var  client_prv ;

  function FirstField() 
  {
    var  client ;
    var  page ;
    var  page_key ;

       i_clients=document.getElementById("Clients") ;
       i_error  =document.getElementById("Error") ;

       password=TransitContext("restore", "password", "") ;

             a_client_key  =new Array() ;
             a_client_f    =new Array() ;
             a_client_i    =new Array() ;
             a_client_o    =new Array() ;
             a_client_cat  =new Array() ;
             a_client_desc =new Array() ;

             a_page_key    =new Array() ;
             a_page_title  =new Array() ;
             a_prsc_key    =new Array() ;
             a_prsc_title  =new Array() ;
             a_prsc_creator=new Array() ;

               client_prv ="NONE" ;

<?php
            ProcessDB() ;
?>

        main_key=Crypto_decode(main_key, password) ;

    for(var elem in a_client_key)
    {
               a_client_cat[elem]=Crypto_decode(a_client_cat[elem], main_key) ;

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

    for(var elem in a_client_desc)
    {
         AddNewClient(elem) ;
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

  function AddNewClient(p_client)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_lnk_new ;
     var  i_dev_new ;
     var  i_txt_new ;


       i_row_new          =document.createElement("tr") ;
       i_row_new.className="table" ;

       i_lnk_new          =document.createElement("a") ;
       i_lnk_new.id       =p_client ;
       i_lnk_new.href     ="#" ;
       i_lnk_new.onclick  =function(e) { ShowPages(this.id) ; } ;
       i_txt_new          =document.createTextNode(a_client_desc[p_client]) ;
       i_lnk_new.appendChild(i_txt_new) ;

       i_col_new          =document.createElement("td") ;
       i_col_new.className="table" ;
       i_col_new.appendChild(i_lnk_new) ;
       i_row_new.appendChild(i_col_new) ;

       i_txt_new          =document.createTextNode(a_client_cat[p_client]) ;
       i_dev_new          =document.createElement("dev") ;
       i_dev_new.id       =p_client+"_category" ;
       i_dev_new.appendChild(i_txt_new) ;

       i_col_new          =document.createElement("td") ;
       i_col_new.id       =p_client+"_actions" ;
       i_col_new.className="table" ;
       i_col_new.appendChild(i_dev_new) ;
       i_row_new.appendChild(i_col_new) ;

       i_clients.appendChild(i_row_new) ;

    return ;         
  } 


  function ShowPages(p_client) 
  {
    var  i_client ;
    var  i_link_new ;
    var  i_view_new ;
    var  i_devv_new ;
    var  i_text_new ;
    var  i_roww_new ;
    var  i_list_new ;
    var  url ;
    var  f_check_save ;


    if(client_prv==p_client)  return ;

       f_check_save=parent.frames["details"].CheckSave ;
    if(f_check_save!=null)
     if(f_check_save('Check')!=null) 
      if (confirm("Данные по предыдущему пациенту не были сохранены. Остаться для сохранения?"))  return ;

    if(client_prv!="NONE")
    {
       i_list_new=document.getElementById(client_prv+"_pages") ;
       i_client  =document.getElementById(client_prv) ;
       i_client  .removeChild(i_list_new) ;
       i_list_new=document.getElementById(client_prv+"_prescriptions") ;
       i_client  =document.getElementById(client_prv+"_actions") ;
       i_client  .removeChild(i_list_new) ;
    }

              v_session=TransitContext("restore","session","") ;

       i_client        =document.getElementById(p_client) ;
       i_list_new      =document.createElement("ul") ;
       i_list_new.id   =p_client+"_pages" ;
       i_list_new.class="level2" ;

	  i_link_new        =document.createElement("a") ;
          i_link_new.id     =p_client+"_messages" ;
          i_link_new.href   ="messages_chat_lr.php?Session="+v_session+"&Sender="+p_client ;
          i_text_new        =document.createTextNode("Переписка") ;
          i_link_new.appendChild(i_text_new) ;

	  i_roww_new        =document.createElement("li") ;
          i_roww_new.appendChild(i_link_new) ;
          i_list_new.appendChild(i_roww_new) ;

	  i_roww_new        =document.createElement("br") ;
          i_list_new.appendChild(i_roww_new) ;

    for(var elem in a_page_title)
    {
         words=elem.split(':') ;
      if(words[1]==p_client)
      {
        if(words[0]=="0")  url="client_card.php" ;
        else               url="client_page_view.php" ;

	 i_link_new        =document.createElement("a") ;
          i_link_new.id     =elem ;
          i_link_new.href   =url+"?Session="+v_session+"&Owner="+p_client+"&Page="+words[0] ;
          i_text_new        =document.createTextNode(a_page_title[elem]) ;
          i_link_new.appendChild(i_text_new) ;

	 i_roww_new        =document.createElement("li") ;
          i_roww_new.appendChild(i_link_new) ;

          i_list_new.appendChild(i_roww_new) ;
      }
    }

	i_client  .appendChild(i_list_new) ;

	i_client        =document.getElementById(p_client+"_actions") ;
	i_devv_new      =document.createElement("dev") ;
	i_devv_new.id   =p_client+"_category" ;
	i_client  .appendChild(i_devv_new) ;

	i_list_new      =document.createElement("ul") ;
	i_list_new.id   =p_client+"_prescriptions" ;
	i_list_new.class="level2" ;

    if(user_opt.indexOf("Doctor")>=0)
    {
            url="client_prescr_edit_wrapper.php" ;
            
	i_link_new        =document.createElement("a") ;
	i_link_new.id     =p_client+"_add_prescription" ;
        i_link_new.href   ="javascript:"
                          +"parent.parent.frames['view'].location.assign('"
                          +url+"?Session="+v_session+"&Owner="+p_client+"&NewPage=1"
                          +"') ; " ;

	i_text_new        =document.createTextNode("Новое назначение") ;
	i_link_new.appendChild(i_text_new) ;


	i_roww_new        =document.createElement("li") ;
	i_roww_new.appendChild(i_link_new) ;
	i_list_new.appendChild(i_roww_new) ;
    }

	i_roww_new        =document.createElement("br") ;
	i_list_new.appendChild(i_roww_new) ;

    for(var elem in a_prsc_title)
    {
         words=elem.split(':') ;
      if(words[1]==p_client)
      {
        if(user==a_prsc_creator[elem])  url="client_prescr_edit_wrapper.php" ;
        else                            url="client_prescr_view.php" ;

          i_link_new        =document.createElement("a") ;
          i_link_new.id     =elem ;
          i_link_new.href   ="javascript:"
                            +"parent.parent.frames['view'].location.assign('"
                            +url+"?Session="+v_session+"&Owner="+p_client+"&Page="+words[0]
                            +"') ; " ;

         if(a_prsc_title[elem]=="")  a_prsc_title[elem]="Unknown" ;

          i_text_new        =document.createTextNode(a_prsc_title[elem]) ;
          i_link_new.appendChild(i_text_new) ;

	  i_roww_new        =document.createElement("li") ;
          i_roww_new.appendChild(i_link_new) ;

       if(user_opt.indexOf("Doctor")>=0)
       {
                url="client_prescr_view.php" ;

          i_view_new        =document.createElement("a") ;
          i_view_new.id     =elem ;
          i_view_new.href   =url+"?Session="+v_session+"&Owner="+p_client+"&Page="+words[0] ;
          i_text_new        =document.createTextNode(" (просмотр)") ;
          i_view_new.appendChild(i_text_new) ;

          i_roww_new.appendChild(i_view_new) ;
       }

          i_list_new.appendChild(i_roww_new) ;
      }
    }

       i_client  .appendChild(i_list_new) ;

     parent.frames["details"].location.assign("doctor_notes.php"+
                                              "?Session="+v_session+
                                              "&Client="+p_client) ;

     client_prv=p_client ;
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
        <b>СПИСОК ПАЦИЕНТОВ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <form onsubmit="return SendFields();" method="POST">
  </form>

  <table class="table" width="100%">
    <thead>
    </thead>
    <tbody id="Clients">
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    </tbody>
  </table>

</div>

</body>

</html>
