<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescription_details_any.php" ;

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

                        $session=$_GET ["Session"] ;
                        $id     =$_GET ["Id"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "         Id:".$id) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Извлечение данных карточки назначения

                       $id_=$db->real_escape_string($id) ;

                       $sql="Select  r.user, t.name, r.name, r.reference, r.description, r.www_link".
                            "       ,d.name_f, d.name_i, d.name_o".
                            "  From  prescriptions_registry r".
                            "        inner join doctor_page_main d on d.owner=r.user".
                            "        inner join ref_prescriptions_types t on t.code=r.type and t.language='RU'".
                            " Where  r.id=$id_" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select * from PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }
    else
    if($res->num_rows==0) {
          FileLog("ERROR", "No such prescription in DB : ".$id) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: неизвестное назначение") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $owner      =$fields[0] ;
                   $owner_name =$fields[6]." ".$fields[7]." ".$fields[8] ;
                   $type       =$fields[1] ;
                   $name       =$fields[2] ;
                   $reference  =$fields[3] ;
                   $description=$fields[4] ;
                   $www_link   =$fields[5] ;

        FileLog("", "Prescription data selected successfully") ;

//--------------------------- Формирование данных страницы

      echo "                  creator='".$owner      ."' ;	\n" ;

      echo "  i_id         .innerHTML='".$id         ."' ;	\n" ;
      echo "  i_owner      .innerHTML='".$owner_name ."' ;	\n" ;
      echo "  i_type       .innerHTML='".$type       ."' ;	\n" ;
      echo "  i_name       .innerHTML='".$name       ."' ;	\n" ;
      echo "  i_reference  .innerHTML='".$reference  ."' ;	\n" ;
      echo "  i_description.innerHTML='".$description."' ;	\n" ;
      echo "  i_www_link   .value    ='".$www_link.   "' ;	\n" ;

   if($session=="") 
      echo "  i_mailto     .disabled =true ;			\n" ;

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

<title>DarkMed Prescription registry details any-form</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
  @import url("tables.css") ;
  @import url("text.css") ;
</style>

<script type="text/javascript">
<!--

    var  i_id ;
    var  i_owner ;
    var  i_type ;
    var  i_name ;
    var  i_reference ;
    var  i_description ;
    var  i_www_link ;
    var  i_goto ;
    var  i_mailto ;
    var  i_error ;

    var  creator ;

  function FirstField() 
  {
    var  text ;
    var  pos ;
    var  nl=new RegExp("@@","g") ;


       i_id         =document.getElementById("Id") ;
       i_owner      =document.getElementById("Owner") ;
       i_type       =document.getElementById("Type") ;
       i_name       =document.getElementById("Name") ;
       i_reference  =document.getElementById("Reference") ;
       i_description=document.getElementById("Description") ;
       i_www_link   =document.getElementById("WWW_link") ;
       i_goto       =document.getElementById("GoToLink") ;
       i_mailto     =document.getElementById("MailTo") ;
       i_error      =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

                text=i_www_link.value ;
                 pos=text.indexOf("://") ;
    if(pos>=0)  text=text.substr(pos+3) ;
                 pos=text.indexOf("/") ;
    if(pos>=0)  text=text.substr(0, pos) ;

    if(i_www_link.value!='')  i_goto.innerHTML='Смотреть на '+text ;
    else                      i_goto.hidden   = true ;

      i_description.innerHTML=i_description.innerHTML.replace(nl,"<br>") ;

         return true ;
  }

  function WhoIsIt()
  {
    window.open("doctor_view.php"+"?Owner="+creator) ;
  } 

  function MailTo()
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	parent.frames["section"].location.assign("messages_chat_lr.php?Session="+v_session+"&Sender="+creator) ;
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

  <div class="Bold_CT" id="Name"></div>
  <br>

  <table border="0" width="100%">
    <tbody>
      <tr>
        <td class="Bold_RT">Категория:</td>
        <td class="Normal_LT"> <div id="Type"> </div></td>
      </tr>
      <tr>
        <td class="Bold_RT">Классификатор:</td>
        <td class="Normal_LT"> <div id="Reference"> </div> </td>
      </td>
      </tr>
    </tbody>
  </table>

  <br>
  <div class="Normal_CT">
    <a href="javascript:
             window.open(document.getElementById('WWW_link').value) ;"
       Id="GoToLink">Смотреть на</a>
  </div>
  <br>
  <i><div id="Description"></div></i>
  <br>
  <br>

  <table border="0" width="100%">
    <tbody>
      <tr>
        <td class="Bold_RT">Автор:</td>
        <td class="Normal_LT">
           <div id="Owner"> </div>
           <input type="button" value="Кто это?" onclick=WhoIsIt()>
           <input type="button" value="Переписка" onclick=MailTo() id="MailTo">
        </td>
      </tr>
    </tbody>
  </table>


  <div id="Id" hidden></div>
  <input type="hidden" Name="WWW_link" Id="WWW_link">

</body>

</html>
