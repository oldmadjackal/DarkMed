<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Prescription_view.php" ;

  require("stdlib.php") ;

//============================================== 
//  ������ � ��

function ProcessDB() {

  global  $sys_ext_count  ;
  global  $sys_ext_user   ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_file   ;
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

                         $get_id=$_GET["Id"] ;

    FileLog("START", "    Id:".$get_id) ;

//--------------------------- ����������� ��

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- ���������� ������ ��� �����������

          $get_id_=$db->real_escape_string($get_id) ;

                       $sql="Select  r.id, r.user, t.name, r.name, r.reference, r.description, r.www_link, r.deseases".
                            "       ,d.name_f, d.name_i, d.name_o".
                            "  From  prescriptions_registry r".
                            "        inner join doctor_page_main d on d.owner=r.user".
                            "        inner join ref_prescriptions_types t on t.code=r.type and t.language='RU'".
                            " Where  r.id='$get_id_'" ; 
       $res=$db->query($sql) ;
    if($res===false) {
          FileLog("ERROR", "Select * from PRESCRIPTIONS_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ���������� ������") ;
                         return ;
    }

	      $fields=$res->fetch_row() ;
	              $res->close() ;

                   $put_id     =$fields[0] ;
                   $owner      =$fields[1] ;
                   $owner_name =$fields[8]." ".$fields[9]." ".$fields[10]." (".$fields[1].")" ;
                   $type       =$fields[2] ;
                   $name       =$fields[3] ;
                   $reference  =$fields[4] ;
                   $description=$fields[5] ;
                   $www_link   =$fields[6] ;
                   $deseases   =$fields[7] ;

        FileLog("", "Prescription data selected successfully") ;

//--------------------------- ���������� ������ ��������� �����������

  if($deseases!="")
  {
             $deseases_list=str_replace(" ", ",", $deseases) ;

                       $sql="Select name, grp, code, id".
                            "  from (".
                            "        Select ''name, d3.name grp, '0'code, d3.id".
                            "          From deseases_registry d3".
                            "         Where d3.type =  '0'".
                            "        union all".
                            "        Select d1.name, d2.name grp, d1.type code, d1.id".
                            "          From deseases_registry d1, deseases_registry d2".
                            "         Where d1.type = d2.id".
                            "        )list".
                            " Where id in (".$deseases_list.")".
                            " Order by grp, name" ;
       $res=$db->query($sql) ;
    if($res===false) {
            FileLog("ERROR", "Select DESEASES_REGISTRY... : ".$db->error) ;
                              $db->close() ;
           ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������� ������ ��������� �����������") ;
                           return ;
    }

    for($i=0 ; $i<$res->num_rows ; $i++)
    {
	      $fields=$res->fetch_row() ;

       echo "    dss_name ='".$fields[0]."' ;	\n" ;
       echo "    dss_group='".$fields[1]."' ;	\n" ;
       echo "    dss_gcode='".$fields[2]."' ;	\n" ;
       echo "    dss_id   ='".$fields[3]."' ;	\n" ;

       echo "  AddDesease(dss_id, dss_name, dss_group, dss_gcode) ;	\n" ;
    }

     $res->close() ;
  }
//--------------------------- ���������� �������������� ������

                     $sql="Select CONCAT_WS(' ', d.name_f, d.name_i, d.name_o)".
			  "      ,e.type, e.remark, e.file, e.short_file, e.www_link".
			  "  From prescriptions_ext e, doctor_page_main d".
			  " Where e.user=d.owner".
                          "  and  e.prescription_id='$get_id_'".
                          " Order by e.order_num" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_EXT... : ".$db->error) ;
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

          $sys_ext_user  [$i]=$fields[0] ;
          $sys_ext_type  [$i]=$fields[1] ;
          $sys_ext_remark[$i]=$fields[2] ;
          $sys_ext_file  [$i]=$fields[3] ;
          $sys_ext_sfile [$i]=$fields[4] ;
          $sys_ext_link  [$i]=$fields[5] ;
     }

  }

     $res->close() ;

//--------------------------- ����� ������ �� ��������

      echo     "                  creator='".$owner      ."' ;\n" ;

      echo     "  i_id         .innerHTML='".$get_id     ."' ;\n" ;
      echo     "  i_owner      .innerHTML='".$owner_name ."' ;\n" ;
      echo     "  i_name       .innerHTML='".$name       ."' ;\n" ;
      echo     "  i_type       .innerHTML='".$type       ."' ;\n" ;
      echo     "  i_reference  .innerHTML='".$reference  ."' ;\n" ;
      echo     "  i_description.innerHTML='".$description."' ;\n" ;
      echo     "  i_www_link   .value    ='".$www_link   ."' ;\n" ;

//      echo     "  SetType('".$type."') ;\n" ;

//--------------------------- ����������

     $db->close() ;

        FileLog("STOP", "Done") ;
}
//============================================== 
//  ����������� �������������� ������ ��������

function ShowExtensions() {

  global  $sys_ext_count  ;
  global  $sys_ext_user   ;
  global  $sys_ext_type   ;
  global  $sys_ext_remark ;
  global  $sys_ext_file   ;
  global  $sys_ext_sfile  ;
  global  $sys_ext_link   ;


  for($i=0 ; $i<$sys_ext_count ; $i++)
  {
       echo  "  <tr class='table'>				\n" ;
       echo  "    <td  class='table' width='20%'>		\n" ;
       echo  $sys_ext_user[$i] ;
       echo  "    </td>						\n" ;
       echo  "    <td class='table'>				\n" ;
       echo  "      <div>					\n" ;
       echo  htmlspecialchars(stripslashes($sys_ext_remark[$i]), ENT_COMPAT, "windows-1251") ;
       echo  "      </div>					\n" ;
       echo  "    <br>						\n" ;

    if($sys_ext_type[$i]=="Image") {
       echo "<div class='fieldC'>					\n" ; 
       echo "<img src='".$sys_ext_sfile[$i]."' height=200		\n" ;
       echo " onclick=\"window.open('".$sys_ext_file[$i]."')\" ;	\n" ;
       echo ">								\n" ; 
       echo "</div>							\n" ; 
       echo "<br>							\n" ;
    }

    if($sys_ext_type[$i]=="File") {
       echo  "  <a href='".$sys_ext_file[$i]."'>������ �� ����</a>	\n" ; 
    }

    if($sys_ext_type[$i]=="Link") {

                        $name=$sys_ext_link[$i] ;
                         $pos= strpos($name, "://") ;
      if($pos!==false)  $name= substr($name, $pos+3) ;
                         $pos= strpos($name, "/") ;
      if($pos!==false)  $name= substr($name, 0, $pos) ;

       echo  "  <a href='#' onclick=window.open('".$sys_ext_link[$i]."')>".$name."</a>	\n" ; 
       echo  "  <br>									\n" ;
    }

       echo  "    </td>						\n" ;
       echo  "  </tr>						\n" ;
  }

}

//============================================== 
//  ������ ��������� �� ������ �� WEB-��������

function ErrorMsg($text) {

    echo  "i_error.style.color='red' ;		\r\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\r\n" ;
    echo  "return ;" ;
}

//============================================== 
//  ������ ��������� �� �������� �����������

function SuccessMsg() {

    echo  "i_error.style.color='green' ;			\r\n" ;
    echo  "i_error.innerHTML  ='������ ������� ���������!' ;	\r\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Prescription Registry View</title>
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
    var  i_goto ;
    var  i_error ;

    var  creator ;

    var  a_types ;


  function FirstField() 
  {
     var  nl=new RegExp("@@","g") ;
     var  text ;
     var  pos ;

       i_table      =document.getElementById("Fields") ;
       i_id         =document.getElementById("Id") ;
       i_owner      =document.getElementById("Owner") ;
       i_type       =document.getElementById("Type") ;
       i_name       =document.getElementById("Name") ;
       i_reference  =document.getElementById("Reference") ;
       i_description=document.getElementById("Description") ;
       i_www_link   =document.getElementById("WWW_link") ;
       i_goto       =document.getElementById("GoToLink") ;
       i_error      =document.getElementById("Error") ;

	a_types=new Array() ;

<?php
            ProcessDB() ;
?>

                text=i_www_link.value ;
                 pos=text.indexOf("://") ;
    if(pos>=0)  text=text.substr(pos+3) ;
                 pos=text.indexOf("/") ;
    if(pos>=0)  text=text.substr(0, pos) ;

    if(i_www_link.value!='')  i_goto.innerHTML=text ;
    else                      i_goto.hidden   =true ;

       i_description.innerHTML=i_description.innerHTML.replace(nl,"<br>") ;

         return true ;
  }

  function WhoIsIt()
  {
    window.open("doctor_view.php"+"?Owner="+creator) ;
  } 

  function AddDesease(p_id, p_name, p_group, p_gcode)
  {
     var  i_dss_list ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  v_id ;


                   v_id="Desease_"+p_id ;

       i_dss_list= document.getElementById("Deseases_list") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = "table" ;
       i_row_new . id        =  v_id ;

       i_col_new = document.createElement("td") ;

   if(p_gcode==0)
   {
       i_col_new . className = "tableG" ;
       i_txt_new = document.createTextNode(p_group) ;
       i_col_new . appendChild(i_txt_new) ;
   }
   else
   {
       i_col_new . className = "tableL" ;
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
   } 
       i_col_new . onclick= function(e) {
					    var  v_session ;
					    var  v_form ;
						 v_session=TransitContext("restore","session","") ;
									      v_form="desease_details_any.php" ;
						parent.frames["details"].location.replace(v_form+
                                                                                         "?Session="+v_session+
                                                                                         "&Id="+p_id) ;
					} ;
       i_row_new . appendChild(i_col_new) ;

       i_dss_list. appendChild(i_row_new) ;

    return ;         
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
        <b>�������� �������� ����������</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <form onsubmit="return SendFields();" method="POST">

  <table width="100%" >
    <thead>
    </thead>
    <tbody>
    <tr>
      <td> <div class="error" id="Error"></div> </td>
    </tr>
    <tr>
    <td class="table">

  <table width="100%" id="Fields">
    <thead>
    </thead>
    <tbody>
    <tr>
      <td class="field"><b> ���: </b></td>
      <td> <dev id="Id"></dev> </td>
    </tr>
    <tr>
      <td class="field"><b> �������: </b></td>
      <td> <span id="Owner"></span>
           <input type="button" value="��� ���?" onclick=WhoIsIt()></td>
      </td>
    </tr>
    <tr>
      <td class="field"><b> ���������: </b></td>
      <td> <div id="Type"></div> </td>
    </tr>
    <tr>
      <td class="field"><b> ��������: </b></td>
      <td> <div id="Name"></div> </td>
    </tr>
    <tr>
      <td class="field"><b> �������: </b></td>
      <td> <div id="Reference"></div> </td>
    </tr>
    <tr>
      <td class="field"><b> �������� ��: </b></td>
      <td>
         <a href="javascript:
                  window.open(document.getElementById('WWW_link').value) ;"
                     id="GoToLink">�������� ��</a>
          <input type="hidden" maxlength=510 id="WWW_link"> 
      </td>
    </tr>
    <tr>
      <td class="field"><b> ��������: </b></td>
      <td> <div id="Description"></div> </td>
    </tr>
    </tbody>
  </table>

      </td>
      <td class="table">
        <table width="100%">
          <thead>
          </thead>
          <tbody  id="Deseases_list">
          </tbody>
        </table>
      </td>
    </tr>
    </tbody>
  </table>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Extensions">

<?php
            ShowExtensions() ;
?>

    </tbody>
  </table>

  </form>

</div>

</body>

</html>
