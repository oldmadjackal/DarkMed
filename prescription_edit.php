<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescription_edit.php" ;

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

                         $get_id=$_GET ["Id"] ;
                         $put_id=$_POST["Id"] ;

  if( isset($put_id )) 
  {
                         $type       =$_POST["Type"] ;
                         $name       =$_POST["Name"] ;
                         $reference  =$_POST["Reference"] ;
                         $description=$_POST["Description"] ;
                         $www_link   =$_POST["WWW_link"] ;
  }

    FileLog("START", "    Session:".$session) ;

  if( isset($get_id )) 
  {
    FileLog("",      "     Get_Id:".$get_id) ;
  }

  if( isset($put_id )) 
  {
    FileLog("",      "     Put_Id:".$put_id) ;
    FileLog("",      "       Type:".$type) ;
    FileLog("",      "       Name:".$name) ;
    FileLog("",      "  Reference:".$reference) ;
    FileLog("",      "Description:".$description) ;
    FileLog("",      "   WWW_link:".$www_link) ;
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
                    ErrorMsg($error) ;
                         return ;
  }

          $session_=$db->real_escape_string($session) ;
          $user_   =$db->real_escape_string($user) ;

//--------------------------- Создание новой записи

  if(!isset($get_id) &&
     !isset($put_id)   ) 
  {
                       $sql="Insert into prescriptions_registry(type, user, name)".
                            " Values('dummy','$user_', '#$session_#')" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Insert PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания карточки назначения") ;
                         return ;
    }

            $db->commit() ;

                       $sql="Select max(id)".
                            "  From prescriptions_registry".
                            " Where name='#$session_#'" ;

       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select id from PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка идентификации карточки назначения") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $put_id     =$fields[0] ;
                   $owner      =$user ;
                   $type       ='dummy' ;
                   $name       ='' ;
                   $reference  ='' ;
                   $description='' ;
                   $www_link   ='' ;

        FileLog("", "New prescription generated successfully") ;
  }
//--------------------------- Извлечение данных для отображения
  else
  if(!isset($put_id))
  {
          $get_id_=$db->real_escape_string($get_id) ;

                       $sql="Select id, user, type, name, reference, description, www_link".
                            "  From  prescriptions_registry".
                            " Where  id='$get_id_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select * from PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения данных") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $put_id     =$fields[0] ;
                   $owner      =$fields[1] ;
                   $type       =$fields[2] ;
                   $name       =$fields[3] ;
                   $reference  =$fields[4] ;
                   $description=$fields[5] ;
                   $www_link   =$fields[6] ;

        FileLog("", "Prescription data selected successfully") ;
  }
//--------------------------- Сохранение данных со страницы
  else
  {
          $put_id_     =$db->real_escape_string($put_id) ;
          $type_       =$db->real_escape_string($type) ;
          $name_       =$db->real_escape_string($name) ;
          $reference_  =$db->real_escape_string($reference) ;
          $description_=$db->real_escape_string($description) ;
          $www_link_   =$db->real_escape_string($www_link) ;

                       $sql="Update prescriptions_registry".
                            " Set   type       ='$type_'".
                            "      ,name       ='$name_'".
                            "      ,reference  ='$reference_'".
                            "      ,description='$description_'".
                            "      ,www_link   ='$www_link_'".
                            " Where id='$put_id_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }

          $db->commit() ;

        FileLog("", "Prescription data saved successfully") ;
     SuccessMsg() ;
  }
//--------------------------- Извлечение списка типов назначений

                     $sql="Select code, name".
			  "  From ref_prescriptions_types".
			  " Where language='RU'".
                          "  and  code<>'unregistered'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_PRESCRIPTIONS_TYPES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника типов назначений") ;
                         return ;
  }
  else
  {  
       echo "   a_types['dummy']='' ;\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_types['".$fields[0]."']='".$fields[1]."' ;\n" ;
     }
  }

     $res->close() ;

//--------------------------- Вывод данных на страницу

      echo     "  i_id         .value='".$put_id     ."' ;\n" ;
      echo     "  i_owner      .value='".$owner      ."' ;\n" ;
      echo     "  i_name       .value='".$name       ."' ;\n" ;
      echo     "  i_reference  .value='".$reference  ."' ;\n" ;
      echo     "  i_description.value='".$description."' ;\n" ;
      echo     "  i_www_link   .value='".$www_link   ."' ;\n" ;

      echo     "  SetType('".$type."') ;\n" ;

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

<title>DarkMed Prescription Registry Card</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_table ;
    var  i_id ;
    var  i_owner ;
    var  i_type ;
    var  i_name ;
    var  i_reference ;
    var  i_description ;
    var  i_www_link ;
    var  i_error ;

    var  a_types ;


  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;

       i_table      =document.getElementById("Fields") ;
       i_id         =document.getElementById("Id") ;
       i_owner      =document.getElementById("Owner") ;
       i_type       =document.getElementById("Type") ;
       i_name       =document.getElementById("Name") ;
       i_reference  =document.getElementById("Reference") ;
       i_description=document.getElementById("Description") ;
       i_www_link   =document.getElementById("WWW_link") ;
       i_error      =document.getElementById("Error") ;

	a_types=new Array() ;

<?php
            ProcessDB() ;
?>

       i_description.value=i_description.value.replace(nl,"\n") ;

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;
     var  nl=new RegExp("\n","g") ;

	error_text="" ;
     
     if(i_name.value==''     )  error_text="Название назначения должно быть задано" ;
     if(i_type.value=='dummy')  error_text="Категория назначения должна быть определена" ;

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

          i_description.value=i_description.value.replace(nl,"@@") ;

          i_id   .disabled=false ;
          i_owner.disabled=false ;

                         return true ;         
  } 

  function SetType(p_selected)
  {
     var  selected ;

    for(var elem in a_types)
    {
                             selected=false ;
       if(p_selected==elem)  selected=true ;

                      i_type.length++ ;
       i_type.options[i_type.length-1].text    =a_types[elem] ;
       i_type.options[i_type.length-1].value   =        elem ;
       i_type.options[i_type.length-1].selected=    selected ;
    }

    return ;         
  } 

  function GoToLink() 
  {
    window.open(i_www_link.value) ;
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
        <b>КАРТОЧКА РЕГИСТРА НАЗНАЧЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> </td>
      <td> <br> <input type="submit" value="Сохранить"  id="Save1"> </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="field"> Код </td>
      <td> <input type="text" size=10 disabled name="Id" id="Id"> </td>
    </tr>
    <tr>
      <td class="field"> Создано </td>
      <td> <input type="text" size=10 disabled name="Owner" id="Owner"> </td>
    </tr>
    <tr>
      <td class="field"> Категория </td>
      <td>
         <select name="Type" id="Type"> 
         </select> 
      </td>
    </tr>
    <tr>
      <td class="field"> Название </td>
      <td> <input type="text" size=60 name="Name" id="Name"> </td>
    </tr>
    <tr>
      <td class="field"> Регистр </td>
      <td> <input type="text" size=60 name="Reference" id="Reference"> </td>
    </tr>
    <tr>
      <td class="field"> Смотреть на </td>
      <td>
          <input type="text" size=60 maxlength=510 name="WWW_link" id="WWW_link"> 
          <input type="button" value="Проверить" onclick=GoToLink()>
      </td>
    </tr>
    <tr>
      <td class="field"> Описание </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Description" id="Description"> </textarea>
      </td>
    </tr>
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
