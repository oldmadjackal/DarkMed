<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script ="Client_page_pilot.php" ;
   $glb_log_off= true ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ��������������� � ��

function ProcessDB() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;

//--------------------------- ���������� ������������

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         return ;
  }
//--------------------------- ���������� ����������

                         $session=$_GET["Session"] ;
                            $page=$_GET["Page"] ;
                         $filekey=$_GET["Key"] ;

    FileLog("START", "Session:".$session) ;
    FileLog("",      "   Page:".$page) ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ������������� ������

     $user=DbCheckSession($db, $session, $options, $error) ;
  if($user===false) {
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ���������� ����������

           $user_=$db->real_escape_string($user ) ;
           $page_=$db->real_escape_string($page ) ;

//--------------------------- ���������� ����� ��������

                       $sql="Select  crypto ".
                            "  From `access_list` ".
                            " Where `owner`='$user_' ".
                            "  and  `login`='$user_' ".
                            "  and  `page` =$page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select ACCESS_LIST...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ����������� ����� �������") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_key='" .$fields[0]."' ;\n" ;

//--------------------------- ���������� ������ ��������

                       $sql="Select id, `check`, title, remark".
                            "  From client_pages".
                            " Where owner='$user_'".
                            "  and  page = $page_" ;
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "DB query(Select CLIENT_PAGES...) : ".$db->error) ;
                            $db->rollback();
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $page_id=$fields[0] ;
                   $check  =$fields[1] ;
                   $title  =$fields[2] ;
                   $remark =$fields[3] ;

        FileLog("", "User ".$user." additional page ".$page." presented successfully") ;

//--------------------------- ����������� ������ �� ��������

      echo     "  remark='".$remark   ."'	;\n" ;

//--------------------------- ���������� �������������� ������

        $tmp_folder=PrepareTmpFolder($session) ;
     if($tmp_folder=="") {
             FileLog("ERROR", "Temporary folder create error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ �������� ��������� �����") ;
                         return ;
     }

                     $sql="Select e.id, e.type, e.remark, e.file, e.short_file, e.www_link".
			  "  From client_pages_ext e".
			  " Where e.page_id='$page_id'".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select CLIENT_PAGE_EXT... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������������ ��������") ;
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
              ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������������ ����� ��������") ;
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

  }

     $res->close() ;

//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ����������� �������������� ������ ��������

function ShowExtensions() {

  global  $sys_ext_count  ;
  global  $sys_ext_id     ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;

         $remark_show=true ;
  
  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
        $row=$i ;

    if($sys_ext_type[$i]=="Image") {
       echo "<img src='".$sys_ext_sfile[$i]."' height=150 id='Image_".$row."'>	\n" ;
               $remark_show=false ;
    }
  }

  if($remark_show==true)  echo  " <div id='Remark'></div>	\n" ;

}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;\n" ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function InfoMsg($text) {

    echo  "i_error.style.color='blue' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
}

//============================================== 
//  ������ ��������� �� �������� �����������

function SuccessMsg() {

    echo  "i_error.style.color='green' ;			\n" ;
    echo  "i_error.innerHTML  ='������ ������� ���������!' ;	\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Client Page Pilot</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css") ;
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_remark ;
    var  i_error ;
    var  session ;
    var  password ;
    var  page_key ;
    var  check_key ;
    var  remark ;

  function FirstField() 
  {
    var  v_session ;
    var  i_ext ;
    var  text ;

	i_remark=document.getElementById("Remark") ;
	i_error =document.getElementById("Error") ;

           page_key="" ;

<?php
            ProcessDB() ;
?>

     if(i_remark!=null)
     {             
         password=TransitContext("restore", "password", "") ;
         page_key= Crypto_decode( page_key, password) ;

       i_remark.innerHTML=Crypto_decode(remark, page_key) ;
     }

//	parent.frames["processor"].location.assign("z_clear_tmp.php?Session="+session) ;

         return true ;
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

 <div class="error" id="Error"></div>

<?php
            ShowExtensions() ;
?>

</body>

</html>
