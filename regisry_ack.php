<?php
header("Content-type: text/html; charset=windows-1251") ;

   $glb_script="Registry_ack.php" ;
   
  require("stdlib.php") ;

//============================================== 
  $key_confirm=$_GET["confirm_key"];
  $status=ReadConfig() ;
  if($status==false) {
          FileLog("ERROR", "ReadConfig()") ;
         ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ������ ������ ����������������� �����") ;
                         die("��� ���") ;
  }
$db=DbConnect($error) ;
  if($db===false) {
                    ErrorMsg($error) ; }
  else    {
    if ($key_confirm=="Repeat")
      {$text="�� ��� E-Mail ���������� ��������� ������ �� ������� �� ������������� �����������";}
    else  {
    $sql=" Select count(*) from `users`  Where `Code_confirm`='".$key_confirm."'";
    $res=$db->query($sql) ;
    $fields=$res->fetch_row();
//---- ������������� E-MAIL    
      if($fields[0]==1)
        {$sql=" Update `users` set Email_confirm='Y' Where `Code_confirm`='".$key_confirm."'";
         $res=$db->query($sql) ;   
          if($res===false) {
            FileLog("ERROR", "Update... : ".$db->error) ;
                              $db->rollback();
                              $db->close() ;
            ErrorMsg("������ �� �������. ��������� ������� �����.<br>������: ��� ") ;}
          else  {$db->commit();
                 $db->close(); 
                 $text="��� E-mail �����������, ��������� �� <a href=\"/\">������� ��������</a> � �������������.";}
         }
       else if ($fields[0]==0) $text="����� ������������� ����������� �������.��������� �� ������ � ����������������� �����";
//---- E-MAIL �����������
   
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