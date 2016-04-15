<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
                      "http://www.w3.org/TR/html4/frameset.dtd" >

<html>

<head>

<title>DarkMed</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">

</head>

<noscript>
</noscript>

<frameset rows="*,20%">

<?php

       $session=$_GET["Session"] ;
       $sender =$_GET["Sender"] ;

   echo " <frame src='messages_chat_lr.php?Session=".$session."&Sender=".$sender."' name='section'>" ;	
?>

   <frame name='details'>
</frameset>

<noframes>
<p>Frames is not supported by this browser</p>
</noframes>

</html>         