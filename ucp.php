﻿<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* ucp.php
   felhasználói vezérlőpult
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('ucp.php');
 SetTitle("Felhasználói vezérlőpult");
 
 print("<center><h2 class='header'>Felhasználói vezérlőpult</h2></center>");
 $felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$_SESSION['userID']. "'"));
 print("<div class='menubox'><a href='ucp.php'>Kezdőlap</a> • <a href='ucp.php?set=theme' class='menuItem'>Téma módosítása</a> • <a href='ucp.php?set=avatar' class='menuItem'>Megjelenítendő kép feltöltése</a> • <a href='ucp.php?set=otherdata' class='menuItem'>Egyéb adatok szerkesztése</a></div><br>");
 
 if ( $_POST['set'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$gpset = $_POST['set'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['set'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$gpset = $_GET['set'];
	} else {
		// Sehogy nem érkezett adat
		$gpset = $NULL;
	}
 }
 //print(str_replace("\n", "\n<br>", var_export($felhasznalo, TRUE)));
 global $tdszam;
 $tdszam = 0;
 
 function UjTd()
 {
	global $tdszam;
	if ( $tdszam == 2)
	{
		print("</tr><tr>");
		$tdszam = 0;
	}
 }
 
 switch ( $gpset )
 {
	case "theme":
		
		if ( ($_GET['seta'] == "settheme") && ($_GET['themename'] != $NULL) )
		{
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET theme='" .mysql_real_escape_string($_GET['themename']). "' WHERE id='" .$_SESSION['userID']. "'");
			print("<div class='messagebox'>A témát sikeresen módosítottad!</div>");
			DoFooter();
			die();
		}
		
		if ( $felhasznalo['theme'] == $NULL ) // Ha a felhasználónál nincs beálított téma, a defaultot használja
		{
			$usrtema = "default";
		} else {
			// Ha van, kiírjuk a nevét
			$usrtema = $felhasznalo['theme'];
		}
		
		print("Aktuális használt téma: <a href='themes/" .$usrtema. "/index.php'>" .$usrtema. "</a><br>");
		
		/* Elérhető témák listázása egy eddig ki nem próbált algoritmussal */
		$dir = "./themes/"; // Téma gyökérmappa
		$exempt = array('.', '..', '.svn', '_svn'); // Megadunk egy le nem kérdezendő kivétellistát
		print("<table border='1'><tr>");
		if (is_dir($dir)) 
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if(!in_array(strtolower($file),$exempt))
					{
						if ( filetype($dir . $file) == "dir" )
						{
							/* Bekértük az összes MAPPÁT a themes mappából */
							if ( file_exists($dir . $file . "/style.css") )
							{
								// Csak olyan mappákkal foglalkozunk, ahol van STYLE.CSS (tehát tud témát leírni)
								print("<td style='widht: 50%'>");
								
								if ( file_exists($dir . $file . "/preview.jpg") )
								{
									// Ha van előnézeti kép, megjelenítjük azt is
									print("<a href='" .$dir . $file . "/preview.jpg' alt='" .$file. " téma előnézete'><img src='" .$dir . $file . "/preview.jpg' width='320' height='240' alt='" .$file. " téma előnézete' border='0'></a>");
								} else {
									// Ha nincs, a "nincs előnézeti kép" képet jelenítjük meg
									print("<img src='themes/nopreview.jpg'>");
								}
								
								print("<br style='clear: both'>" .$file ."&nbsp;<a href='ucp.php?set=theme&seta=settheme&themename=" .$file. "'>Téma beállítása</a></td>");
								$tdszam++;
								UjTd();
							}
							echo "<br>";
						}
					}
				}
				closedir($dh);
			}
		}
		print("</tr></table>");
		//print("<a href='ucp.php?set=theme&seta=settheme'></a>");
		break;
	case "otherdata": // Egyéb adatok szerkesztése
		if ( $_POST['seta'] == "setdatas" )
		{
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET bemutatkozas='" .mysql_real_escape_string($_POST['bemutatkozas']). "', thely='" .mysql_real_escape_string($_POST['thely']). "' WHERE id='" .$_SESSION['userID']. "'");
			print("<div class='messagebox'>Adatok frissítve!</div>");
			DoFooter();
			die();
		}
		
		print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<p class='formText'>Bemutatkozás: <textarea name='bemutatkozas' rows='8' cols='25'>" .$felhasznalo['bemutatkozas'] ."</textarea><br>
			Tartózkodási hely: <input type='text' name='thely' value='" .$felhasznalo['thely']. "'><br>
			<input type='hidden' name='set' value='otherdata'>
			<input type='hidden' name='seta' value='setdatas'>
			<input type='submit' value='Adatok szerkesztése'></form>");
			
		break;
	case "avatar": // Megjelenítendő kép szerkesztése
		if ( $_POST['feltolt'] == "yes" ) // Ha elküldtük a feltöltése parancsot
		{
			if ( $_FILES['picfile']['size'] > 2097152 )
			{
				Hibauzenet("ERROR", "A feltöltött fájl túl nagy méretű!", "A feltöltött fájl mérete " .DecodeSize($_FILES['picfile']['size']). ", azonban a maximális megengedett méret csak " .DecodeSize(2097152). "! Kérlek töltsd fel egy kisebb méretű fájlt!");
			} else {
				if ( in_array($_FILES['picfile']['type'], array("image/gif", "image/jpeg", "image/png")) )
				{
					if(move_uploaded_file($_FILES['picfile']['tmp_name'], "uploads/" .$_SESSION['username']))
					{
						// Sikeres feltöltés esetén
						print("<div class='messagebox'>Az avatarod frissítése sikeresen megtörtént!</div>");
					} else {
						// Hiba volt a feltöltés közben
						Hibauzenet("ERROR", "A fájlt nem sikerült feltölteni!");
					}
				} else {
					Hibauzenet("WARNING", "A feltöltött fájlnak JPG, GIF vagy PNG típusúnak kell lennie!", "A te általad feltöltött fájl (" .$_FILES['picfile']['name']. ") nem érvényes képfájl!");
				}
			}
		}
		
		print("<div class='userbox'>Aktuális megjelenítendő képed:&nbsp;");
		if ( file_exists("uploads/" .$_SESSION['username']) )
		{
			print("<img src='uploads/" .$_SESSION['username']. "' width='128' height='128' alt='" .$_SESSION['username']. " megjelenítendő képe'>");
		} else {
			print("<img src='themes/" .$_SESSION['themeName']. "/anon.png' width='128' height='128' alt='" .$_SESSION['username']. " megjelenítendő képe'>");
		}
		
		print("<br>
		<form enctype='multipart/form-data' action='" .$_SERVER['PHP_SELF']. "' method='POST'>
		<p class='formText'>Az aktuális avatarod megváltoztatásához tallóz be egy JPG, PNG vagy GIF fájlt a merevlemezedről. A kép ajánlott mérete <b>128x128 pixel</b>, fájlmérete maximálisan: " .DecodeSize(2097152). "
		<br><input name='picfile' type='file' size='50' accept='application/octet-stream'><br>
	<input type='submit' value='Feltöltés'>
	<input type='hidden' name='set' value='avatar'>
	<input type='hidden' name='feltolt' value='yes'></p>
	</form>");
		
		break;
	default:
		
		switch ($felhasznalo['userLevel']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
		{
			case -1:
				$usrRang = 'Kititva';
				break;
			case 0:
				$usrRang = 'Nincs aktiválva';
				break;
			case 1:
				$usrRang = 'Felhasználó';
				break;
			case 2:
				$usrRang = 'Moderátor';
				break;
			case 3:
				$usrRang = 'Adminisztrátor';
				break;
		}
 
		print("<div class='menubox'><span class='menutitle'>Információk</span><br>
		<p class='formText'>
			<b>Regisztráció időpontja:</b> " .Datum("normal","kisbetu","dL","H","i","s", $felhasznalo['regdate']). "<br>
			<b>Aktiválás időpontja:</b> " .Datum("normal","kisbetu","dL","H","i","s", $felhasznalo['activatedate']). "<br>
			<b>Rang:</b> " .$usrRang. "</p></div>");
 }
 DoFooter();
?>