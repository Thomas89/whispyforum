﻿<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* editpost.php
   fórum hozzászólás szerkesztése
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('editpost.php');
 SetTitle("Hozzászólás szerkesztése");
 
 /* Inicializációs rész */
 $jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a hozzászólást
 // Adatok bekéréss
 if ( $_POST['pId'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$getid = $_POST['pId'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['pId'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$getid = $_GET['pId'];
	} else {
		// Sehogy nem érkezett adat
		$getid = $NULL;
	}
 }
 // Felhasználói rang, felhasználó ellenörzése
 $adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE id='" .$getid. "'")); // Post adatainak bekérése
 if ( $_SESSION['userId'] != $adat['uid'])
	$jog = 0; // Ha a szerkeszteni kívánt személy nem az eredeti post szerzője, nincs joga szerkeszteni
	
 if ( ($_SESSION['userLevel'] == 0) || ($_SESSION['userLevel'] == 1) )
	$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
 
 // Téma zároltság ellenörzése
 $sor2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$adat['tId']. "'")); // Téma sora
 if ( $sor2['locked'] == 1 )
	$jog = 0; // Ha a téma, amelyben a hozzászólás van, zárolt, a hozzászólás nem szerkeszthető.
 
 if ( $jog == 0 )
 {
	Hibauzenet("ERROR", "Nincs jogod a hozzászólás szerkesztéséhez, vagy a téma le van zárva");
 } else {
	if ( $_POST['submit'] == "Hozzászólás szerkesztése")
	{
		// Szerkesztés
		print("<div class='messagebox'>Hozzászólás sikeresen szerkesztve!<br><a href='viewtopic.php?id=" .$sor2['id']. "#pid" .$getid. "'>Vissza a hozzászóláshoz</a>");
		
		die();
	}
	// Hozzászólás, és fórum kiírása
	print("<h1><center><p class='header'>Hozzászólás szerkesztése</p></center></h1>");
	$postBody = $adat['pText']; // Nyers
	$postBody = EmoticonParse($postBody); // Hangulatjelek hozzáadása BB-kódként
	$postBody = HTMLDestroy($postBody); // HTML kódok nélkül 
	$postBody = BBDecode($postBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
	
	print("<div class='post'>"); // Fejléc
	print("<div class='postbody'><h3 class='postheader'><p class='header'><a name='pid" .$adat['id']. "'></a>" .$adat['pTitle']. "");
	print("</p></h3>"); // Hozzászólás fejléc
	print("<div class='content'>" .$postBody. "</div></div>"); // Hozzászólás
	print("<div class='postright'>Hozzászólás időpontja: <b>" .Datum("normal","kisbetu","dL","H","i","s",$adat['pDate']). "</b><p><b>" .$adat2['username']. "</b><br>Rang: " .$usrRang. "<br>Hozzászólások: " .$adat2['postCount']. "<br>"); // Hozzászólás adatai (hozzászóló, stb.)
	print("Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). ""); // Hozzászóló adatai
	print("</div></div>"); // Hozzászólás vége
	
	print("<br style='clear: both'>
		<a href='viewtopic.php?id=" .$sor2['id']. "'><< Vissza a témához</a><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Hozzászólás szerkesztése: " .$adat['pTitle']. "</span>
			<p class='formText'>Cím: <input type='text' name='title' size='70' value='" .$adat['pTitle']. "'></p>
			<div class='postbox'><p class='formText'>Hozzászólás:<br>
			<textarea rows='20' name='post' cols='70'>" .$adat['pText']. "</textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .THEME_NAME. "/emoticons.php' onClick=\"window.open('/themes/" .THEME_NAME. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div>
			<input type='hidden' name='pId' value='" .$adat['id']. "'>
			<fieldset class='submit-buttons'>
				<input type='submit' name='submit' value='Hozzászólás szerkesztése'>
			</fieldset>
			</form><br>");
 }
 
 DoFooter();
?>