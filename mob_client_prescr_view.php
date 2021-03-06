<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Mob_Client_prescr_view.php" ;

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
    echo  "return ;\n" ;
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
  @import url("mob_common.css")
</style>

<script src="CryptoJS/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_title ;
    var  i_creator ;
    var  i_remark ;
    var  i_pres_list ;
    var  i_pres_title ;
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
       i_pres_list =document.getElementById("List") ;
       i_pres_tiles=document.getElementById("Tiles") ;
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
             a_plist_id    [i]=Crypto_decode(a_plist_id    [i], page_key) ;
             a_plist_name  [i]=Crypto_decode(a_plist_name  [i], page_key) ;
             a_plist_remark[i]=Crypto_decode(a_plist_remark[i], page_key) ;
       }

                ShowPrescriptions() ;
       
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

  function ShowPrescriptions() 
  {
       
    if(i_pres_tiles.checked==true)  presentation="Tiles" ;
    else	                    presentation="List" ;

       for(i in a_plist_id) {
      	    i_row=document.getElementById("Row_"+a_plist_id[i]) ;
  	 if(i_row!=null)  i_set.removeChild(i_row) ;
       }

       for(i in a_plist_id)
         if(presentation=="Tiles")
                  AddListRow_tiles(i, a_plist_id[i], a_plist_name[i], a_plist_remark[i], a_plist_type[i], a_plist_ref[i]) ;
         else     AddListRow_list (i, a_plist_id[i], a_plist_name[i], a_plist_remark[i], a_plist_type[i], a_plist_ref[i]) ;

  }

  function AddListRow_tiles(p_order, p_id, p_name, p_remark, p_type, p_ref)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_elm_new ;
     var  i_frm_new ;
     var  i_shw_new ;
     var  i_msr_new ;
     var  i_msr_list ;
     var  style ;
     var  msr_flag ;
     var  col ;


		  i_row_new = document.createElement("tr") ;
		  i_row_new . className = "table" ;
		  i_row_new . id        = "Row_"+p_id ;

     if(p_type=="measurement") {  msr_flag=true ;
                                     style="tableM" ;  }
     else                      {  msr_flag=false ;
                                     style="table" ;   }

		  i_col_new = document.createElement("td") ;
		  i_col_new . className = style ;
		  i_txt_new = document.createTextNode(p_order) ;
		  i_col_new . appendChild(i_txt_new) ;
		  i_row_new . appendChild(i_col_new) ;

		  i_col_new = document.createElement("td") ;
		  i_col_new . className = style ;
		  i_col_new . id = p_id ;
  if(p_id!="0")   i_col_new . onclick  = function(e) {  ShowDetails(this.id) ;  } ;
  if(p_id!="0")   i_txt_new = document.createTextNode(p_name) ;
  else		  i_txt_new = document.createTextNode(p_remark) ;
		  i_col_new . appendChild(i_txt_new) ;

  if(p_id!="0") {
		  i_elm_new = document.createElement("br") ;
		  i_col_new . appendChild(i_elm_new) ;
		  i_elm_new = document.createElement("br") ;
		  i_col_new . appendChild(i_elm_new) ;
		  i_txt_new = document.createTextNode(p_remark) ;
		  i_col_new . appendChild(i_txt_new) ;
		}

		  i_row_new . appendChild(i_col_new) ;

		  i_col_new = document.createElement("td") ;
		  i_col_new . className = style ;
  if(msr_flag ) {
		  i_elm_new = document.createElement("br") ;
		  i_col_new . appendChild(i_elm_new) ;
		  i_msr_new = document.createElement("input") ;
		  i_msr_new . type   ="button" ;
		  i_msr_new . value  ="������� ������" ;
		  i_msr_new . id     = p_ref ;
		  i_msr_new . onclick= function(e) {  CheckMeasurements(this.id) ;  }
		  i_col_new . appendChild(i_msr_new) ;
		}
  else          { 
		  i_frm_new = document.createElement("iframe") ;
		  i_frm_new . src         ="prescription_pilot.php?Id="+p_id ;
		  i_frm_new . seamless    = true ;
		  i_frm_new . height      ="202" ;
		  i_frm_new . scrolling   ="no" ;
		  i_frm_new . frameborder ="0" ;
		  i_frm_new . marginheight="0" ;
		  i_frm_new . marginwidth ="0" ;
		  i_col_new . appendChild(i_frm_new) ;
		}

		  i_row_new . appendChild(i_col_new) ;

    if(msr_flag) 
    {
         i_msr_list=document.getElementById("MeasurementsList") ;
      if(i_msr_list.hidden==true)  i_msr_list.hidden=false ;
    }

		  i_set.appendChild(i_row_new) ;

    return ;         
  } 

  function AddListRow_list(p_order, p_id, p_name, p_remark, p_type, p_ref)
  {
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_msr_new ;
     var  i_msr_list ;
     var  style ;
     var  msr_flag ;


     if(p_type=="measurement") {  msr_flag=true ;
                                     style="tableM" ;  }
     else                      {  msr_flag=false ;
                                     style="table" ;   }

	i_row_new = document.createElement("tr") ;
	i_row_new . className = style ;
        i_row_new . id        = "Row_"+p_id ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = style ;
	i_txt_new = document.createTextNode(p_order) ;
	i_col_new . appendChild(i_txt_new) ;
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = style ;
  if(p_id!="0")
	i_txt_new = document.createTextNode(p_name) ;
  else	i_txt_new = document.createTextNode(p_remark) ;
	i_col_new . appendChild(i_txt_new) ;
	i_col_new . onclick= function(e) {  ShowDetails(p_id) ;  }
	i_row_new . appendChild(i_col_new) ;

	i_col_new = document.createElement("td") ;
	i_col_new . className = style ;
  if(p_id!="0") {
	i_txt_new = document.createTextNode(p_remark) ;
	i_col_new . appendChild(i_txt_new) ;
  }
	i_msr_new = document.createElement("input") ;
	i_msr_new . type   ="button" ;
	i_msr_new . value  ="������� ������" ;
	i_msr_new . id     ='Measurement_'+ p_order ;
	i_msr_new . onclick= function(e) {  CheckMeasurements(p_ref) ;  }
  if(msr_flag) {
	i_txt_new = document.createElement("br") ;
	i_col_new . appendChild(i_txt_new) ;
	i_col_new . appendChild(i_msr_new) ;
  }
	i_row_new . appendChild(i_col_new) ;
	i_set     . appendChild(i_row_new) ;

  if(msr_flag) 
  {
	i_msr_list=document.getElementById("MeasurementsList") ;
     if(i_msr_list.hidden==true)  i_msr_list.hidden=false ;
  }

    return ;         
  } 

  function ShowDetails(p_id)
  {
    window.open("mob_prescription_view.php?Id="+p_id) ;
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

	location.replace("mob_chat.php?Session="+v_session+"&Sender="+creator) ;
  }

  function CheckMeasurements(p_ref)
  {
    var  v_session ;

	 v_session=TransitContext("restore","session","") ;

     if(p_ref==null)  location.assign("mob_measurements_check.php?Session="+v_session+"&Owner="+page_owner+"&Page="+page_num) ;
     else             location.assign("mob_measurement_check.php?Session="+v_session+"&Owner="+page_owner+"&Page="+page_num+"&Reference="+p_ref) ;
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
        <input type="button" value="?" onclick=GoToHelp()     id="GoToHelp"> 
        <input hidden type="button" value="!" onclick=GoToCallBack() id="GoToCallBack"> 
      </td> 
      <td class="title"> 
        <b id="Title"></b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div class="error" id="Error"></div>

  <br>
  <div class="fieldC">
    <span><b>����: </b></span><span id="Creator"></span>
    <br>
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

  <table>
    <tbody>
      <tr>
        <td width="10%"> 
        <td width="10%"><input type="radio" style="transform: scale(4)" name="Type[]" id="List" checked onclick=ShowPrescriptions()></td> 
        <td> ������ ����������</td>
      </tr>
      <tr>
        <td width="10%"> 
        <td width="10%"><input type="radio" style="transform: scale(4)" name="Type[]" id="Tiles" onclick=ShowPrescriptions()></td> 
        <td> '������' ����������</td>
      </tr>
    </tbody>
  </table>

  <table width="100%">
    <tbody  id="Prescriptions">
    </tbody>
  </table>

</body>

</html>
