<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Deseases_select.php" ;

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

                         $session =$_GET ["Session"] ;
  if(!isset($session ))  $session =$_POST["Session"] ;
                         $deseases=$_GET ["Deseases"] ;
  if(!isset($deseases))  $session =$_POST["Deseases"] ;

  FileLog("START", "    Session:".$session) ;
  FileLog("",      "   Deseases:".$deseases) ;

//--------------------------- Подключение БД

     $db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ;
                         return ;
  }
//--------------------------- Идентификация сессии

  if(!isset($session))  $session="" ;

                        $options="" ;

  if($session!="") {

       $user=DbCheckSession($db, $session, $options, $error) ;
                    FileLog("", "User Options:".$options) ;

    if($user===false) {
                       $db->close() ;
                    ErrorMsg($error) ;
                         return ;
    }
  }
//--------------------------- Формирование списка заболеваний

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
                          " Order by grp, name" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select DESEASES_REGISTRY... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра заболеваний") ;
                         return ;
  }
  if($res->num_rows==0) 
  {
          FileLog("", "Deseases registry is empty") ;
  }
  else
  {
           $deseases=" ".$deseases." " ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       echo "    dss_name ='".$fields[0]."' ;	\n" ;
       echo "    dss_group='".$fields[1]."' ;	\n" ;
       echo "    dss_gcode='".$fields[2]."' ;	\n" ;
       echo "    dss_id   ='".$fields[3]."' ;	\n" ;

       if(strpos($deseases, "".$fields[3]."")===false)  
              echo "  AddNewRow(".$i.", dss_name, dss_group, dss_gcode, dss_id, false) ;	\n" ;
       else   echo "  AddNewRow(".$i.", dss_name, dss_group, dss_gcode, dss_id, true ) ;	\n" ;
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

    echo  "i_error.style.color='red' ;		\r\n" ;
    echo  "i_error.innerHTML  ='".$text."' ;	\r\n" ;
    echo  "return ;" ;
}

//============================================== 
//  Выдача сообщения об успешной тработке

function SuccessMsg() {

    echo  "i_error.style.color='green' ;	\r\n" ;
    echo  "i_error.innerHTML  ='Выполнено.' ;	\r\n" ;
}
//============================================== 
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/html4/loose.dtd" >

<html>

<head>

<title>DarkMed Messages Deseases Select</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script type="text/javascript">
<!--

    var  i_error ;
    var  gshow_start ;
    var  gshow_end ;

  function FirstField() 
  {
    var  msg_text ;

       i_error   =document.getElementById("Error") ;

<?php
            ProcessDB() ;
?>
         return true ;
  }


     var  g_disabled ;

  function AddNewRow(p_num, p_name, p_group, p_gcode, p_id, p_checked)
  {
     var  i_deseases ;
     var  i_row_new ;
     var  i_col_new ;
     var  i_txt_new ;
     var  i_chk_new ;
     var  v_disabled ;
     var  v_class ;
 

   if(p_gcode==0) 
   {
                     v_class   ="tableG" ;
      if(p_checked)  g_disabled= true ;
      else           g_disabled= false ; 
                     v_disabled= false ;
   }
   else
   {
                     v_class   ="tableL" ;
                     v_disabled= g_disabled ;
   }

       i_deseases= document.getElementById("Deseases") ;

       i_row_new = document.createElement("tr") ;
       i_row_new . className = v_class ;
       i_row_new . id        = p_num ;

   if(p_gcode!=0 && p_checked===false)
       i_row_new . hidden    = true ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = "table" ;
       i_col_new . width="1%" ;

       i_chk_new = document.createElement("input") ;
       i_chk_new . type     ="checkbox" ;
       i_chk_new . className= v_class ;
       i_chk_new . id       ="Check_"+p_num ;
       i_chk_new . value    = p_id ;
       i_chk_new . checked  = p_checked ;
       i_chk_new . disabled = v_disabled ;
       i_chk_new . onclick  = function(e) {  DeseaseSet(this, p_id, p_name, p_group, p_gcode) ;  } ;
       i_col_new . appendChild(i_chk_new) ;
       i_row_new . appendChild(i_col_new) ;

       i_col_new = document.createElement("td") ;
       i_col_new . className = v_class ;

   if(p_gcode==0)
   {
       i_txt_new = document.createTextNode(p_group) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . onclick= function(e) { ShowGroup(this.parentNode.id) ; } ;
   }
   else
   {
       i_txt_new = document.createTextNode(p_name) ;
       i_col_new . appendChild(i_txt_new) ;
       i_col_new . onclick= function(e) {  window.open("desease_view.php?Id="+p_id) ;  } ;
   } 
       i_row_new . appendChild(i_col_new) ;

       i_deseases. appendChild(i_row_new) ;

    return ;         
  } 

  function ShowGroup(id_from) 
  {
    var  i_row ;
    var  i_checkbox ;

                    id_from++ ;

     for(i=gshow_start ; i<=gshow_end ; i++) 
     {
           i_row=document.getElementById(i) ;
        if(i_row       ==null )  break ;

           i_checkbox=document.getElementById("Check_"+i) ;
        if(i_checkbox.checked==false)  i_row.hidden=true ;
     }

   if(id_from==gshow_start)  {  gshow_start="" ;  return ;  }

        gshow_start=id_from ; 

     for(i=id_from ; ; i++) 
     {
           i_row=document.getElementById(i) ;
        if(i_row          ==  null  )  break ;
        if(i_row.className=="tableG")  break ;

           i_row.hidden=false ;
              gshow_end=i ; 
     }
  }

  function DeseaseSet(p_this, p_id, p_name, p_group, p_gcode) 
  {
    var  i_from ;
    var  i_chk ;
    var  id_before ;


         id_before=null ;

          i_from=p_this.id.replace("Check_", "") ;
          i_from++ ;

   if(p_this.checked) 
   {
     for(i=i_from ; ; i++) 
     {
           i_chk=document.getElementById("Check_"+i) ;
        if(i_chk          ==  null  )     break ;
        if(i_chk.checked  ==  true  ) {  id_before=i_chk.value ;  break ;  }
     }
   }

     parent.frames['section'].SetDeseaseSelection(p_this.checked, p_id, p_name, p_group, p_gcode, id_before) ;

   if(p_this.className=="tableG"   )
   {
     for(i=i_from ; ; i++) 
     {
           i_chk=document.getElementById("Check_"+i) ;
        if(i_chk          ==  null  )     break ;
        if(i_chk.className=="tableG")     break ;

        if(p_this.checked)
        { 
          if(i_chk.checked==true)
                parent.frames['section'].SetDeseaseSelection(false, i_chk.value, null, null, null, null) ;

             i_chk.checked =false ;
             i_chk.disabled=true ;
        }
        else
        {
             i_chk.disabled=false ;
        }
     }
   }

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

<div>
  <table width="100%">
    <thead>
    </thead>
    <tbody id="Deseases">
    </tbody>
  </table>

  <p class="error" id="Error"></p>

</div>

</body>

</html>
