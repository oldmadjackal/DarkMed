<?php
header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Registry_ack.php" ;
   
  require("stdlib.php") ;

//============================================== 
  $key_confirm=$_GET["confirm_key"];
  $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: ошибка чтение конфигурационного файла") ;
                         die("фиг вам") ;
  }
$db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ; }
  else    {
    if ($key_confirm=="Repeat")
      {$text="На Ваш E-Mail отправлено повторное письмо со ссылкой на подтверждение регистрации";}
    else  {
    $sql=" Select count(*) from `users`  Where `Code_confirm`='".$key_confirm."'";
    $res=$db->query($sql) ;
    $fields=$res->fetch_row();
//---- ПОДТВЕРЖДЕНИЕ E-MAIL    
      if($fields[0]==1)
        {$sql=" Update `users` set Email_confirm='Y' Where `Code_confirm`='".$key_confirm."'";
         $res=$db->query($sql) ;   
          if($res===false) {
            FileLog("ERROR", "Update... : ".$db->error) ;
                              $db->rollback();
                              $db->close() ;
            ErrorMsg("Ошибка на сервере. Повторите попытку позже.<br>Детали: нет ") ;}
          else  {$db->commit();
                 $db->close(); 
                 $text="Ваш E-mail подтвержден, перейдите на <a href=\"/\">главную страницу</a> и авторизуйтесь.";}
         }
       else if ($fields[0]==0) $text="Время подтверждения регистрации истекло.Перейдите на портал и зарегистрируйтесь снова";
//---- E-MAIL ПОДТВЕРЖДЕН
   
         } }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
                      "http://www.w3.org/TR/html4/frameset.dtd" >

<html>

<head>

<title>DarkMed</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

<style type="text/css">
  @import url("common.css")
</style>

</head>
<body>
<br><br><br><br><br><br><br><br>
<p align="center">
<table width="90%">
    <tbody>
    <tr>
      <td class="title"> 
        <b><img src="/images/NewStep.png"><?php echo $text; ?></b>
      </td> 
    </tr>
    </tbody>
  </table>
</p>
</body>
</html>