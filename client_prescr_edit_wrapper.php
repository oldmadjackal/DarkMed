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

                                  $session =$_GET["Session"] ;
                                  $owner   =$_GET["Owner"] ;
     if(isset($_GET["Page"   ]))  $page    =$_GET["Page"] ;
     if(isset($_GET["NewPage"]))  $new_page=$_GET["NewPage"] ;

     if(isset($page))  echo " <frame src='client_prescr_edit.php?Session=".$session."&Owner=".$owner."&Page="   .$page    ."' name='section'>" ;
     else              echo " <frame src='client_prescr_edit.php?Session=".$session."&Owner=".$owner."&NewPage=".$new_page."' name='section'>" ;
?>

   <frame name="details">
</frameset>

<noframes>
<p>Frames is not supported by this browser</p>
</noframes>

</html>         