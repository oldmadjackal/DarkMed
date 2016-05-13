<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Messages.php" ;

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

  FileLog("START", "    Session:".$session) ;

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

//--------------------------- Запрос ключа подписи получателя

                     $sql="Select u.sign_s_key".
			  "  From `users` u".
			  " Where u.login  ='$user_'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select USERS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса ключа подписи") ;
                         return ;
  }
  else
  {      
	      $fields=$res->fetch_row() ;

       echo "    receiver_key='".$fields[0]."' ;			\n" ;
       echo "    receiver_key=Crypto_decode(receiver_key, password) ;	\n" ;
  }

     $res->close() ;

//--------------------------- Формирование списка сообщений

  if($glb_options_a["user"]=="Doctor"   ||
     $glb_options_a["user"]=="Executor"   )
  {
                     $sql="Select m.* from (".
			  "Select m1.id, m1.sender, m1.type, t1.name, m1.text, u1.sign_p_key,".
			  "       c1.name_f, c1.name_i, c1.name_o, a1.crypto, m1.sent, m1.done".
			  "  From messages m1 ".
                          "       inner join ref_messages_types t1 on t1.code =m1.type and t1.language='RU' ".
                          "       inner join users              u1 on u1.login=m1.sender ".
                          "       inner join client_page_main   c1 on c1.owner=m1.sender ".
                          "       left outer join access_list   a1 on a1.owner=c1.owner and a1.login=m1.receiver and a1.page=0 ".
			  " Where m1.receiver='$user_'".
			  "  and  m1.read is null".
			  " union ".
			  "Select m2.id, m2.sender, m2.type, t2.name, m2.text, u2.sign_p_key,".
			  "       d2.name_f, d2.name_i, d2.name_o, 'nocrypt', m2.sent, m2.done".
			  "  From messages m2 ".
                          "       inner join ref_messages_types t2 on t2.code =m2.type and t2.language='RU' ".
                          "       inner join users              u2 on u2.login=m2.sender ".
                          "       inner join doctor_page_main   d2 on d2.owner=m2.sender ".
			  " Where m2.receiver='$user_'".
			  "  and  m2.read is null".
                          ") m".
                          " Order by m.id desc" ;
  }
  else
  {
                     $sql="Select m.id, m.sender, m.type, t.name, m.text, u.sign_p_key,".
			  "       d.name_f, d.name_i, d.name_o, 'nocrypt', m.sent, m.done".
			  "  From messages m ".
                          "       inner join ref_messages_types t on t.code=m.type and  t.language='RU' ".
                          "       inner join users u on u.login=m.sender ".
                          "       inner join doctor_page_main d on d.owner=m.sender ".
			  " Where m.receiver='$user_'".
			  "  and  m.read is null".
                          " Order by m.id desc" ;
  }

     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select MESSAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка сообщений") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "No messages detected") ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "  sender_key     ='".$fields[ 5]."' ;					\n" ;
       echo "    msg_id       ='".$fields[ 0]."' ;					\n" ;
       echo "    msg_sender_id='".$fields[ 1]."' ;					\n" ;
       echo "    msg_type     ='".$fields[ 2]."' ;					\n" ;
       echo "    msg_type_    ='".$fields[ 3]."' ;					\n" ;
       echo "    msg_text     ='".$fields[ 4]."' ;					\n" ;
       echo "    msg_text     =Sign_decode(msg_text, sender_key, receiver_key) ;	\n" ;
       echo "    msg_sender   ='".$fields[ 1]."' ;					\n" ;
       echo "    msg_date     ='".$fields[10]."' ;					\n" ;
       echo "    msg_done     ='".$fields[11]."' ;					\n" ;

       echo "  a_msg_id    [".$i."]=msg_id  ;        \n" ;
       echo "  a_msg_sender[".$i."]=msg_sender_id ;  \n" ;
       echo "  a_msg_type  [".$i."]=msg_type ;       \n" ;
       echo "  a_msg_text  [".$i."]=msg_text ;       \n" ;
       echo "  a_msg_done  [".$i."]=msg_done ;       \n" ;

      if($fields[9]=="nocrypt")
      {
       echo "     msg_sender  ='".$fields[ 6]."'+' '	\n" ;
       echo "                 +'".$fields[ 7]."'+' '	\n" ;
       echo "                 +'".$fields[ 8]."' ;	\n" ;
      }
      else
      if($fields[9]!="")
      {
       echo "     msg_name_key=Crypto_decode('".$fields[9]."', password) ;	\n" ;
       echo "     msg_sender  =Crypto_decode('".$fields[6]."', msg_name_key)+' '	\n" ;
       echo "                 +Crypto_decode('".$fields[7]."', msg_name_key)+' '	\n" ;
       echo "                 +Crypto_decode('".$fields[8]."', msg_name_key) ;	\n" ;
      }	

       echo "  AddNewMessage(msg_id, msg_sender_id, msg_sender, msg_type, msg_type_, msg_text, msg_date) ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Формирование списка установленных связей

  if($glb_options_a["user"]=="Doctor"   ||
     $glb_options_a["user"]=="Executor"   )
  {
                       $sql="Select distinct owner".
	  		    "  From access_list ".
			    " Where login='$user_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select ACCESS_LIST... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка установленных связей") ;
                         return ;
    }
    else
    {  

      for($i=0 ; $i<$res->num_rows ; $i++)
      {
	      $fields=$res->fetch_row() ;

        echo "  a_access['".$fields[0]."']='1' ;   \n" ;
      }
      
         $res->close() ;
    }
  }
//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;  \n" ;
    echo  "i_error.innerHTML  ='".$text."' ;  \n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color='green' ;   \n" ;
    echo  "i_error.innerHTML  ='Доступ к указанным страницам предоставлен.' ;  \n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Messages InBox</title>
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
    var  i_error ;
    var  password ;

    var  a_msg_id  ;
    var  a_msg_sender ;
    var  a_msg_type  ;
    var  a_msg_text  ;
    var  a_msg_done  ;
    var  a_access  ;


  function FirstField() 
  {
    var  receiver_key ;
    var  sender_key ;
    var  msg_text ;

       i_table=document.getElementById("Fields") ;
       i_pages=document.getElementById("Pages") ;
       i_error=document.getElementById("Error") ;

       a_msg_id    =new Array() ;
       a_msg_sender=new Array() ;
       a_msg_type  =new Array() ;
       a_msg_text  =new Array() ;
       a_msg_done  =new Array() ;
       a_access    =new Array() ;

         password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

          ProcessNext() ;

         return true ;
  }

  function SendFields() 
  {
     var  i_page ;
     var  i_doctor ;
     var  doctor_key ;
     var  error_text ;


	i_letter.value="" ;
            error_text="Не задано ни одной страницы для доступа" ;

        i_error.style.color="red" ;
        i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;         
  } 

  function AddNewMessage(p_id, p_sender_id, p_sender, p_type, p_type_desc, p_text, p_date)
  {
     var  i_messages ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_del_new ;


      words=p_date.split(" ") ;
     ddmmyy=words[0].split("-") ;
     p_date=words[1]+" "+ddmmyy[2]+"."+ddmmyy[1]+"."+ddmmyy[0] ;

       i_messages= document.getElementById("Messages") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "Table_LT " ;
       i_row_new . id        = "Msg_"+p_id ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_date) ;
       i_col_new . className = "Table_LT " ;
       i_col_new . onclick= function(e) {  ViewDetails(p_id, p_type) ;  } ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_sender) ;
       i_col_new . className = "Table_LT " ;
       i_col_new . onclick= function(e) {  ViewDetails(p_id, p_type) ;  } ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_type_desc) ;
       i_col_new . className = "Table_LT " ;
       i_col_new . onclick= function(e) {  ViewDetails(p_id, p_type) ;  } ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_text) ;
       i_col_new . className = "Table_LT " ;
       i_col_new . onclick= function(e) {  ViewDetails(p_id, p_type) ;  } ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="X" ;
       i_del_new . id     ="Delete_"+p_id ;
       i_del_new . onclick= function(e) {  MarkRead(p_id) ;  } ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "Table_LT " ;
       i_col_new . appendChild(i_del_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_messages.appendChild(i_row_new) ;

    return ;         
  } 

  
    var  msg_idx=-1 ;
    var  a_msg_groups ;
    var  action="none" ;

  function ProcessNext()
  {
    var  v_session ;
    var  v_form ;


    if(msg_idx==-1)
    {
         a_msg_groups=new Array() ;
    }

    if(action.indexOf("read")>=0)
    {
       document.getElementById("Msg_"+a_msg_id[msg_idx]).hidden=true ;
    }
    
     for(msg_idx++ ; msg_idx<a_msg_id.length ; msg_idx++)
     {
                action="none" ;

         if(a_msg_type[msg_idx]=="CLIENT_PRESCRIPTIONS_ALERT") 
         {
               if(a_msg_groups[a_msg_text[msg_idx]]==null)
               {
                  if(a_msg_done[msg_idx]!="Y")  action="execute" ;

                    a_msg_groups[a_msg_text[msg_idx]]="1" ;                          
               }
               else
               {
                          action="execute,read" ;
               }
         }
         if(a_msg_type[msg_idx]=="CLIENT_ACCESS_INVITE") 
         {
            if(a_access[a_msg_sender[msg_idx]]!=null)
            {
//                  action="execute" ;
            }
         }
         if(a_msg_type[msg_idx]=="CLIENT_ACCESS_PAGES") 
         {
            if(a_access[a_msg_sender[msg_idx]]!=null)
            {
               if(a_msg_done[msg_idx]!="Y")  action="execute" ;
            }
         }

         if(action!="none")
         {
                ViewDetails(a_msg_id[msg_idx], a_msg_type[msg_idx], action) ;

                  i_error.style.color='blue' ;
                  i_error.innerHTML  ='Подождите - идет автоматическая обработка полученных сообщений' ;

                    return ;
         }
     }

                  i_error.innerHTML='' ;

       if(a_msg_id[0]!=null)  ViewDetails(a_msg_id[0], a_msg_type[0]) ;
  }
  
    var  selected=null ; 
  
  function ViewDetails(p_id, p_type, p_action)
  {
    var  v_session ;
    var  v_form ;


   if(selected!=null)
   {
                    cols=selected.getElementsByTagName('td') ;
     for(var i=0; i<cols.length; i++)  cols[i].className="Table_LT" ;
   }

                selected=document.getElementById("Msg_"+p_id) ;
                    cols=selected.getElementsByTagName('td') ;
     for(var i=0; i<cols.length; i++)  cols[i].className="TableSelected_LT" ;

	 v_session=TransitContext("restore","session","") ;

                                              v_form="message_details_any.php" ;
    if(p_type=="CLIENT_ACCESS_INVITE"      )  v_form="message_details_invite.php" ;
    if(p_type=="CLIENT_ACCESS_PAGES"       )  v_form="message_details_access.php" ;
    if(p_type=="CLIENT_PRESCRIPTIONS_ALERT")  v_form="message_details_prescr.php" ;
    if(p_type=="CHAT_MESSAGE"              )  v_form="message_details_simple.php" ;

    if(p_action==null)    
	  parent.frames["details"].location.assign(v_form+"?Session="+v_session+"&Message="+p_id) ;
    else  parent.frames["details"].location.assign(v_form+"?Session="+v_session+"&Message="+p_id+"&Action="+p_action) ;
  }

  function MarkRead(p_id)
  {
    var  i_message ;
    var  v_session ;

         i_message=document.getElementById("Msg_"+p_id) ;
         i_message.style.textDecoration="line-through" ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["details"].location.assign("z_message_markread.php?Session="+v_session+"&Message="+p_id) ;
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
        <input type="button" class="HelpButton"     value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input type="button" class="CallBackButton" value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="FormTitle"> 
        <b>ВХОДЯЩИЕ СООБЩЕНИЯ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <p class="Error_CT" id="Error"></p>
  
  <form onsubmit="return SendFields();" method="POST">

  <table width="100%">
    <thead>
    </thead>
    <tbody id="Messages">
    </tbody>
  </table>

  </form>

</body>

</html>
