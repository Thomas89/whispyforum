﻿<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* newtopic.php
   új topic hozzáadása
*/

	include('includes/common.php');
	Inicialize('viewtopics.php');
	
	/* Fórum ID beállítása a bejövő adatok alapján */
	if ( $_GET['id'] != $NULL)
		$fId = $_GET['id'];
	
	if ( $_POST['id'] != $NULL)
		$fId = $_POST['id'];
	
	if ( $fId == $NULL )
		die(Hibauzenet("ERROR", "A megadott azonosítójú fórum nem létezik") );
	
	if ($_SESSION['loggedin'] != 1) { // Ha a felhasználó nincs bejelentkezve, nem szólhat hozzá
		Hibauzenet("ERROR", "Amíg nem jelentkezel be, nem készíthetsz új témát");
	} else {		
		$sor2 = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."forum WHERE id='" .$fId. "'"), MYSQL_ASSOC); // Fórum adatai
		
		if ( $_POST['submit'] != $NULL )
		{
			// Új téma hozzáadáas
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."forum SET topics='" .($sor2['topics']+1). "', posts='" .($sor2['posts']+1)."' WHERE id='" .$fId. "'"); // Fórum témaszám és postszám növelése
			$topicSzam = mysql_num_rows($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics"));
			$postSzam = mysql_num_rows($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts"));
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']. "topics(fId, name,	type, startdate, startuser, lastpostdate, lastuser, replies, opens, lpId, locked)
			VALUES('" .$fId. "', '" .$_POST['Ttitle']. "', '1', '" .time(). "', '" .$_SESSION['userid']. "', '" .time(). "', '" .$_SESSION['userid']. "', '1', '0', '" .($postSzam+1). "', '0')"); // Téma hozzáadása
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."posts(tId, uId, pTitle, pText, pDate) VALUES ( " .($topicSzam+1). ", '" .$_SESSION["userID"]. "', '" .$_POST["title"]. "', '" .$_POST['post']. "', '" .time(). "')"); // Post hozzáadása
			
			print("<div class='messagebox'>Az új témát létrehoztam!<br><a href='viewtopics.php?id=" .$_POST['id']. "'>Vissza a fórumhoz</a></div>"); // Visszatérési link
			die(); // A többi kód nem fut le!
			
		}
		
		if ( $sor2 == FALSE )
		{
			Hibauzenet("ERROR", "A megadott azonosítójú fórum nem létezik");
		} else {
			print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'><span class='formHeader'>Új téma beküldése: " .$sor2['name']. "</span>
			<p class='formText'>Téma neve: <input type='text' name='Ttitle' size='70' value='" .$_POST['Ttitle']. "'></p>
			<p class='formText'>Témakezdő hozzászólás címe: <input type='text' name='title' size='70' value='" .$_POST['title']. "'></p>
			<div class='postbox'><p class='formText'>Témakezdő kozzászólás:<br></a>
			<textarea rows='20' name='post' cols='70'>" .$_POST['post']. "</textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .THEME_NAME. "/images.php#emoticons' target='_blank'>Hangulatjelek</a><img src='/themes/" .THEME_NAME. "/x.bmp'><a href='/includes/help.php?cmd=BB' target='_blank'>BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div>
			</p>
			<input type='hidden' name='id' value='" .$fId. "'>
			<fieldset class='submit-buttons'>
				<input type='submit' name='submit' value='Téma létrehozása'>
				<input type='submit' name='preview' value='Előnézet'>
			</fieldset>
			</form><br>"); // Hozzászólás beküldési űrlap
			
			if ( $_POST['preview'] != $NULL )
			{
				/* Hózzászólás formázása */
				$postBody = $_POST['post']; // Nyers
				$postBody = EmoticonParse($postBody); // Hangulatjelek hozzáadása BB-kódként
				$postBody = HTMLDestroy($postBody); // HTML kódok nélkül 
				$postBody = BBDecode($postBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
				
				// Megtekintés
				print("<h3 class='header'><p class='header'>" .$_POST['Ttitle']. "</p></h2>");
				print("<h2 class='header'><p class='header'>A hozzászólásod így fog kinézni:</p></h2><div class='post'>"); // Fejléc
				print("<div class='postbody'><h3 class='postheader'><p class='header'>" .$_POST['title']. "</p></h3>"); // Hozzászólás fejléc
				print("<div class='content'>" .$postBody. "</div></div>"); // Hozzászólás
				print("<dl class='postprofile'><dt>" .$_SESSION['username']. "</dt><br><dd>Rang: " .$_SESSION['usrLevelTXT']. "</dd><dd>Hozzászólások: " .($_SESSION['postCount']+1). "</dd>"); // Hozzászólás adatai (hozzászóló, stb.)
				print("<dd>Csatlakozott: " .Datum("normal","m","d","H","i","", $_SESSION['regdate']). "</dd></dl>"); 
				print("</div>"); // Hozzászólás vége
			}
		}
	}
?>