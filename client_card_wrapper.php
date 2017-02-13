<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
                      "http://www.w3.org/TR/html4/frameset.dtd" >

<html>

<head>

<title>DarkMed</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

</head>

<noscript>
</noscript>

<frameset cols="*,370">

<?php

                               $session =$_GET["Session"] ;
    if(isset($_GET["Owner"]))  $owner   =$_GET["Owner"] ;

    if(isset($owner))  echo " <frame src='client_card.php?Session=".$session."&Owner=".$owner."' name='section'>" ;
    else               echo " <frame src='client_card.php?Session=".$session."' name='section'>" ;
?>

   <frame name="anatomy">
</frameset>

<noframes>
<p>Frames is not supported by this browser</p>
</noframes>

</html>         