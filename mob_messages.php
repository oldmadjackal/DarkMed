<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_Messages.php" ;

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

  if($glb_options_a["user"]=="Doctor"  ||
     $glb_options_a["user"]=="Executor"  )
  {
                     $sql="Select m.* from (".
			  "Select m1.id, m1.sender, m1.type, t1.name, m1.text, m1.details, u1.sign_p_key,".
			  "       c1.name_f, c1.name_i, c1.name_o, a1.crypto, m1.done".
			  "  From messages m1 ".
                          "       inner join ref_messages_types t1 on t1.code =m1.type and t1.language='RU' ".
                          "       inner join users              u1 on u1.login=m1.sender ".
                          "       inner join client_page_main   c1 on c1.owner=m1.sender ".
                          "       left outer join access_list   a1 on a1.owner=c1.owner and a1.login=m1.receiver and a1.page=0 ".
			  " Where m1.receiver='$user_'".
			  "  and  m1.read is null".
			  " union ".
			  "Select m2.id, m2.sender, m2.type, t2.name, m2.text, m2.details, u2.sign_p_key,".
			  "       d2.name_f, d2.name_i, d2.name_o, 'nocrypt', m2.done".
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
                     $sql="Select m.id, m.sender, m.type, t.name, m.text, m.details, u.sign_p_key,".
			  "       d.name_f, d.name_i, d.name_o, 'nocrypt', m.done".
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

       echo "  messages_cnt=".$res->num_rows." ;					\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "  sender_key     ='".$fields[ 6]."' ;					\n" ;
       echo "    msg_id       ='".$fields[ 0]."' ;					\n" ;
       echo "    msg_sender_id='".$fields[ 1]."' ;					\n" ;
       echo "    msg_type     ='".$fields[ 2]."' ;					\n" ;
       echo "    msg_type_    ='".$fields[ 3]."' ;					\n" ;
       echo "    msg_text     ='".$fields[ 4]."' ;					\n" ;
       echo "    msg_text     =Sign_decode(msg_text, sender_key, receiver_key) ;	\n" ;
       echo "    msg_details  ='".$fields[ 5]."' ;					\n" ;
       echo "    msg_details  =Sign_decode(msg_details, sender_key, receiver_key) ;	\n" ;
       echo "    msg_sender   ='".$fields[ 1]."' ;					\n" ;
       echo "    msg_done     ='".$fields[11]."' ;					\n" ;

       echo "  a_msg_id     [".$i."]=msg_id  ;        \n" ;
       echo "  a_msg_sender [".$i."]=msg_sender_id ;  \n" ;
       echo "  a_msg_type   [".$i."]=msg_type ;       \n" ;
       echo "  a_msg_text   [".$i."]=msg_text ;       \n" ;
       echo "  a_msg_details[".$i."]=msg_details ;    \n" ;
       echo "  a_msg_done   [".$i."]=msg_done ;       \n" ;

      if($fields[10]=="nocrypt")
      {
       echo "     msg_sender  ='".$fields[7]."'+' '	\n" ;
       echo "                 +'".$fields[8]."'+' '	\n" ;
       echo "                 +'".$fields[9]."' ;	\n" ;
      }
      else
      if($fields[10]!="")
      {
       echo "     msg_name_key=Crypto_decode('".$fields[10]."', password) ;	\n" ;
       echo "     msg_sender  =Crypto_decode('".$fields[ 7]."', msg_name_key)+' '	\n" ;
       echo "                 +Crypto_decode('".$fields[ 8]."', msg_name_key)+' '	\n" ;
       echo "                 +Crypto_decode('".$fields[ 9]."', msg_name_key) ;	\n" ;
      }	

       echo "  AddNewMessage(".$i.", msg_id, msg_sender_id, msg_sender, msg_type, msg_type_, msg_text, msg_details) ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Формирование списка установленных связей

  if($glb_options_a["user"]=="Doctor"   ||
     $glb_options_a["user"]=="Executor"   )
  {
                       $sql="Select sender, done".
	  		    "  From messages ".
			    " Where receiver='$user_'".
                            "  and  type    ='CLIENT_ACCESS_INVITE'".
                            "  and  done in ('Y','R')" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select MESSAGES(CLIENT_ACCESS_INVITE)... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка установленных связей") ;
                         return ;
    }
    else
    {  

      for($i=0 ; $i<$res->num_rows ; $i++)
      {
	      $fields=$res->fetch_row() ;

        echo "  a_access['".$fields[0]."']='".$fields[1]."' ;   \n" ;
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

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color='green' ;					\n" ;
    echo  "i_error.innerHTML  ='Доступ к указанным страницам предоставлен.' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed-Mobile Messages InBox</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("mob_common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script src="rsa.js"></script>

<script type="text/javascript">
<!--

    var  i_error ;
    var  password ;
    var  messages_cnt ;
    var  row_id ;
    var  message_id ;
    var  message_type ;
    var  sender_id ;
    var  details ;
    
    var  a_msg_id  ;
    var  a_msg_sender ;
    var  a_msg_type  ;
    var  a_msg_text  ;
    var  a_msg_details  ;
    var  a_msg_done  ;
    var  a_access  ;

    
  function FirstField() 
  {
    var  receiver_key ;
    var  sender_key ;
    var  msg_text ;

       i_table   =document.getElementById("Fields") ;
       i_pages   =document.getElementById("Pages") ;
       i_error   =document.getElementById("Error") ;
       
       a_msg_id     =new Array() ;
       a_msg_sender =new Array() ;
       a_msg_type   =new Array() ;
       a_msg_text   =new Array() ;
       a_msg_details=new Array() ;
       a_msg_done   =new Array() ;
       a_access     =new Array() ;

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

     parent.frames['details'].location.replace('mob_messages_footer.php') ;

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
            error_text="" ;

        i_error.style.color="red" ;
        i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

                         return true ;         
  } 

  function AddNewMessage(p_row, p_id, p_sender_id, p_sender, p_type, p_type_desc, p_text, p_details)
  {
     var  i_messages ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_div_new ;
     var  i_fld_new ;

       i_messages= document.getElementById("Messages") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "Table_LT" ;
       i_row_new . id        = "Row_"+p_row ;
       i_row_new . onclick   =function(e) {  SelectMessage(this.id.substr(4)) ;  } ;

       i_col_new = document.createElement("td") ;
       i_txt_new = document.createTextNode(p_sender) ;
       i_col_new . className = "Table_LT" ;
       i_col_new . appendChild(i_txt_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Msg_'+p_row ;
       i_fld_new . type     ="hidden" ;
       i_fld_new . value    = p_id ;
       i_col_new . appendChild(i_fld_new) ;

       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Sender_'+p_row ;
       i_fld_new . type     ="hidden" ;
       i_fld_new . value    = p_sender_id ;
       i_col_new . appendChild(i_fld_new) ;

       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Type_'+p_row ;
       i_fld_new . type     ="hidden" ;
       i_fld_new . value    = p_type ;
       i_col_new . appendChild(i_fld_new) ;

       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Details_'+p_row ;
       i_fld_new . type     ="hidden" ;
       i_fld_new . value    = p_details ;
       i_col_new . appendChild(i_fld_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "Table_LT" ;
       i_txt_new = document.createTextNode(p_type_desc) ;
       i_col_new . appendChild(i_txt_new) ;

       i_div_new = document.createElement("div") ;
       i_div_new . id     = "Text_"+p_row ;
       i_div_new . hidden = true ;
       i_txt_new = document.createTextNode(p_text) ;
       i_div_new . appendChild(i_txt_new) ;
       i_col_new . appendChild(i_div_new) ;
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
    var  v_group ;


    if(msg_idx==-1)
    {
         a_msg_groups=new Array() ;
    }

    if(action.indexOf("read")>=0)
    {
       document.getElementById("Row_"+msg_idx).hidden=true ;
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
         if(a_msg_type[msg_idx]=="CLIENT_INVITE_ACCEPT" ||
            a_msg_type[msg_idx]=="CLIENT_INVITE_REJECT"   ) 
         {
                               v_group="Invite_AR:"+a_msg_sender[msg_idx] ;

               if(a_msg_groups[v_group]==null)
               {
                  if(a_msg_done[msg_idx]!="Y")  action="execute" ;

                    a_msg_groups[v_group]="1" ;                          
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
               if(a_msg_done[msg_idx]!="Y")
                if(a_access[a_msg_sender[msg_idx]]=='R')  action="execute,read" ;
                else                                      action="execute" ;
            }
         }

         if(action!="none")
         {
                ProcessMessage(msg_idx, a_msg_id[msg_idx], a_msg_type[msg_idx], a_msg_details[msg_idx], action) ;

                  i_error.style.color='blue' ;
                  i_error.innerHTML  ='Подождите - идет автоматическая обработка полученных сообщений' ;

                    return ;
         }
     }

                  i_error.innerHTML='' ;
  }

      var  selected=null ; 
  
  function ProcessMessage(p_row, p_id, p_type, p_details, p_action)
  {
    var  v_session ;
    var  v_url ;
    var  accept_details ;
    var  words_1 ;
    var  words_2 ;
    var  plus=new RegExp("\\+","g") ;


   if(selected!=null)
   {
                    cols=selected.getElementsByTagName('td') ;
     for(var i=0; i<cols.length; i++)  cols[i].className="Table_LT" ;
   }

                selected=document.getElementById("Row_"+p_row) ;
                    cols=selected.getElementsByTagName('td') ;
     for(var i=0; i<cols.length; i++)  cols[i].className="TableSelected_LT" ;

       accept_details="" ;
	words_1      =p_details.split(';') ;

    for(var i=0 ; i<words_1.length ; i++)
    {
      if(words_1[i]!="") 
      {
	 words_2      =words_1[i].split(':') ;

	accept_details=accept_details+words_2[0]+" " ;
	accept_details=accept_details+Crypto_encode(words_2[1], password) ;
	accept_details=accept_details+" " ;
	accept_details=accept_details+Crypto_encode(words_2[2], password) ;
      }
    }

	accept_details=accept_details.replace(plus,"%2B") ;

 	  v_session=TransitContext("restore","session","") ;

              v_url="mob_z_accept_access.php?Session="+v_session+
					   "&Message="+p_id+
					   "&Details="+accept_details+
                                           "&Action=" +p_action ;

	parent.frames["processor"].location.assign(v_url) ;
  }
  
  function SelectMessage(p_row) 
  {
     for(i=0 ; i<messages_cnt ; i++)
       if(i!=p_row)  document.getElementById("Row_"+i).hidden=true ;

	document.getElementById("Text").innerHTML=document.getElementById("Text_"+p_row).innerHTML ;

           row_id  =  p_row ;
       message_id  =document.getElementById("Msg_"    +p_row).value ;
       message_type=document.getElementById("Type_"   +p_row).value ;
        sender_id  =document.getElementById("Sender_" +p_row).value ;
          details  =document.getElementById("Details_"+p_row).value ;

     if(message_type=="CLIENT_ACCESS_INVITE"      ||
        message_type=="CLIENT_ACCESS_PAGES"       ||
	message_type=="CLIENT_PRESCRIPTIONS_ALERT"  )
     {
        if(a_msg_done[p_row]=='Y')
        {
           document.getElementById("Accept").hidden=true ;
           document.getElementById("Read"  ).hidden=false ;
        }
        else
        {
           document.getElementById("Accept").hidden=false ;
           document.getElementById("Read"  ).hidden=true ;
        }
     }
     else
     {
           document.getElementById("Accept").hidden=true ;
           document.getElementById("Read"  ).hidden=false ;
     }

           document.getElementById("Actions").hidden=false ;     
           document.getElementById("Text"   ).hidden=false ;
  }

  function ResetMessages() 
  {
     for(i=0 ; i<messages_cnt ; i++)
         document.getElementById("Row_"+i).hidden=false ;

	document.getElementById("Actions").hidden=true ;
	document.getElementById("Text"   ).hidden=true ;
  }

  function GoToView()
  {
    window.open("doctor_view.php"+"?Owner="+sender_id) ;
  } 

  function GoToMail()
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["section"].location.assign("mob_chat.php?Session="+v_session+"&Sender="+sender_id) ;
  } 

  function AccessAccept()
  {
    var  accept_details ;
    var  words_1 ;
    var  words_2 ;
    var  plus=new RegExp("\\+","g") ;


         i_message=document.getElementById("Row_"+row_id) ;
         i_message.style.textDecoration="line-through" ;

             ResetMessages() ;

       accept_details="" ;
	words_1      =details.split(';') ;

    for(var i=0 ; i<words_1.length ; i++)
    {
      if(words_1[i]!="") 
      {
	 words_2      =words_1[i].split(':') ;

	accept_details=accept_details+words_2[0]+" " ;
	accept_details=accept_details+Crypto_encode(words_2[1], password) ;
	accept_details=accept_details+" " ;
	accept_details=accept_details+Crypto_encode(words_2[2], password) ;
      }
    }

	accept_details=accept_details.replace(plus,"%2B") ;

	  v_session=TransitContext("restore","session","") ;

		url="mob_z_accept_access.php?Session="+v_session+
					   "&Message="+message_id+
					   "&Details="+accept_details ;

	parent.frames["processor"].location.assign(url) ;
  }

  function MarkRead()
  {
    var  i_message ;
    var  v_session ;

         i_message=document.getElementById("Row_"+row_id) ;
         i_message.style.textDecoration="line-through" ;

             ResetMessages() ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["processor"].location.assign("mob_z_message_markread.php?Session="+v_session+"&Message="+message_id) ;
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

<div>

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
        <b>ВХОДЯЩИЕ СООБЩЕНИЯ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">
  <p class="error" id="Error"></p>
  <table width="100%">
    <thead>
    </thead>
    <tbody id="Messages">
    </tbody>
  </table>

  <table width="100%" hidden id="Actions">
    <thead>
    </thead>
    <tbody>
    <tr>
      <i><div id="Text"></div></i>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="button" class="G_bttn" value="Принять"   id="Accept" onclick=AccessAccept()> </td>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="button" class="R_bttn" value="Прочитано" id="Read"   onclick=MarkRead()> </td>
    </tr>
    <tr>
      <td class="fieldC"> <br> <input type="button" value="Переписка" onclick=GoToMail()></td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
