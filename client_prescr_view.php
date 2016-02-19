<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Client_prescr_view.php" ;

  require("stdlib.php") ;

//============================================== 
//  �������� � ������ ��������������� � ��

function ProcessDB() {

//--------------------------- ���������� ������������

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         return ;
  }
//--------------------------- ���������� ����������

                         $session=$_GET["Session"] ;
                           $owner=$_GET["Owner"] ;
                            $page=$_GET["Page"] ;

    FileLog("START", "Session:".$session) ;
    FileLog("",      "  Owner:".$owner) ;
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

          $owner_  =$db->real_escape_string($owner) ;
          $user_   =$db->real_escape_string($user ) ;
          $page_   =$db->real_escape_string($page ) ;

//--------------------------- ���������� ����� ��������

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
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ����������� ����� �������") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

      echo     "   page_num  ='" .$page."' ;		\n" ;
      echo     "   page_owner='" .$owner."' ;		\n" ;
      echo     "   page_key  ='" .$fields[0]."' ;	\n" ;

//--------------------------- ���������� ������ ��������

                       $sql="Select p.title, p.remark, p.creator, CONCAT_WS(' ', d.name_f,d.name_i,d.name_o)".
                            "  From client_pages p, doctor_page_main d".
                            " Where d.owner=p.creator".
			    "  and  p.owner='$owner_'".
                            "  and  p.page = $page_" ;
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

                   $title    =$fields[0] ;
                   $remark   =$fields[1] ;
                   $creator  =$fields[2] ;
                   $creator_n=$fields[3] ;

        FileLog("", "User ".$owner." additional page ".$page_." presented successfully") ;

//--------------------------- ���������� ������ ����������

                     $sql="Select prescription_id, name, remark, `type`, if(reference=0,id,reference)".
			  "  From prescriptions_pages".
                          " Where owner='$owner_'".
                          "  and  page = $page_".
                          " Order by order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_PAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ������ ����������") ;
                         return ;
  }
  else
  {  
     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "   a_plist_id    [".($i+1)."]='".$fields[0]."' ;	\n" ;
       echo "   a_plist_name  [".($i+1)."]='".$fields[1]."' ;	\n" ;
       echo "   a_plist_remark[".($i+1)."]='".$fields[2]."' ;	\n" ;
       echo "   a_plist_type  [".($i+1)."]='".$fields[3]."' ;	\n" ;
       echo "   a_plist_ref   [".($i+1)."]='".$fields[4]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- ����������� ������ �� ��������

      echo     "    creator          ='".$creator  ."'	;\n" ;
      echo     "  i_title  .innerHTML='".$title    ."'	;\n" ;
      echo     "  i_creator.innerHTML='".$creator_n."'	;\n" ;
      echo     "  i_remark .innerHTML='".$remark   ."'	;\n" ;

//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\n" ;
    echo  "return ;				\n" ;
}

//============================================== 
//  ������ ��������������� ��������� �� WEB-��������

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

<title>DarkMed Client Prescriptions View</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_title ;
    var  i_creator ;
    var  i_remark ;
    var  i_set ;
    var  i_error ;
    var  creator ;
    var  page_owner ;
    var  page_num ;

    var  a_plist_id ;
    var  a_plist_name ;
    var  a_plist_remark ;
    var  a_plist_type ;
    var  a_plist_ref ;


  function FirstField() 
  {
    var  password ;
    var  page_key ;
    var  prescr_id ;
    var  prescr_name ;
    var  prescr_remark ;

       i_title  =document.getElementById("Title") ;
       i_creator=document.getElementById("Creator") ;
       i_remark =document.getElementById("Remark") ;
       i_set    =document.getElementById("Prescriptions") ;
       i_error  =document.getElementById("Error") ;

	a_plist_id    =new Array() ;
	a_plist_name  =new Array() ;
	a_plist_remark=new Array() ;
	a_plist_type  =new Array() ;
	a_plist_ref   =new Array() ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;

       page_key= Crypto_decode( page_key, password) ;

       i_title .innerHTML=Crypto_decode(i_title .innerHTML, page_key) ;
       i_remark.innerHTML=Crypto_decode(i_remark.innerHTML, page_key) ;

       for(i in a_plist_id) {
             prescr_id    =Crypto_decode(a_plist_id    [i], page_key) ;
             prescr_name  =Crypto_decode(a_plist_name  [i], page_key) ;
             prescr_remark=Crypto_decode(a_plist_remark[i], page_key) ;

          AddListRow(i, prescr_id, prescr_name, prescr_remark, a_plist_type[i], a_plist_ref[i]) ;
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

  function AddListRow(p_order, p_id, p_name, p_remark, p_type, p_ref)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_shw_new ;
     var  i_msr_new ;
     var  i_msr_list ;
     var  measurement ;


     if(p_type=="measurement")  measurement=true ;
     else                       measurement=false ;

	i_row_new = document.createElement("tr") ;
	i_row_new . className = "table" ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_txt_new = document.createTextNode(p_order) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
  if(p_id!="0")
	i_txt_new = document.createTextNode(p_name) ;
  else	i_txt_new = document.createTextNode(p_remark) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
  if(p_id!="0") {
	i_txt_new = document.createTextNode(p_remark) ;
	i_col_new . appendChild(i_txt_new) ;
  }
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = "table" ;
	i_shw_new = document.createElement("input") ;
	i_shw_new . type   ="button" ;
	i_shw_new . value  ="���������" ;
	i_shw_new . id     ='Details_'+ p_order ;
	i_shw_new . onclick= function(e) {  ShowDetails(p_id) ;  }
	i_msr_new = document.createElement("input") ;
	i_msr_new . type   ="button" ;
	i_msr_new . value  ="������� ������" ;
	i_msr_new . id     ='Measurement_'+ p_order ;
	i_msr_new . onclick= function(e) {  CheckMeasurements(p_ref) ;  }
  if(p_id!="0")
	i_col_new . appendChild(i_shw_new) ;
  if(measurement)
	i_col_new . appendChild(i_msr_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_set     . appendChild(i_row_new) ;

  if(measurement) 
  {
	i_msr_list=document.getElementById("MeasurementsList") ;
     if(i_msr_list.hidden==true)  i_msr_list.hidden=false ;
  }

    return ;         
  } 

  function ShowDetails(p_id)
  {
    window.open("prescription_view.php?Id="+p_id) ;
  }

  function WhoIsIt()
  {
    var  v_session ;

         v_session=TransitContext("restore","session","") ;

    window.open("doctor_view.php"+"?Session="+v_session+"&Owner="+creator) ;
  } 

  function ChatWith()
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

	location.assign("messages_chat_lr.php?Session="+v_session+"&Sender="+creator) ;
  }

  function CheckMeasurements(p_ref)
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

     if(p_ref==null) 	             location.assign("measurements_check.php?Session="+v_session+"&Owner="+page_owner+"&Page="+page_num) ;
     else   parent.frames["details"].location.assign("measurement_check_details.php?Session="+v_session+"&Owner="+page_owner+"&Page="+page_num+"&Reference="+p_ref) ;
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
        <b>�������� ���������� (��������)</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div class="error" id="Error"></div>
  <form onsubmit="return SendFields();" method="POST" id="Form">

  <b><div class="fieldC" id="Title"></div></b>
  <br>
  <div class="fieldC">
    <span><b>����: </b></span>
    <span id="Creator"></span>
    <input type="button" value="��� ���?" onclick=WhoIsIt()>
    <input type="button" value="���������" onclick=ChatWith()>
  </div>
  <br>
  <div left=5m id="Remark"></div> 
  <br>

  <div class="fieldC" hidden id="MeasurementsList">
    <input type="button" value="������� ����������� ���������" onclick=CheckMeasurements(null)>
    <br>
    <br>
  </div>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Prescriptions">
    </tbody>
  </table>

  </form>

</div>

</body>

</html>
