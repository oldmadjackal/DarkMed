<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_page_file.php" ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ��������������� � ��

function ProcessDB() {

 global  $sys_show_frame ;
 global  $sys_session ;
 global  $sys_file ;
 global  $sys_key ;
 global  $sys_image_path ;

//--------------------------- ���������� ������������

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         return ;
  }
//--------------------------- ���������� ����������

                        $session=$_GET["Session"] ;
                        $file   =$_GET["File"] ;
                        $key    =$_GET["Key"] ;
                        $show   =$_GET["Show"] ;

    if(isset($show))  $sys_show_frame="0" ;
    else              $sys_show_frame="1" ;

  FileLog("START", "  Session:".$session) ;
  FileLog("",      "     File:".$file) ;
  FileLog("",      "     Show:".$sys_show_frame) ;
  FileLog("",      "      Key:".$key) ;

//--------------------------- ���� ��� �����

       $sys_session=$session ;
       $sys_file   =$file ;
       $sys_key    =$key  ;

    if($sys_show_frame)  return ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ��������� ���� � ����� ��������

        $file_=$db->real_escape_string($file) ;

                     $sql="Select e.file".
			  "  From client_pages_ext e".
			  " Where e.id='$file_'" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGE_EXT... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� �����") ;
                         return ;
  }

 	          $fields=$res->fetch_row() ;
               $file_path=$fields[0] ;

                 $res->close() ;

//--------------------------- ����������� �����

        $tmp_folder=PrepareTmpFolder($session."_".$file) ;
     if($tmp_folder=="") {
             FileLog("ERROR", "Temporary folder create error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ��������� �����") ;
                         return ;
     }

                     $path=$tmp_folder."/".basename($file_path) ;
                copy($file_path, $path) ;

               $cur_folder=getcwd() ;
                            chdir($tmp_folder) ;

	        $path=DecryptFile($path, $key) ;

                            chdir($cur_folder) ;

          if($path===false) {
               FileLog("ERROR", "IMAGE/FILE image decrypt error") ;
                       $db->rollback();
                       $db->close() ;
              ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������������ ����� ��������") ;
                           return ;
          }

		$path=substr($path, strlen($cur_folder)+1) ;

//--------------------------- ������������ �����������

    echo "  parent.frames['processor'].location.assign('z_clear_tmp.php?Session=".$session."_".$file."&Wait=30') ;	\n" ;
    echo "  location.assign('".$path."') ;										\n" ;

//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ����������� ������

function ShowFrame() {

 global  $sys_show_frame ;
 global  $sys_session ;
 global  $sys_file ;
 global  $sys_key ;


    if($sys_show_frame!="1")  return ;

  FileLog("DEBUG",      "ShowFrame ".$sys_show_frame) ;
  
	$picture="client_page_file.php?Session=".$sys_session."&File=".$sys_file."&Key=".$sys_key."&Show=1" ;

    echo  " <frameset rows='95%,*'>					\n" ;
    echo  "   <frame src='".$picture."' name='file'>			\n" ;
    echo  "   <frame src='start.html'  scrolling='no' name='processor'>	\n" ;
    echo  " </frameset>							\n" ;

}

//============================================== 
//  ����������� ��������

function ShowFile() {

 global  $sys_show_frame ;

    if($sys_show_frame!="0")  return ;

    echo  "  <body onload='FirstField();'>				\n" ;
    echo  "  <div><b>�������� ����� �������� �������������</b></div>	\n" ;
    echo  "  <div class='error' id='Error'></div>			\n" ;
    echo  "  </body>							\n" ;

}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;" ;
}

//============================================== 
//  ������ ��������� �� �������� ����������

function SuccessMsg() {

    echo  "i_error.style.color='green' ;				\n" ;
    echo  "i_error.innerHTML  ='��������� �������� ��� �����������' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Client image view</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_error ;

  function FirstField() 
  {
       i_error=document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>

         return true ;
  }


<?php
  require("common.inc") ;
?>


//-->
</script>

</head>


<noscript>
</noscript>

<?php
            ShowFrame() ;
            ShowFile () ;
?>

</html>
