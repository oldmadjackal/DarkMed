<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
                      "http://www.w3.org/TR/html4/frameset.dtd" >

<html>

<head>

<title>DarkMed</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

</head>

<noscript>
</noscript>

<frameset cols="70%,30%">

<?php

       $session=$_GET ["Session"] ;

   echo " <frame src='deseases_registry.php?Session=".$session."' name='section'>" ;	
?>

   <frame name='details'>
</frameset>

<noframes>
<p>Frames is not supported by this browser</p>
</noframes>

</html>         