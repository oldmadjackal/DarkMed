<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
                      "http://www.w3.org/TR/html4/frameset.dtd" >

<html>

<head>

<title>DarkMed</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

</head>

<noscript>
</noscript>

<frameset cols="60%,40%">

<?php

                          $session=$_GET["Session"] ;
  if(isset($_GET["Id"]))  $id     =$_GET["Id"] ;

  if(isset($id))  echo " <frame src='set_edit.php?Session=".$session."&Id=".$id."' name='section'>" ;	
  else            echo " <frame src='set_edit.php?Session=".$session."' name='section'>" ;	 
?>

   <frame name="details">
</frameset>

<noframes>
<p>Frames is not supported by this browser</p>
</noframes>

</html>         