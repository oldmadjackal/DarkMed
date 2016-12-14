<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_prescriptions.php" ;

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
  
  if( isset($_POST["Receiver"]))
  {
                       $receiver=$_POST["Receiver"] ;
                       $letter  =$_POST["Letter"] ;
                       $invite  =$_POST["InvText"] ;
                       $incopy  =$_POST["InCopy"] ;
  }

        FileLog("START", " Session:".$session) ;

  if( isset($receiver))
  {
        FileLog("",      "Receiver:".$receiver) ;
        FileLog("",      "  Letter:".$letter) ;
        FileLog("",      "  Invite:".$invite) ;
        FileLog("",      "  InCopy:".$incopy) ;
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

//--------------------------- Сохранение сообщения

  if(isset($receiver))
  {
          $receiver=$db->real_escape_string($receiver) ;
          $letter  =$db->real_escape_string($letter) ;
          $invite  =$db->real_escape_string($invite) ;
          $incopy  =$db->real_escape_string($incopy) ;

       $res=$db->query("Insert into messages(Receiver,Sender,Type,Text,Details,Copy)".
                       " values('$receiver','$user_','CLIENT_ACCESS_PAGES','$invite','$letter','$incopy')") ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "Access message saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Извлечение ключей пациента

                     $sql="Select u.sign_s_key, u.msg_key".
			  "  From users u".
			  " Where u.login='$user_'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select USERS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса ключей сообщений пользователя") ;
                         return ;
  }
  else
  {      
	      $fields=$res->fetch_row() ;

       echo "          user='".$user."' ;				\n" ;
       echo "    sender_key='".$fields[0]."' ;				\n" ;
       echo "    sender_key=Crypto_decode(sender_key, password) ;	\n" ;
       echo "       msg_key='".$fields[1]."' ;				\n" ;
       echo "       msg_key=Crypto_decode(msg_key, password) ;		\n" ;
  }

     $res->close() ;

//--------------------------- Формирование списка дополнительных страницы пациента

                     $sql="Select p.page, p.title, a.crypto, a.ext_key, p.creator, CONCAT_WS(' ', d.name_f,d.name_i,d.name_o)".
			  "  From client_pages p, access_list a, doctor_page_main d".
			  " Where d.owner=p.creator".
                          "  and  p.owner='$user'".
			  "  and  p.page > 0".
			  "  and  a.owner='$user_'".
			  "  and  a.login='$user_'".
			  "  and  a.page =p.page".
                          "  and  p.type ='Prescription'".
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

       echo     "         page_num     ='".$fields[0]."' ;			\n" ;
       echo     "         link_key     ='".$fields[2]."' ;			\n" ;
       echo     "         link_key     =Crypto_decode(link_key, password) ;	\n" ;
       echo     "         link_text    ='".$fields[1]."' ;			\n" ;
       echo     "         link_text    =Crypto_decode(link_text, link_key) ;	\n" ;
       echo     "         file_key     ='".$fields[3]."' ;			\n" ;
       echo     "         file_key     =Crypto_decode(file_key, password) ;	\n" ;

       echo     "          a_letter [page_num]=page_num+':'+link_key+':'+file_key+';' ;	\n" ;
       echo     "          a_creator[page_num]='".$fields[4]."' ;			\n" ;
       echo     "           PageAdd(page_num, link_text, file_key, '".$fields[5]."') ;	\n" ;
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
//  Выдача сообщения об успешной регистрации

function SuccessMsg() {

    echo  "i_error.style.color='green' ;		\n" ;
    echo  "i_error.innerHTML  ='Дoступ предоставлен' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Client Prescriptions</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("text.css") ;
  @import url("tables.css") ;
  @import url("buttons.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script src="rsa.js"></script>

<script type="text/javascript">
<!--

    var  i_pages ;
    var  i_error ;
    var  i_receiver ;
    var  i_letter ;
    var  i_incopy ;
    var  password ;
    var  user ;
    var  page_key ;
    var  sender_key ;
    var  msg_key ;
    var  doctor ;
    
    var  a_letter ;
    var  a_creator ;

  function FirstField() 
  {
    var  link_key ;
    var  link_text ;
    var  file_key ;


       i_pages   =document.getElementById("Pages") ;
       i_error   =document.getElementById("Error") ;
       i_receiver=document.getElementById("Receiver") ;
       i_letter  =document.getElementById("Letter") ;
       i_incopy  =document.getElementById("InCopy") ;
       
	a_letter =new Array() ; 
	a_creator=new Array() ; 

       password=TransitContext("restore", "password", "") ;

<?php
            ProcessDB() ;
?>

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;
     var  i_invite ;


       i_invite  =document.getElementById("InvText") ;

	error_text=""
 
     if(i_invite.value=="")  error_text="Укажите сопроводительный текст" ;
 
     if(error_text!="") 
     {
          document.getElementById("InvError").innerHTML  =error_text ;
          document.getElementById("InvError").style.color='red' ;
          document.getElementById("InvError").hidden     =false ;
                return false ;
     }

       i_receiver.value=   doctor.user ;
       i_incopy  .value=Crypto_encode(i_invite.value, msg_key) ;
       i_letter  .value=  Sign_encode(i_letter.value, sender_key, doctor.pkey) ;
       i_invite  .value=  Sign_encode(i_invite.value, sender_key, doctor.pkey) ;

                return true ;         
  } 

  function PageAdd(p_page, p_title, p_text_key, p_creator) 
  {
    var  i_row_new ;
    var  i_col_new ;
    var  i_hdr_new ;
    var  i_lnk_new ;
    var  i_txt_new ;
    var  i_btn_new ;
    var  i_ret_new ;
    var  i_frm_new ;
    var  v_session ;


            v_session=TransitContext("restore","session","") ;

       i_pages   = document.getElementById("Pages") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "Table_LT" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "Table_LT" ;
       i_col_new . width = "100%" ;
//---
       i_btn_new = document.createElement('input') ;
       i_btn_new . type   ="button" ;
       i_btn_new . value  ="Передать врачу/специалисту" ;
       i_btn_new . onclick= function(e) {  SendAccess(p_page) ;  } ;
       
       i_col_new . appendChild(i_btn_new) ;
//---
       i_ret_new = document.createElement('br') ;
       i_col_new . appendChild(i_ret_new) ;
//---
       i_lnk_new = document.createElement('a') ;
       i_lnk_new . href="client_prescr_view.php?Session="+v_session+"&Page="+p_page+"&Owner="+user ;
       i_txt_new = document.createTextNode(p_title) ;
       i_lnk_new . appendChild(i_txt_new) ;

       i_hdr_new = document.createElement('font') ;
       i_hdr_new . id  ="Title_"+p_page ; 
       i_hdr_new . size="+2" ;
       i_hdr_new . bold=true ;
       i_hdr_new . appendChild(i_lnk_new) ;

       i_col_new . appendChild(i_hdr_new) ;
//---
       i_ret_new = document.createElement('br') ;
       i_col_new . appendChild(i_ret_new) ;
//---
       i_txt_new = document.createTextNode('Назначение сделал '+p_creator) ;
       i_col_new . appendChild(i_txt_new) ;
//---
       i_frm_new = document.createElement('iframe') ;
       i_frm_new . src         ="client_prescr_pilot.php?Session="+v_session+"&Page="+p_page+"&Owner="+user ;
       i_frm_new . seamless    = true ;
       i_frm_new . height      ="152" ;
       i_frm_new . width       ="700" ;
       i_frm_new . scrolling   ="no" ;
       i_frm_new . frameborder ="0" ;
       i_frm_new . marginheight="0" ;
       i_frm_new . marginwidth ="0" ;

       i_col_new . appendChild(i_frm_new) ;
//---
       i_row_new . appendChild(i_col_new) ;
       i_pages   . appendChild(i_row_new) ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "TableGap" ;
       i_row_new . height    = "10px" ;
       i_col_new = document.createElement("td") ;
       i_col_new . className = "TableGap" ;
       i_col_new . height    = "10ex" ;
       i_row_new . appendChild(i_col_new) ;
       i_pages   . appendChild(i_row_new) ;

  }  

  function SendAccess(p_page) 
  {
     var  invitation ;
     var  page_title ;
     
     
     invitation=document.getElementById("Invitation"   ) ;
     page_title=document.getElementById("Title_"+p_page) ;

     invitation.hidden=false ;
     
     page_title.parentNode.insertBefore(invitation, page_title) ;
     
         doctor=parent.frames["details"].EI_GetSelectedDoctor() ;

      if(doctor.user==null)
      {
          document.getElementById("InvError").innerHTML  ="Сначала необходимо выбрать врача или специалиста в списке справа.<br>"+
                                                          "Если список справа пуст - необходимо направить приглашение одному из специалистов со вкладки 'Врачи и Специалисты'" ;
          document.getElementById("InvError").style.color='red' ;
          document.getElementById("InvError").hidden     =false ;
          document.getElementById("InvMain" ).hidden     =true ;
      }
      else
      if(doctor.user==a_creator[p_page])
      {
          document.getElementById("InvError").innerHTML  ="Нельзя направлять назначение его автору." ;
          document.getElementById("InvError").style.color='red' ;
          document.getElementById("InvError").hidden     =false ;
          document.getElementById("InvMain" ).hidden     =true ;              
      }              
      else
      {
          document.getElementById("InvError"   ).hidden   =true ;
          document.getElementById("InvMain"    ).hidden   =false ;
          document.getElementById("InvReceiver").innerHTML=doctor.name ;
      }

                i_letter.value=a_letter[p_page] ;
  } 

  function InvCancel() 
  {
        document.getElementById("Invitation").hidden=true ;
  } 

  function EI_DoctorSelected(p_doctor) 
  {
        document.getElementById("InvError"   ).hidden=true ;
        
          doctor=parent.frames["details"].EI_GetSelectedDoctor() ;

        document.getElementById("InvReceiver").innerHTML=doctor.name ;         
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
        <b>НАЗНАЧЕНИЯ</b>
      </td> 
    </tr>
    </tbody>
  </table>
  
   <div class="Error_CT" id="Error"></div>
   
  <br>
  
  <form onsubmit="return SendFields();" method="POST">

  <table width="100%">
    <tbody id="Pages">
    </tbody>
  </table>
  
  <div id="Invitation" hidden>
    <div class="Error_LT" id="InvError"></div>
    <div id="InvMain">
      <b><div class="Text_LT" id="InvReceiver"></div></b>
      <br>
      <div class="Text_LT">Сопроводительный текст:</div>
      <textarea cols=60 rows=4 wrap="soft" name="InvText" id="InvText"></textarea>
      <br>
      <input type="submit" value="Направить данные">
      <input type="button" onclick=InvCancel() value="Отменить"> 
    </div>
  </div>

	<input type="hidden" name="Receiver" id="Receiver" >
	<input type="hidden" name="Letter"   id="Letter" >
	<input type="hidden" name="InCopy"   id="InCopy" >

  </form>

</body>

</html>
