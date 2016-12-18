<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_page_view.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;

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

                           $owner=$_GET ["Owner"] ;

                            $page=$_GET ["Page"] ;
  if(!isset($page    ))     $page=$_POST["Page"] ;

                         $filekey=$_POST["FileKey"] ;

                          $update=$_POST["Update"] ;
  if(isset($update   )) {
                          $crypto=$_POST["Crypto"] ;
                          $extkey=$_POST["ExtKey"] ;
                           $check=$_POST["Check"] ;
                          $remark=$_POST["Remark"] ;
                           $count=$_POST["Count"] ;
  }


    FileLog("START", "Session:".$session) ;
    FileLog("",      "  Owner:".$owner) ;
    FileLog("",      "   Page:".$page) ;

  if(isset($update   )) {
    FileLog("",      " Update:".$update) ;
    FileLog("",      "  Check:".$check) ;
    FileLog("",      " Crypto:".$crypto) ;
    FileLog("",      " ExtKey:".$extkey) ;
    FileLog("",      "  Count:".$count) ;
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
//--------------------------- Определение владельца страницы

  if(!isset($owner))  $owner=$user ; 

  if($owner!=$user)  $sys_read_only=true ; 
  else               $sys_read_only=false ; 

                        $owner_=$db->real_escape_string($owner) ;
                        $user_ =$db->real_escape_string($user ) ;
                        $page_ =$db->real_escape_string($page ) ;

//--------------------------- Режим запроса ключа шифрования файла

  if(!isset($filekey)) {

                       $sql="Select  crypto, ext_key ".
                            "  From `access_list` ".
                            " Where `owner`='$owner_' ".
                            "  and  `login`='$user_' ".
                            "  and  `page` =$page_" ;
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

      echo     "        page_key='" .$fields[0]."' ;	\n" ;
      echo     "         ext_key='" .$fields[1]."' ;	\n" ;
      echo     "  i_update.value='request' ;		\n" ;

      InfoMsg("Загружается информация...") ;

      FileLog("",     "File key request sent") ;     
      FileLog("STOP", "Done") ;     
         return ;
                       } 
//--------------------------- Приведение параметров

          $crypto_=$db->real_escape_string($crypto) ;
          $extkey_=$db->real_escape_string($extkey) ;
          $check_ =$db->real_escape_string($check ) ;

//--------------------------- Извлечение ключа страницы

                       $sql="Select  crypto, ext_key ".
                            "  From `access_list` ".
                            " Where `owner`='$owner_' ".
                            "  and  `login`='$user_' ".
                            "  and  `page` =$page_" ;
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

      echo     "   page_key='" .$fields[0]."' ;\n" ;
      echo     "    ext_key='" .$fields[1]."' ;\n" ;

//--------------------------- Извлечение данных страницы

                       $sql="Select id, `check`, title, remark".
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

                   $page_id=$fields[0] ;
                   $check  =$fields[1] ;
                   $title  =$fields[2] ;
                   $remark =$fields[3] ;

        FileLog("", "User ".$owner." additional page ".$page." presented successfully") ;

//--------------------------- Отображение данных на странице

      echo     "  i_check .value    ='".$check. "' ;\n" ;
      echo     "  i_title .innerHTML='".$title. "' ;\n" ;
      echo     "  i_remark.innerHTML='".$remark."' ;\n" ;
      echo     "  i_update.value    ='update' ;	\n" ;

//--------------------------- Извлечение дополнительных блоков

        $tmp_folder=PrepareTmpFolder($session) ;
     if($tmp_folder=="") {
             FileLog("ERROR", "Temporary folder create error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания временной папки") ;
                         return ;
     }

      echo     "  i_count    .value='0' ;	\n" ;

                     $sql="Select e.id, e.type, e.remark, e.file, e.short_file, e.www_link".
			  "  From client_pages_ext e".
			  " Where e.page_id='$page_id'".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGE_EXT... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка извлечения расширенного описания") ;
                         return ;
  }
  else
  {  
          $sys_ext_count=$res->num_rows ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

               $spath="" ;

        if($fields[1]=="Image")
        {
                     $spath=$tmp_folder."/".basename($fields[4]) ;
                copy($fields[4], $spath) ;

               $cur_folder=getcwd() ;
                            chdir($tmp_folder) ;

	     $spath  =DecryptFile($spath, $filekey) ;

                            chdir($cur_folder) ;

          if($spath===false) {
               FileLog("ERROR", "IMAGE/FILE small image decrypt error") ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка дешифрования файла картинки") ;
                           return ;
          }

		$spath=substr($spath, strlen($cur_folder)+1) ;
        }

          $sys_ext_id    [$i]= $fields[0] ;
          $sys_ext_type  [$i]= $fields[1] ;
          $sys_ext_remark[$i]= $fields[2] ;
          $sys_ext_sfile [$i]= $spath ;
          $sys_ext_link  [$i]= $fields[5] ;
     }

      echo     "  i_count    .value=".$res->num_rows." ;	\n" ;
  }

     $res->close() ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение дополнительных блоков описания

function ShowExtensions() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;


  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
        $row=$i ;

    if($row%3==0)
       echo  "  <tr id='Row_".$row."'>				\n" ;

       echo  "    <td  class='Table_LT' width='10%'>				\n" ;

    if($sys_ext_type[$i]=="Image") {
       echo "<br>								\n" ;
       echo "<div class='Normal_CT'>						\n" ;
       echo "<img src='".$sys_ext_sfile[$i]."' height=200 id='Image_".$row."'>	\n" ;
       echo "</div>								\n" ;
    }
       echo  "      <input type='hidden' id='Order_".$row."' value='".$row."'>			\n" ;
       echo  "      <input type='hidden' id='Ext_"  .$row."' value='".$sys_ext_id[$i]."'>	\n" ;
       echo  "      <input type='hidden' id='Type_" .$row."' value='".$sys_ext_type[$i]."'>	\n" ;
       echo  "    <br>										\n" ;
       echo  "      <div id='Remark_".$row."'>".$sys_ext_remark[$i]."</div>			\n" ;
       echo  "    <br>										\n" ;

    if($sys_ext_type[$i]=="File") {
       echo  "  <a href='#' id='File_".$row."'>Ссылка на файл</a>	\n" ; 
    }

    if($sys_ext_type[$i]=="Link") {
       echo  "  <a href='#' id='Link_".$row."' onclick=window.open('$LINK$')>".$sys_ext_link[$i]."</a>	\n" ; 
       echo  "  <br>											\n" ;
    }

       echo  "    </td>						\n" ;

    if($row%3==2 || $row==($sys_ext_count-1))
       echo  "  </tr>						\n" ;
  }

}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;\n" ;
}

//============================================== 
//  Выдача сообщения об ошибке на WEB-страницу

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

<title>DarkMed Client Page</title>
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
    var  i_page ;
    var  i_check ;
    var  i_crypto ;
    var  i_ext_key ;
    var  i_file_key ;
    var  i_title ;
    var  i_remark ;
    var  i_count ;
    var  i_update ;
    var  i_error ;
    var  session ;
    var  password ;
    var  page_key ;
    var  ext_key ;
    var  check_key ;

  function FirstField() 
  {
    var  v_session ;
    var  i_ext ;
    var  i_clr_call ;
    var  i_clear ;
    var  text ;


	i_table    =document.getElementById("Fields") ;
	i_page     =document.getElementById("Page") ;
	i_check    =document.getElementById("Check") ;
	i_crypto   =document.getElementById("Crypto") ;
	i_ext_key  =document.getElementById("ExtKey") ;
	i_file_key =document.getElementById("FileKey") ;
	i_title    =document.getElementById("Title") ;
	i_remark   =document.getElementById("Remark") ;
	i_count    =document.getElementById("Count") ;
	i_update   =document.getElementById("Update") ;
	i_error    =document.getElementById("Error") ;

           page_key="" ;
            ext_key="" ;

<?php
            ProcessDB() ;
?>

    if(i_update.value=='insert')
    {
       document.getElementById("AddExtension").disabled=true ;
        i_error.style.color='blue' ;
        i_error.innerHTML  ='Создание дополнительных блоков будет доступно после первого сохранения' ;
    }

       password=TransitContext("restore", "password", "") ;
        session=TransitContext("restore", "session",  "") ;

    if(ext_key!="")
    {
            ext_key      =Crypto_decode(ext_key, password) ;
         i_file_key.value= ext_key ;

      if(i_update.value=='request') {
                                      document.forms[0].submit() ;
                                          return(true) ;
                                    }
    }

    if(page_key!="")
    {
           page_key=Crypto_decode(page_key, password) ;

          check_key=Crypto_decode(i_check.value, page_key) ;

     if(!Check_validate(check_key)) 
     {
	i_error.style.color="red" ;
	i_error.innerHTML  ="Ошибка расшифровки данных." ;
         return true ;
     }

       i_title .innerHTML=Crypto_decode(i_title .innerHTML, page_key) ;
       i_remark.innerHTML=Crypto_decode(i_remark.innerHTML, page_key) ;

      for(i=0 ; i<i_count.value ; i++) {
           i_ext          =document.getElementById("Remark_"+i) ;
           i_ext.innerHTML=Crypto_decode(i_ext.innerHTML.replace(/(^\s+|\s+$)/g,''), page_key) ;
           i_ext.innerHTML=              i_ext.innerHTML.replace(/\n+/g,'<br>') ;

           i_ext=document.getElementById("Link_"+i) ;
        if(i_ext!=null) {
              text          = i_ext.innerHTML ;
              text          =Crypto_decode(text, page_key) ;
             i_ext.innerHTML= text ;
             i_ext.onclick  =function(e) { window.open(text) ; } ;
        }

           i_ext=document.getElementById("Image_"+i) ;
        if(i_ext!=null) {
               i_ext.onclick=function(e) {
					    var  id=document.getElementById(this.id.replace("Image", "Ext")).value ;
					   window.open("client_page_image.php?Session="+session+"&Image="+id+"&Key="+ext_key) ; 
                                         } ;
        }

           i_ext=document.getElementById("File_"+i) ;
        if(i_ext!=null) {
               i_ext.onclick=function(e) {
					    var  id=document.getElementById(this.id.replace("File", "Ext")).value ;
					   window.open("client_page_file.php?Session="+session+"&File="+id+"&Key="+ext_key) ; 
                                         } ;
        }
      }  
    }

	 v_session=TransitContext("restore","session","") ;

       i_clear    = document.getElementById("Clear") ;
       i_clr_call = document.createElement("iframe") ;
       i_clr_call . src         ="z_clear_tmp.php?Session="+v_session ;
       i_clr_call . seamless    = true ;
       i_clr_call . height      ="50" ;
       i_clr_call . width       ="50" ;
       i_clr_call . scrolling   ="no" ;
       i_clr_call . frameborder ="0" ;
       i_clr_call . marginheight="0" ;
       i_clr_call . marginwidth ="0" ;
       i_clear    . appendChild(i_clr_call) ;

         return true ;
  }

  function SendFields() 
  {
      if(i_update.value=='request')  return(true) ;

     return(false) ;
  } 

  function GoToLink(p_link) 
  {
    window.open(document.getElementById(p_link).value) ;
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
        <b><div id="Title"></div></b>
      </td> 
    </tr>
    </tbody>
  </table>

  <br>
  <div class="Error_CT" id="Error"></div>

  <i><div class="Normal_CT" id="Remark"></div></i>
  <br>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Extensions">

<?php
            ShowExtensions() ;
?>

    </tbody>
  </table>

  <div hidden id="Clear"></div>

  <form onsubmit="return SendFields();" method="POST"  enctype="multipart/form-data" id="Form">

        <input type="hidden" name="Page"    id="Page"> 
        <input type="hidden" name="Check"   id="Check"> 
        <input type="hidden" name="Crypto"  id="Crypto">
        <input type="hidden" name="ExtKey"  id="ExtKey">
        <input type="hidden" name="Update"  id="Update">
        <input type="hidden" name="FileKey" id="FileKey">
        <input type="hidden" name="Count"   id="Count">
  </form>

</body>

</html>
