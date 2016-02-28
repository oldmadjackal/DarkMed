<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_prescr_edit.php" ;

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
  if(!isset($session ))  $session=$_POST["Session"] ;

                        $new_page=$_GET ["NewPage"] ;
                           $owner=$_GET ["Owner"] ;

                            $page=$_GET ["Page"] ;
  if(!isset($page    ))     $page=$_POST["Page"] ;

                          $update=$_POST["Update"] ;
  if(isset($update   )) {
                          $crypto=$_POST["Crypto"] ;
                           $check=$_POST["Check"] ;
                           $title=$_POST["Title"] ;
                          $remark=$_POST["Remark"] ;
                           $count=$_POST["Count"] ;
  }

                         $publish=$_POST["Publish"] ;
  if(isset($publish)    &&
           $publish=="1"  ) {
                         $publish=$_POST["Publish"] ;
                          $invite=$_POST["Invite"] ;
                          $letter=$_POST["Letter"] ;
                          $incopy=$_POST["InCopy"] ;
  }

             $a_prescr=array() ;
             $a_name  =array() ;
             $a_remark=array() ;
             $a_ref   =array() ;

  if(isset($update)) {

           settype($count, "integer") ;
     for($prescr_count=0, $i=1 ; $i<=$count ; $i++) 
     {
 	         $id=$_POST["Id_".$i] ;
        if(isset($id))
        {
             $a_prescr[$prescr_count]=$_POST["Id_"       .$i] ;
             $a_name  [$prescr_count]=$_POST["Name_"     .$i] ;
             $a_remark[$prescr_count]=$_POST["Remark_"   .$i] ;
             $a_ref   [$prescr_count]=$_POST["Reference_".$i] ;
                       $prescr_count++ ;
        }
     }
  }

    FileLog("START", "Session:".$session) ;
    FileLog("",      "NewPage:".$new_page) ;
    FileLog("",      "  Owner:".$owner) ;
    FileLog("",      "   Page:".$page) ;

  if(isset($update)) {
    FileLog("",      " Update:".$update) ;
    FileLog("",      "  Check:".$check) ;
    FileLog("",      " Crypto:".$crypto) ;
    FileLog("",      "  Title:".$title) ;
    FileLog("",      " Remark:".$remark) ;
  }

  if(isset($publish)    &&
           $publish=="1"  ) {
    FileLog("",     " Publish:".$publish) ;
    FileLog("",     "  Invite:".$invite) ;
    FileLog("",     "  Letter:".$letter) ;
    FileLog("",     "  InCopy:".$incopy) ;
  }

  if(isset($update)) {

    FileLog("",     "  Count:".$count) ;

   for($i=0 ; $i<$prescr_count ; $i++) 
    FileLog("",      "Prescription:".$a_prescr[$i]." ".$a_name[$i]." ".$a_remark[$i]." ".$a_ref[$i]) ;
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

          $owner_=$db->real_escape_string($owner) ;
          $user_ =$db->real_escape_string($user ) ;

//--------------------------- Извлечение создателя страницы

  if(isset($page) && $page!="")
  {

          $page_=$db->real_escape_string($page) ;

                       $sql="Select creator".
                            "  From client_pages".
                            " Where owner='$owner_'".
                            "  and  page = $page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select CLIENT_PAGES(CREATOR)) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения создателя страницы") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

    if($fields[0]!=$user) {
                       $db->close() ;
                    ErrorMsg("Редактировать страницу назначений может только ее создатель") ;
                         return ;
    }

  }
//--------------------------- Извлечение списка типов назначений

                     $sql="Select code, name".
			  "  From ref_prescriptions_types".
			  " Where language='RU'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select REF_PRESCRIPTIONS_TYPES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса справочника типов назначений") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "                      i_category.length++ ;				\n" ;
       echo "   i_category.options[i_category.length-1].text	='".$fields[1]."' ;	\n" ;
       echo "   i_category.options[i_category.length-1].value	='".$fields[0]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение Регистра назначений

                     $sql="Select id, type, name".
			  "  From prescriptions_registry".
                          " Where type<>'dummy'".
                          " Order by name" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра назначений") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_prescriptions['".$fields[0]."']='".$fields[1]."' ;	\n" ;

       echo "                              i_c_".$fields[1].".length++ ;				\n" ;
       echo "   i_c_".$fields[1].".options[i_c_".$fields[1].".length-1].text ='".$fields[2]."' ;	\n" ;
       echo "   i_c_".$fields[1].".options[i_c_".$fields[1].".length-1].value='".$fields[0]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение списка комплексов назначений

                     $sql="Select id, name".
			  "  From sets_registry".
                          " Where name not like '#%'".
                          "  and  user='$user_'".
                          " Order by name" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select SETS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра комплексов назначений") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "                       i_sets_list.length++ ;				\n" ;
       echo "   i_sets_list.options[i_sets_list.length-1].text ='".$fields[1]."' ;	\n" ;
       echo "   i_sets_list.options[i_sets_list.length-1].value='".$fields[0]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Отображение пустой новой страницы

  if( isset($new_page) &&
     !isset($check   )   ) 
  {
     $db->close() ;

      echo     "       page_key     ='' ;	\n" ;
      echo     "        msg_key     ='' ;	\n" ;
      echo     "  i_count  .value   ='0' ;	\n" ;
      echo     "  i_update .value   ='insert' ;	\n" ;
      echo     "  i_publish.disabled= true ;	\n" ;

      FileLog("",     "New page template sent") ;     
      FileLog("STOP", "Done") ;     
         return ;
  }
//--------------------------- Приведение параметров

          $page_   =$db->real_escape_string($page ) ;

  if(isset($update)) 
  {
          $crypto_ =$db->real_escape_string($crypto) ;
          $check_  =$db->real_escape_string($check ) ;
          $title_  =$db->real_escape_string($title ) ;
          $remark_ =$db->real_escape_string($remark) ;
          $publish_=$db->real_escape_string($publish) ;
  }
//--------------------------- Первое сохранение новой страницы

  if(isset($update)         && 
           $update=="insert"  )
  {
//- - - - - - - - - - - - - - Определяем номер новой страницы
                       $sql="Select count(*), max(Page)+1".
                            "  From client_pages".
                            " Where owner='$owner_'" ;
       $res=$db->query($sql) ;
    if($res===false) {
               FileLog("ERROR", "Select new page number from Insert CLIENT_PAGES. : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка определения номера новой страницы") ;
                           return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

        if($fields[0]=="0")  $page_=  "1" ;
        else                 $page_=$fields[1] ;
//- - - - - - - - - - - - - - Добавляем новую страницу
                       $sql="Insert into ".
                            "`access_list`(`Owner`,   `Login`,  `Page`,  `Crypto`)".
                            "       values('$owner_', '$user_', $page_,  '$crypto_')" ;
       $res=$db->query($sql) ;
    if($res===false) {
               FileLog("ERROR", "Insert ACCESS_LIST... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 1") ;
                           return ;
    }

                       $sql="Insert into ".
                            "client_pages(`Owner`,    Page,  `Type`,         `Creator`, `Check`,   `Title`  )".
                            "      values('$owner_', $page_, 'prescription', '$user_',  '$check_', '$title_')" ;
       $res=$db->query($sql) ;
    if($res===false) {
               FileLog("ERROR", "Insert CLIENT_PAGES... : ".$db->error) ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 2") ;
                           return ;
    }
//- - - - - - - - - - - - - -
        FileLog("", "User ".$owner." additional page ".$page_." successfully created") ;
  }
//--------------------------- Направление сообщения пациенту

  if($publish=="1")
  {
          $invite=$db->real_escape_string($invite) ;
          $letter=$db->real_escape_string($letter) ;
          $incopy=$db->real_escape_string($incopy) ;

       $res=$db->query("Insert into messages(Receiver,Sender,Type,Text,Details,Copy)".
                       " values('$owner_','$user_','CLIENT_PRESCRIPTIONS_ALERT','$invite','$letter','$incopy')") ;
    if($res===false) {
             FileLog("ERROR", "Insert MESSAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания сообщения пациенту") ;
                         return ;
    }

        FileLog("", "Message for User ".$owner." to access page ".$page_." sent successfully") ;
  }
//--------------------------- Сохранение данных страницы

  if(isset($check))
  {
//- - - - - - - - - - - - - - Сохранение титульных данных
                       $sql="Update client_pages".
                            "   Set title    ='$title_'".
                            "      ,remark   ='$remark_'".
                            "      ,published='$publish_'".
                            " Where `owner`='$owner_' ".
                            "  and   page  = $page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Update CLIENT_PAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных") ;
                         return ;
    }
//- - - - - - - - - - - - - - Сохранение списка назначений
           $assign_reference=false ;

   for($i=0 ; $i<$prescr_count ; $i++) 
   {
          $prescr_=$db->real_escape_string($a_prescr[$i]) ;
          $name_  =$db->real_escape_string($a_name  [$i]) ;
          $remark_=$db->real_escape_string($a_remark[$i]) ;

      if($a_ref[$i]!="") 
      {
        if(strpos($a_ref[$i], ":")===false) 
        {
           $assign_reference=true ;

           $type_=$db->real_escape_string($a_ref[$i]) ;
           $ref_ ='' ;
        }
        else
        {
           $words=explode(":", $a_ref[$i]) ;
           $type_=$db->real_escape_string($words[0]) ;
           $ref_ =$db->real_escape_string($words[1]) ;
        }
      }
      else
      {
           $type_='' ;
           $ref_ ='0' ;
      }  

                       $sql="Insert into prescriptions_pages".
                            "       (  owner,   page,   order_num,  prescription_id,   name,     remark,     type,     reference)".
                            " Values('$owner_', $page_,  $i,      '$prescr_',        '$name_', '$remark_', '$type_', '$ref_'    )" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Insert PRESCRIPTIONS_PAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 2") ;
                         return ;
    }

    if($i==0)  $new_first_id=$db->insert_id ;

   }
//- - - - - - - - - - - - - - Удаление старого списка назначений
                       $sql="Delete from prescriptions_pages".
                            " Where owner='$owner_'".
                            "  and  page = $page_".
                            "  and  id   < $new_first_id" ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Delete PRESCRIPTIONS_PAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 1") ;
                         return ;
    }
//- - - - - - - - - - - - - - Назначение референсов
   if($assign_reference)
   {
                       $sql="Update prescriptions_pages".
                            "   Set reference=id".
                            " Where owner    ='$owner_'".
                            "  and  page     = $page_".
                            "  and  type     ='measurement'".
                            "  and  reference= 0 " ; 
       $res=$db->query($sql) ;
    if($res===false) {
             FileLog("ERROR", "Delete PRESCRIPTIONS_PAGES... : ".$db->error) ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка записи в базу данных 1") ;
                         return ;
    }
   }
//- - - - - - - - - - - - - -
          $db->commit() ;

        FileLog("", "User ".$owner." additional page ".$page_." saved successfully") ;
     SuccessMsg() ;

  }
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

      echo  "   page_key  ='".$fields[0]."' ;	\n" ;
      echo  "   page_owner='".$owner    ."' ;	\n" ;
      echo  "   page_num  ='".$page     ."' ;	\n" ;

//--------------------------- Извлечение ключей подписи

                       $sql="Select login, sign_s_key, sign_p_key, msg_key".
                            "  From users".
                            " Where login='$user_' ".
                            "   or  login='$owner_' " ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select USERS...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения ключей подписи") ;
                         return ;
    }

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

        if($fields[0]==$user) {  echo "i_s_key.value='" .$fields[1]."' ;	\n" ;
                                 echo "msg_key      ='" .$fields[3]."' ;	\n" ;  }
        else                  {  echo "i_r_key.value='" .$fields[2]."' ;	\n" ;  }
     }

	              $res->close() ;

//--------------------------- Извлечение данных страницы

                       $sql="Select `check`, title, remark, published".
                            "  From client_pages".
                            " Where owner='$owner_'".
                            "  and  page = $page_" ;
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

                   $check    =$fields[0] ;
                   $title    =$fields[1] ;
                   $remark   =$fields[2] ;
                   $published=$fields[3] ;

        FileLog("", "User ".$owner." additional page ".$page_." presented successfully") ;

//--------------------------- Извлечение списка назначений

      echo     "  i_count.value='0'	;\n" ;

                     $sql="Select prescription_id, remark, if(`type` is null or `type`='', '', concat(`type`,':',if(reference=0,id,reference)))".
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

       echo "   a_plist_id    [".$i."]='".$fields[0]."' ;	\n" ;
       echo "   a_plist_remark[".$i."]='".$fields[1]."' ;	\n" ;
       echo "   a_plist_ref   [".$i."]='".$fields[2]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Отображение данных на странице

      echo     "  i_page  .value='".$page_.  "' ;\n" ;
      echo     "  i_check .value='".$check. "' ;\n" ;
      echo     "  i_title .value='".$title. "' ;\n" ;
      echo     "  i_remark.value='".$remark."' ;\n" ;
      echo     "  i_update.value='update' ;	\n" ;

  if($published=="1")
  {
      echo     "  i_publish.checked =true ;	\n" ;
      echo     "  i_publish.disabled=true ;	\n" ;
  }
  else
  {
	InfoMsg("Назначения еще не переданы пациенту") ;
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

<title>DarkMed Prescriptions Page</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_table ;    
    var  i_page ;
    var  i_check ;
    var  i_crypto ;
    var  i_title ;
    var  i_remark ;
    var  i_measurements ;
    var  i_id ;
    var  i_count ;
    var  i_update ;
    var  i_publish ;
    var  i_s_key ;
    var  i_r_key ;
    var  i_invite ;
    var  i_letter ;
    var  i_incopy ;
    var  i_error ;
    var  password ;
    var  page_key ;
    var  page_owner ;
    var  msg_key ;
    var  check_key ;
    var  next_page ;

    var  a_prescriptions ;
    var  a_plist_id ;
    var  a_plist_remark ;
    var  a_plist_ref ;

  function FirstField() 
  {
    var  prescr_id ;
    var  prescr_remark ;


       i_table       =document.getElementById("Fields") ;
       i_page        =document.getElementById("Page") ;
       i_check       =document.getElementById("Check") ;
       i_crypto      =document.getElementById("Crypto") ;
       i_title       =document.getElementById("Title") ;
       i_remark      =document.getElementById("Remark") ;
       i_measurements=document.getElementById("Measurements") ;
       i_id          =document.getElementById("Id") ;
       i_count       =document.getElementById("Count") ;
       i_sets_list   =document.getElementById("SetsList") ;
       i_update      =document.getElementById("Update") ;
       i_publish     =document.getElementById("Publish") ;
       i_s_key       =document.getElementById("Sign_s_key") ;
       i_r_key       =document.getElementById("Sign_r_key") ;
       i_invite      =document.getElementById("Invite") ;
       i_letter      =document.getElementById("Letter") ;
       i_incopy      =document.getElementById("InCopy") ;
       i_error       =document.getElementById("Error") ;

			    i_sets_list.length++ ;
	i_sets_list.options[i_sets_list.length-1].text ='' ;
	i_sets_list.options[i_sets_list.length-1].value='0' ;
        i_sets_list.onchange=function(e) { SetSelect(this.options[this.selectedIndex].value); } ;

	a_prescriptions=new Array() ;
	a_plist_id     =new Array() ;
	a_plist_remark =new Array() ;
	a_plist_ref    =new Array() ;

	i_category=document.getElementById("Category") ;
			   i_category.length++ ;
	i_category.options[i_category.length-1].text ='' ;
	i_category.options[i_category.length-1].value='dummy' ;

	i_c_exercise       =document.getElementById("C_exercise"       ) ;
	i_c_exploration    =document.getElementById("C_exploration"    ) ;
	i_c_measurement    =document.getElementById("C_measurement"    ) ;
	i_c_operation      =document.getElementById("C_operation"      ) ;
	i_c_others         =document.getElementById("C_others"         ) ;
	i_c_pharmacotherapy=document.getElementById("C_pharmacotherapy") ;
	i_c_test           =document.getElementById("C_test"           ) ;
	i_c_treatment      =document.getElementById("C_treatment"      ) ;
	i_c_unregistered   =document.getElementById("C_unregistered"   ) ;

			            i_c_exercise.length++ ;
	i_c_exercise       .options[i_c_exercise.length   -1].text ='' ;
	i_c_exercise       .options[i_c_exercise.length   -1].value='0' ;
			            i_c_exploration.length++ ;
	i_c_exploration    .options[i_c_exploration.length-1].text ='' ;
	i_c_exploration    .options[i_c_exploration.length-1].value='0' ;
			            i_c_measurement.length++ ;
	i_c_measurement    .options[i_c_measurement.length-1].text ='' ;
	i_c_measurement    .options[i_c_measurement.length-1].value='0' ;
			            i_c_operation.length++ ;
	i_c_operation      .options[i_c_operation.length-1].text ='' ;
	i_c_operation      .options[i_c_operation.length-1].value='0' ;
			            i_c_others.length++ ;
	i_c_others         .options[i_c_others.length-1].text ='' ;
	i_c_others         .options[i_c_others.length-1].value='0' ;
			            i_c_pharmacotherapy.length++ ;
	i_c_pharmacotherapy.options[i_c_pharmacotherapy.length-1].text ='' ;
	i_c_pharmacotherapy.options[i_c_pharmacotherapy.length-1].value='0' ;
			            i_c_test.length++ ;
	i_c_test           .options[i_c_test.length-1].text ='' ;
	i_c_test           .options[i_c_test.length-1].value='0' ;
			            i_c_treatment.length++ ;
	i_c_treatment      .options[i_c_treatment.length-1].text ='' ;
	i_c_treatment      .options[i_c_treatment.length-1].value='0' ;
			            i_c_unregistered.length++ ;
	i_c_unregistered   .options[i_c_unregistered.length-1].text ='' ;
	i_c_unregistered   .options[i_c_unregistered.length-1].value='0' ;

       i_title.focus() ;

           page_key="" ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;

    if(page_key!="")
    {
       page_key= Crypto_decode( page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
	i_error.style.color="red" ;
	i_error.innerHTML  ="Ошибка расшифровки данных." ;
         return true ;
     }

       i_title .value=Crypto_decode(i_title .value, page_key) ;
       i_remark.value=Crypto_decode(i_remark.value, page_key) ;

       i_s_key.value =Crypto_decode(i_s_key .value, password) ;

       for(i in a_plist_id) {
             prescr_id    =Crypto_decode(a_plist_id    [i], page_key) ;
             prescr_remark=Crypto_decode(a_plist_remark[i], page_key) ;

          AddListRow(prescr_id, 'UNKNOWN', prescr_remark, a_plist_ref[i]) ;
       }

    }

    if(msg_key!="")
    {
       msg_key=Crypto_decode(msg_key, password) ;
    }

         return true ;
  }

  function SendFields() 
  {
     var  error_text ;

	error_text=""

       i_table.rows[1].cells[0].style.color="black"   ;
     
     if(i_title.value=="") {
       i_table.rows[1].cells[0].style.color="red"   ;
             error_text=error_text+"<br>Не задано поле 'Заголовок'" ;
     }

     if(error_text!="")
     {
       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;
        return false ;
     }

     if(page_key=="") 
     {
//        page_key    =GetRandomString(64) ;
          page_key    ="PageKey_"+GetRandomString(32) ;  // DEBUG only
          check_key   =Check_generate() ;
        i_crypto.value=Crypto_encode( page_key, password) ;
        i_check.value =Crypto_encode(check_key, page_key) ;
     }
     else
     {
        i_crypto.value="" ;
     }

     if(               i_check .value  == "" ||
        Check_validate(i_check .value)===true  )
     {
             error_text=error_text+"<br>Ошибка крипто-системы. Попробуйте перезагрузить страницу." ;
     }

     if(i_publish.value=="1")
     {
                                     i_letter.value=i_page.value+":"+page_key ;
       if(i_publish.disabled==true)  i_invite.value="Отредактирована страница назначений: "+i_title.value ;
       else                          i_invite.value="Создана страница назначений: "        +i_title.value ;

              i_incopy.value    =Crypto_encode(i_invite.value, msg_key) ;
              i_invite.value    =  Sign_encode(i_invite.value, i_s_key.value, i_r_key.value) ;
              i_letter.value    =  Sign_encode(i_letter.value, i_s_key.value, i_r_key.value) ;
              i_publish.disabled= false ;
     }

       i_error.style.color="red" ;
       i_error.innerHTML  = error_text ;

     if(error_text!="")  return false ;

       i_title .value=Crypto_encode(i_title .value, page_key) ;
       i_remark.value=Crypto_encode(i_remark.value, page_key) ;

     for(i=1 ; i<=i_count.value ; i++) {
	 i_id    =document.getElementById("Id_"          +i) ;
	 i_remark=document.getElementById("Remark_"      +i) ;
	 i_prescr=document.getElementById("Prescription_"+i) ;
	 i_categ =document.getElementById("Category_"    +i) ;
	 i_name  =document.getElementById("Name_"        +i) ;
	 i_ref   =document.getElementById("Reference_"   +i) ;

	 i_name  .value=i_prescr.options[i_prescr.selectedIndex].text ;

      if(i_ref.value=="")
       if(i_categ.options[i_categ.selectedIndex].value=="measurement")
	 i_ref   .value=i_categ.options[i_categ.selectedIndex].value ;

	 i_id    .value=Crypto_encode(i_id    .value, page_key) ;
	 i_name  .value=Crypto_encode(i_name  .value, page_key) ;
	 i_remark.value=Crypto_encode(i_remark.value, page_key) ;
     }    

                         return true ;
  } 

  function SetCategory(p_id, p_category)
  {
     var  id_id ;
     var  prescription_id ;
     var  i_prescription ;
     var  i_template ;

           
          id_id            =p_id.replace("Category","Id") ;
          prescription_id  =p_id.replace("Category","Prescription") ;
  	i_prescription     =document.getElementById(prescription_id) ;
  	i_template         =document.getElementById("C_"+p_category).cloneNode(true) ;
        i_template.id      =prescription_id ;
        i_template.hidden  =false ;
        i_template.onchange=function(e) {                 this.options[0].disabled=true ;
                                          SetPrescription(this.id, this.options[this.selectedIndex].value) ;  } ;
        i_prescription.parentNode.replaceChild(i_template, i_prescription) ;

  	   document.getElementById(id_id).value='0' ;

    return ;         
  } 

  function SetPrescription(p_id, p_prescription)
  {
     var  v_session ;
     var  v_form ;
     var  id_id ;
     var  show_id ;
     var  i_id ;
     var  i_show ;

           
       id_id      =p_id.replace("Prescription","Id") ;
  	i_id      =document.getElementById(id_id) ;
        i_id.value=p_prescription ;

          show_id      =p_id.replace("Prescription","Details") ;
  	i_show         =document.getElementById(show_id) ;
        i_show.disabled=false ;

	 v_session=TransitContext("restore","session","") ;
            v_form="prescription_details_any.php" ;
	parent.frames["details"].location.assign(v_form+"?Session="+v_session+"&Id="+p_prescription) ;

    return ;         
  } 

  function AddListRow(p_id, p_category, p_remark, p_reference)
  {
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_cat_new ;
     var  i_prs_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_upp_new ;
     var  num_new ;
     var  fixed ; 


     if(p_id=='0')  p_category='unregistered' ;

     if(p_category=="UNKNOWN") {
		p_category=a_prescriptions[p_id] ;
     }

     if(p_category=="measurement") {
		i_measurements.hidden=false ;
     }

     if(p_reference.indexOf(":")>=0)  fixed=true ;
     else                             fixed=false ;

       num_new=parseInt(i_count.value)+1 ;
                        i_count.value=num_new ;

       i_cat_new = document.getElementById("Category").cloneNode(true) ;
       i_cat_new . id      ='Category_'+num_new ;
       i_cat_new . hidden  = false ;
       i_cat_new . disabled=fixed ;
       i_cat_new . onchange= function(e) {                      this.options[0].disabled=true ;
                                           SetCategory(this.id, this.options[this.selectedIndex].value) ;  } ;
       i_cat_new.options[0].disabled=true ;
       i_cat_new.options[0].selected=false ;

     for(i=1 ; i<i_cat_new.length ; i++)
       if(i_cat_new.options[i].value==p_category)  i_cat_new.options[i].selected=true ;

       i_prc_new         =document.getElementById("C_"+p_category).cloneNode(true) ;
       i_prc_new.id      ='Prescription_'+num_new ;
       i_prc_new.hidden  =false ;
       i_prc_new.disabled=fixed ;
       i_prc_new.onchange=function(e) {                 this.options[0].disabled=true ;
                                         SetPrescription(this.id, this.options[this.selectedIndex].value) ;  } ;

       i_prc_new.options[0].disabled=true ;
       i_prc_new.options[0].selected=false ;

     for(i=1 ; i<i_prc_new.length ; i++)
       if(i_prc_new.options[i].value==p_id)  i_prc_new.options[i].selected=true ;

       i_set     = document.getElementById("Prescriptions") ;
       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Order_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . disabled = true ;
       i_fld_new . style.textAlign="right" ;
       i_fld_new . size     =  1 ;
       i_fld_new . value    = num_new ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Id_'+ num_new ;
       i_fld_new . name     ='Id_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . hidden   = true ;
       i_fld_new . value    = p_id ;
       i_col_new . appendChild(i_fld_new) ;
       i_col_new . appendChild(i_cat_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_col_new . appendChild(i_prc_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Name_'+ num_new ;
       i_fld_new . name     ='Name_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . hidden   = true ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Remark_'+ num_new ;
       i_fld_new . name     ='Remark_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . size     =  80 ;
       i_fld_new . value    = p_remark ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Reference_'+ num_new ;
       i_fld_new . name     ='Reference_'+ num_new ;
       i_fld_new . type     ="hidden" ;
       i_fld_new . value    = p_reference ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type   ="button" ;
       i_shw_new . value  ="Подробнее" ;
       i_shw_new . id     ='Details_'+ num_new ;
       i_shw_new . onclick= function(e) {  ShowDetails(this.id) ;  }

     if(p_id=='0')
       i_shw_new . disabled= true ;

       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="Удалить" ;
       i_del_new . id     ='Delete_'+ num_new ;
       i_del_new . onclick= function(e) {  DeleteRow(this.id) ;  }
       i_upp_new = document.createElement("input") ;
       i_upp_new . type   ="button" ;
       i_upp_new . value  ="Вверх" ;
       i_upp_new . id     ='LiftUp_'+ num_new ;
       i_upp_new . onclick= function(e) {  LiftUpRow(this.id) ;  }
       i_col_new . appendChild(i_upp_new) ;
       i_col_new . appendChild(i_del_new) ;
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_set     . appendChild(i_row_new) ;

    return ;         
  } 

  function AddNewRow()
  {
     var  i_set ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_cat_new ;
     var  i_fld_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_del_new ;
     var  i_upp_new ;
     var  num_new ;

       num_new=parseInt(i_count.value)+1 ;
                        i_count.value=num_new ;

       i_cat_new = document.getElementById("Category").cloneNode(true) ;
       i_cat_new . id      ='Category_'+ num_new ;
       i_cat_new . hidden  = false ;
       i_cat_new . onchange= function(e) {                      this.options[0].disabled=true ;
                                           SetCategory(this.id, this.options[this.selectedIndex].value) ;  } ;

       i_set     = document.getElementById("Prescriptions") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Order_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . disabled = true ;
       i_fld_new . style.textAlign="right" ;
       i_fld_new . size     =  1 ;
       i_fld_new . value    = num_new ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Id_'+ num_new ;
       i_fld_new . name     ='Id_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . hidden   = true ;
       i_col_new . appendChild(i_fld_new) ;
       i_col_new . appendChild(i_cat_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_fld_new = document.createElement("select") ;
       i_fld_new . disabled = true ;
       i_fld_new . id       ='Prescription_'+ num_new ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Name_'+ num_new ;
       i_fld_new . name     ='Name_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . hidden   = true ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("br") ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Remark_'+ num_new ;
       i_fld_new . name     ='Remark_'+ num_new ;
       i_fld_new . type     ="text" ;
       i_fld_new . size     =  80 ;
       i_col_new . appendChild(i_fld_new) ;
       i_fld_new = document.createElement("input") ;
       i_fld_new . id       ='Reference_'+ num_new ;
       i_fld_new . name     ='Reference_'+ num_new ;
       i_fld_new . type     ="hidden" ;
       i_col_new . appendChild(i_fld_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_shw_new = document.createElement("input") ;
       i_shw_new . type    ="button" ;
       i_shw_new . value   ="Подробнее" ;
       i_shw_new . id      ='Details_'+ num_new ;
       i_shw_new . disabled= true ;
       i_shw_new . onclick = function(e) {  ShowDetails(this.id) ;  }
       i_del_new = document.createElement("input") ;
       i_del_new . type   ="button" ;
       i_del_new . value  ="Удалить" ;
       i_del_new . id     ='Delete_'+ num_new ;
       i_del_new . onclick= function(e) {  DeleteRow(this.id) ;  }
       i_upp_new = document.createElement("input") ;
       i_upp_new . type   ="button" ;
       i_upp_new . value  ="Вверх" ;
       i_upp_new . id     ='LiftUp_'+ num_new ;
       i_upp_new . onclick= function(e) {  LiftUpRow(this.id) ;  }
       i_col_new . appendChild(i_upp_new) ;
       i_col_new . appendChild(i_del_new) ;
       i_col_new . appendChild(i_shw_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_set     . appendChild(i_row_new) ;

    return ;         
  } 

  function DeleteRow(p_id)
  {
    var  i_ref  ;
    var  i_del  ;
    var  i_col  ;
    var  i_row  ;
    var  i_list  ;
    var  i_elm  ;
    var  reply  ;
    var  top  ;
    var  bottom  ;
    var  a_names ;


	 i_ref =document.getElementById(p_id.replace("Delete","Reference")) ;
      if(i_ref.value.indexOf(":")>0)
      {
            reply=confirm("При удалении данного назначения будет удалена также вся история сделанных по нему измерений. Удалить назначение?") ;
         if(reply==false)  return ;
      }

	 i_elm =document.getElementById(p_id.replace("Delete","Order")) ;
           top =parseInt(i_elm.value) ;         
        bottom =parseInt(i_count.value) ;

         i_del =document.getElementById(p_id) ;
         i_col =   i_del.parentNode ;
         i_row =   i_col.parentNode ;
         i_list=   i_row.parentNode ;

         i_list.removeChild(i_row) ;

       a_names=["Order","Id","Category","Prescription","Name","Remark","Reference","Details","Delete","LiftUp"] ;

     for(i=top+1 ; i<=bottom ; i++) {
     for(j in a_names) {
			       i_elm      =document.getElementById(a_names[j]+"_"+i) ;
			       i_elm.id   =a_names[j]+"_"+(i-1) ;
			       i_elm.name =a_names[j]+"_"+(i-1) ;
      if(a_names[j]=="Order")  i_elm.value= i-1 ;
                       }  
                                    } 

         i_count.value=i_count.value-1 ;

     return ;
  } 

  function LiftUpRow(p_id)
  {
    var  i_btn  ;
    var  i_col  ;
    var  i_row_1 ;
    var  i_row_2 ;
    var  i_list  ;
    var  i_elm  ;
    var  a_names ;
    var  down ;
    var  up ;


          i_elm =document.getElementById(p_id.replace("LiftUp","Order")) ;
           down =parseInt(i_elm.value) ;         

    if(down<=1)  return ;

             up =down-1 ;

         i_btn  =document  .getElementById(p_id) ;
         i_col  =   i_btn  .parentNode ;
         i_row_2=   i_col  .parentNode ;
         i_list =   i_row_2.parentNode ;

         i_btn  =document  .getElementById("LiftUp_"+up) ;
         i_col  =   i_btn  .parentNode ;
         i_row_1=   i_col  .parentNode ;

         i_list.insertBefore(i_row_2, i_row_1) ;

       a_names=["Order","Id","Category","Prescription","Name","Remark","Reference","Details","Delete","LiftUp"] ;

     for(j in a_names) {
			 i_row_1      =document.getElementById(a_names[j]+"_"+up  ) ;
			 i_row_2      =document.getElementById(a_names[j]+"_"+down) ;
			 i_row_1.id   = a_names[j]+"_"+down ;
			 i_row_1.name = a_names[j]+"_"+down ;
			 i_row_2.id   = a_names[j]+"_"+up ;
			 i_row_2.name = a_names[j]+"_"+up ;
                          
      if(a_names[j]=="Order") {
                         i_row_1.value=down ;
                         i_row_2.value=up  ;
                              }
                       }  

     return ;
  } 

  function ShowDetails(p_id)
  {
    var  id_id ;
    var  i_id ;
    var  v_session ;
    var  v_form ;

           
             id_id=p_id.replace("Details","Id") ;
  	      i_id=document.getElementById(id_id) ;

	 v_session=TransitContext("restore","session","") ;
	    v_form="prescription_details_any.php" ;

	parent.frames["details"].location.assign(v_form+"?Id="+i_id.value) ;

    return ;         
  } 

  function SetSelect(p_id)
  {
    var  v_session ;
    var  v_form ;

      i_sets_list.options[0].disabled=true ;

	 v_session=TransitContext("restore","session","") ;
	    v_form="set_details_any.php" ;

	parent.frames["details"].location.assign(v_form+"?Session="+v_session+"&Id="+p_id) ;
  } 

  function TestFrame(p_id, p_remark)
  {
	alert("Test "+p_id+" "+p_remark) ;
  } 

  function ShowMeasurements()
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

      location.assign("measurements_view.php?Session="+v_session+"&Owner="+page_owner+"&Page="+page_num) ;
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
        <b>СТРАНИЦА НАЗНАЧЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div><br></div>
  <form onsubmit="return SendFields();" method="POST" id="Form">
  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"> </td>
      <td> 
        <input type="submit" value="Сохранить" id="Save1"> 
        <input type="checkbox" value="1" name="Publish" id="Publish"> Передать пациенту
      </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
      <td class="field"> Заголовок </td>
      <td> <input type="text" size=60 name="Title" id="Title"> </td>
    </tr>
    <tr>
      <td class="field"> Примечание </td>
      <td> 
        <textarea cols=60 rows=7 wrap="soft" name="Remark" id="Remark"></textarea>
      </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td> 
        <input type="button" hidden value="Результаты контрольных измерений" id="Measurements" onclick=ShowMeasurements()> 
      </td>
    </tr>
    <tr>
      <td class="field"> </td>
      <td>
        <input type="hidden" name="Page"       id="Page"> 
        <input type="hidden" name="Check"      id="Check"> 
        <input type="hidden" name="Crypto"     id="Crypto">
        <input type="hidden" name="Update"     id="Update">
        <input type="hidden" name="Sign_s_key" id="Sign_s_key">
        <input type="hidden" name="Sign_r_key" id="Sign_r_key">
        <input type="hidden" name="Invite"     id="Invite">
        <input type="hidden" name="Letter"     id="Letter">
        <input type="hidden" name="InCopy"     id="InCopy">
        <input type="hidden" name="Id"         id="Id">
        <input type="hidden" name="Count"      id="Count">
      </td>
    </tr>
    </tbody>
  </table>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Prescriptions">
    </tbody>
  </table>

      <input type="button" value="Добавить назначение" onclick=AddNewRow()>
      <br>
      <br>
      <dev>Комплексы назначений:</dev>
      <select id="SetsList"></select>
      <br>
      <br>

    <select hidden id="Category"         ></select>
    <select hidden id="C_exercise"       ></select>
    <select hidden id="C_exploration"    ></select>
    <select hidden id="C_measurement"    ></select>
    <select hidden id="C_operation"      ></select>
    <select hidden id="C_others"         ></select>
    <select hidden id="C_pharmacotherapy"></select>
    <select hidden id="C_test"           ></select>
    <select hidden id="C_treatment"      ></select>
    <select hidden id="C_unregistered"   ></select>

  </form>

</div>

</body>

</html>
