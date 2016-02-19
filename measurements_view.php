<?php

header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Measurements_view.php" ;

  require("stdlib.php") ;

//============================================== 
//  Проверка и запись регистрационных в БД

function ProcessDB() {

  global  $sys_cols_count  ;
  global  $sys_cols        ;
  global  $sys_vals_count  ;
  global  $sys_vals        ;
  global  $sys_image_path  ;

//--------------------------- Считывание конфигурации

     $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         return ;
  }
//--------------------------- Извлечение параметров

                         $session=$_GET["Session"] ;
                           $owner=$_GET["Owner"] ;
                            $page=$_GET["Page"] ;

    FileLog("START", "Session:".$session) ;
    FileLog("",      "  Owner:".$owner) ;
    FileLog("",      "   Page:".$page) ;

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
//--------------------------- Приведение параметров

          $owner_=$db->real_escape_string($owner) ;
          $user_ =$db->real_escape_string($user ) ;
          $page_ =$db->real_escape_string($page ) ;

//--------------------------- Извлечение ключа страницы

                       $sql="Select crypto".
                            "  From access_list".
                            " Where owner='$owner_' ".
                            "  and  login='$user_' ".
                            "  and  page = $page_" ;
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

      echo     "   page_key  ='" .$fields[0]."' ;	\n" ;

//--------------------------- Извлечение списка назначений - контрольных измерений

                     $sql="Select distinct measurement_id, prescription_id, name, remark".
			  "  From prescriptions_pages p inner join measurements m on p.reference=m.measurement_id".
                          " Where owner='$owner_'".
                          "  and  page = $page_".
                          " Order by measurement_id" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select PRESCRIPTIONS_PAGES... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса списка назначений") ;
                         return ;
  }
  else
  {  
               $sys_cols_count=$res->num_rows ;
       echo "       cols_count='".$res->num_rows."' ;	\n" ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;
                
               $sys_cols[$i]=$fields[0] ;

       echo "   a_cols_name  [".$i."]='".$fields[2]."' ;	\n" ;
       echo "   a_cols_remark[".$i."]='".$fields[3]."' ;	\n" ;
     }
  }

     $res->close() ;

//--------------------------- Извлечение массива значений

                     $sql="Select measurement_id, value, checked, unix_timestamp(checked)".
			  "  From measurements".
                          " Where page_id in (Select id From client_pages Where owner='$owner_' and page= $page_)".
                          " Order by checked" ;
     $res=$db->query($sql) ;
  if($res===false) {
          FileLog("ERROR", "Select MEASUREMENTS... : ".$db->error) ;
                            $db->close() ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка запроса реестра значений контрольных измерений") ;
                         return ;
  }
  else
  {  
        $time_mark=0 ;
              $row=0 ;

     for($i=0 ; $i<$res->num_rows ; $i++)
     {
	      $fields=$res->fetch_row() ;

       if($i==  0             )  $a_grid["time"]["min"]=$fields[3] ;
       if($i==$res->num_rows-1)  $a_grid["time"]["max"]=$fields[3] ;

       if($fields[3]-$time_mark>7200) 
       {
		$time_mark=$fields[3] ;

                      $row++ ;          
            $sys_vals[$row]["date"]=$fields[2] ;
            $sys_vals[$row]["time"]=$fields[3] ;
       }

         $sys_vals[$row][$fields[0]]=$fields[1] ;

                                   $values=explode(":", $fields[1]) ;
             if(count($values)<2)  $values=explode(",", $fields[1]) ;
             if(count($values)<2)  $values=explode("=", $fields[1]) ;
             if(count($values)<2)  $values=explode("/", $fields[1]) ;
             if(count($values)<2)  $values=explode("-", $fields[1]) ;

           foreach($values as $value) {
             if(!isset($a_grid[$fields[0]]["min"])      ||
                       $a_grid[$fields[0]]["min"]>$value  )  $a_grid[$fields[0]]["min"]=$value ;
             if(!isset($a_grid[$fields[0]]["max"])      ||
                       $a_grid[$fields[0]]["max"]<$value  )  $a_grid[$fields[0]]["max"]=$value ;
                                      }

             if(count($values)>1)  $a_grid[$fields[0]]["points"]=count($values) ;
     }

           $sys_vals_count=$row+1 ;
  }

     $res->close() ;

//--------------------------- Отрисовка графика

//- - - - - - - - - - - - - - Подготовка виртуального экрана
        $X_size= 700 ;
        $Y_size= 200 ;
        $space =   2 ;
	 $image=imagecreate($X_size+2*$space, $Y_size+2*$space) ;

      $bk_color=imagecolorallocate($image, 255, 255, 255) ; 
//                imagefill         ($image, 0, 0, $bk_color) ; 
//- - - - - - - - - - - - - - Рассчет параметров отображения
               $X_min=$a_grid["time"]["min"] ;
               $X_max=$a_grid["time"]["max"] ;
       settype($X_min, "double") ;
       settype($X_max, "double") ;

     if($X_min==$X_max) { $X_min-=1. ;  $X_max+=1. ;  }  
//- - - - - - - - - - - - - - Отрисовка графиков
  for($j=0 ; $j<$sys_cols_count ; $j++)
  {
      if($j==0)  $color=imagecolorallocate($image, 250,   0,   0) ; 
      if($j==1)  $color=imagecolorallocate($image,   0, 250,   0) ; 
      if($j==2)  $color=imagecolorallocate($image,   0,   0, 250) ; 

               $X_prv=-1 ;
               $Y_prv=-1 ;

               $Y_min=$a_grid[$sys_cols[$j]]["min"] ;
               $Y_max=$a_grid[$sys_cols[$j]]["max"] ;
       settype($Y_min, "double") ;
       settype($Y_max, "double") ;

      if($Y_min==$Y_max) { $Y_min-=1. ;  $Y_max+=1. ;  }  

    for($i=1 ; $i<$sys_vals_count ; $i++)   
      if(isset($sys_vals[$i][$sys_cols[$j]]))
      {
                     $X=$sys_vals[$i]["time"] ;
             settype($X, "double") ;
//- - - - - - - - - - - - - - Для диапазонных значений
        if(isset($a_grid[$sys_cols[$j]]["points"]))
        {
                                   $values   =explode(":", $sys_vals[$i][$sys_cols[$j]]) ;
             if(count($values)<2)  $values   =explode(",", $sys_vals[$i][$sys_cols[$j]]) ;
             if(count($values)<2)  $values   =explode("=", $sys_vals[$i][$sys_cols[$j]]) ;
             if(count($values)<2)  $values   =explode("/", $sys_vals[$i][$sys_cols[$j]]) ;
             if(count($values)<2)  $values   =explode("-", $sys_vals[$i][$sys_cols[$j]]) ;
             if(count($values)<2)  $values[1]=$values[0] ;

                     $Y1=$values[0] ;
             settype($Y1, "double") ; 
                     $Y2=$values[1] ;
             settype($Y2, "double") ; 

                     $X =        ($X -$X_min)*$X_size/($X_max-$X_min) ;
                     $Y1=$Y_size-($Y1-$Y_min)*$Y_size/($Y_max-$Y_min) ;
                     $Y2=$Y_size-($Y2-$Y_min)*$Y_size/($Y_max-$Y_min) ;

              imagesetthickness($image, 1) ;
              imageline        ($image, $X+$space,   $Y1+$space,   $X+$space,   $Y2+$space,   $color) ;
              imagerectangle   ($image, $X+$space-1, $Y1+$space-1, $X+$space+1, $Y1+$space+1, $color) ;
              imagerectangle   ($image, $X+$space-1, $Y2+$space-1, $X+$space+1, $Y2+$space+1, $color) ;
        }
//- - - - - - - - - - - - - - Для дискретных значений
        else
        {
                     $Y=$sys_vals[$i][$sys_cols[$j]] ;
             settype($Y, "double") ;

                     $X=        ($X-$X_min)*$X_size/($X_max-$X_min) ;
                     $Y=$Y_size-($Y-$Y_min)*$Y_size/($Y_max-$Y_min) ;

                            imagesetthickness($image, 1) ;
             if($X_prv>=0)  imageline        ($image, $X_prv+$space, $Y_prv+$space, $X+$space, $Y+$space, $color) ;
                            imagerectangle   ($image, $X+$space-1, $Y+$space-1, $X+$space+1, $Y+$space+1, $color) ;

                     $X_prv=$X ;
                     $Y_prv=$Y ;
        }
//- - - - - - - - - - - - - - Отрисовка графиков
      }
  }
//- - - - - - - - - - - - - - Сохранение в файл
        $tmp_folder=PrepareTmpFolder($session) ;
     if($tmp_folder=="") {
             FileLog("ERROR", "Temporary folder create error") ;
                     $db->rollback();
                     $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка создания временной папки") ;
                         return ;
     }

       $sys_image_path=$tmp_folder."/picture".time().".gif" ;

              imagegif    ($image, $sys_image_path) ; 
	      imagedestroy($image) ;
//- - - - - - - - - - - - - - Перевод абсолютного пути в относительный
           $cur_folder=getcwd() ;
       $sys_image_path=substr($sys_image_path, strlen($cur_folder)+1) ;

//--------------------------- Завершение

     $db->close() ;

        FileLog("STOP", "Done") ;
}

//============================================== 
//  Отображение значений измерений

function ShowGraph() {

  global  $sys_image_path  ;

       echo "<div class='fieldC'>		\n" ;
       echo "<img src='".$sys_image_path."'>	\n" ;
       echo "</div>				\n" ;
       echo "<br>				\n" ;

}

//============================================== 
//  Отображение значений измерений

function ShowMeasurements() {

  global  $sys_cols_count  ;
  global  $sys_cols        ;
  global  $sys_vals_count  ;
  global  $sys_vals        ;


       echo  "  <tr class='table'>					\n" ;
       echo  "    <td class='table'>					\n" ;
       echo  "      <div class='fieldC'>Дата</div>			\n" ;
       echo  "    </td>							\n" ;

    for($j=0 ; $j<$sys_cols_count ; $j++)
    {
            if($j==0)  $color="red" ;
       else if($j==1)  $color="green" ;
       else            $color="blue" ;

       echo  "    <td class='table'>						\n" ;
       echo  "      <font color='".$color."'>					\n" ;
       echo  "      <div class='fieldC' id='Name_".$j."'></div>			\n" ;
       echo  "      </font>							\n" ;
       echo  "      <i><div class='fieldC'  id='Remark_".$j."'></div></i>	\n" ;
       echo  "    </td>								\n" ;
    }
       echo  "  </tr>							\n" ;

  for($i=1 ; $i<$sys_vals_count ; $i++)
  {
        $row=$i ;

       echo  "  <tr class='table'>					\n" ;
       echo  "    <td class='table'>					\n" ;
       echo  "      <div class='fieldC'>".$sys_vals[$i]["date"]."</div>	\n" ;
       echo  "    </td>							\n" ;

    for($j=0 ; $j<$sys_cols_count ; $j++)
    {
       echo  "    <td class='table'>						\n" ;
       echo  "      <div class='fieldC'>".$sys_vals[$i][$sys_cols[$j]]."</div>	\n" ;
       echo  "    </td>								\n" ;
    }

       echo  "  </tr>								\n" ;

  }

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

<title>DarkMed Measurements View</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/tripledes.js"></script>
<script type="text/javascript">
<!--

    var  i_set ;
    var  i_error ;

    var  cols_count ;
    var  a_cols_name ;
    var  a_cols_remark ;


  function FirstField() 
  {
    var  i_ceil  ;
    var  password ;
    var  page_key ;
    var  prescr_id ;
    var  prescr_name ;
    var  prescr_remark ;

       i_set  =document.getElementById("Prescriptions") ;
       i_error=document.getElementById("Error") ;

	a_cols_name  =new Array() ;
	a_cols_remark=new Array() ;

<?php
            ProcessDB() ;
?>

       password=TransitContext("restore", "password", "") ;

       page_key= Crypto_decode( page_key, password) ;

       for(i=0 ; i<cols_count ; i++)
       {
            i_ceil          =document.getElementById("Name_"+i) ;
            i_ceil.innerHTML=Crypto_decode(a_cols_name  [i], page_key).replace("#MEASUREMENT#","") ;
            i_ceil          =document.getElementById("Remark_"+i) ;
            i_ceil.innerHTML=Crypto_decode(a_cols_remark[i], page_key) ;
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

  function ShowDetails(p_id)
  {
    window.open("prescription_view.php?Id="+p_id) ;
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
        <b>ИСТОРИЯ КОНТРОЛЬНЫХ ИЗМЕРЕНИЙ</b>
      </td> 
    </tr>
    </tbody>
  </table>

  <div class="error" id="Error"></div>
  <form onsubmit="return SendFields();" method="POST" id="Form">

  <br>

<?php
            ShowGraph() ;
?>

  <table width="100%">
    <thead>
    </thead>
    <tbody  id="Measurements">

<?php
            ShowMeasurements() ;
?>

    </tbody>
  </table>

  </form>

</div>

</body>

</html>
