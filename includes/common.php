﻿<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/common.php
   minden weboldalról meghívandó betöltőscript
*/

function Fejlec()
{
	// Fejléc létrehozása
	print("<div class='headerbox'>"); // Blokknyitás
	
	// KÓD IDE //
	print("</div>"); // Blokkzárás
	
	/* A footer elcsúszás kiiktatása végett egy láthatatlan táblázatba kerül a középső rész */
	print("<table class='centerdiv'><tr>");
}

function Lablec()
{
	global $cfg;
	
	/* A footer elcsúszást védelem vége */
	print("</td></tr></table>");
	
	// Lábléc létrehozása
	print("<div class='footerbox'>"); // Blokknyitás
	/* Generálás vége, generálási idő kiszámítása */
	global $start_time;
	
    $mtime = microtime();
	$mtime = explode(' ',$mtime);
    $current_time = $mtime[1] + $mtime[0];
	
	$genIdo = substr(($current_time - $start_time), 0, 5);
	
	print("WhispyFórum " .RELEASE_TYPE. " " .VERSION. " • Kiadás dátuma: " .RELEASE_DATE. " • <a href='mailto:" .$cfg['webmaster_email']. "'>" .$cfg['webmaster']. "</a> • Az oldal generálása " .$genIdo. " másodpercig tartott");
	print("</div>"); // Blokkzárás
}

function Inicialize ( $pagename )
{
 /* Generálás kezdete, idő eltárolása */
 global $start_time;
 $mtime = microtime();
 $mtime = explode(' ',$mtime);
 $start_time = $mtime[1] + $mtime[0];
 
 session_start(); // Elindítjuk a munkafolyamatot

 /* SZÜKSÉGES FÁJLOK BETÖLTÉSE */
 require('config.php'); // Konfigurációs állomány betöltése
 require('includes/versions.php'); // Verzióinformációk
 
 // Funkciótárak és osztályok betöltése
 require('includes/functions.php'); // Funkciótár
 require('includes/mysql.php'); // MySQL kezelési osztály ($sql)
 require('includes/user.php'); // Felhasználó és munkamenetfolyamat (session) kezelési osztály
 require('includes/sendmail.php'); // Levélküldési osztály
 require('includes/templates.php'); // Modulkezelő
 
 // Témafájl betöltése
 print("<link rel='stylesheet' type='text/css' href='themes/" .THEME_NAME. "/style.css'>
");
 /* */
 
 /* INICIALIZÁLÁS */
 $sql->Connect(); // Csatlakozás az adatbázisszerverhez
 $user->GetUserData(); // Felhasználó adatainak frissítése
 WriteLog("PAGE_VIEW", $pagename. ',' .$_SERVER['REMOTE_ADDR']. ',' .$_SERVER['HTTP_USER_AGENT']. ',' .$_SESSION['username']. ',' .$_SESSION['userLevelTXT']);
 
 /* Telepítettség ellenörzése */
 if ( !file_exists('install.lock') )
	Hibauzenet("CRITICAL", "A portálrendszer nincs telepítve", "Kérlek futtatsd a telepítőscriptet <a href='install.php'>innen</a>!", __FILE__, __LINE__);
 
 /* Verzióadatok elleörzése */
 $adat = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."version"), MYSQL_ASSOC);
 if ( ($adat["RELEASE_TYPE"] != RELEASE_TYPE) || ($adat["VERSION"] != VERSION) || ($adat["RELEASE_DATE"] != RELEASE_DATE) )
	Hibauzenet("CRITICAL", "A futó verzió nem egyezik a telepített verzióval", "Futó verzió: <b>" .RELEASE_TYPE. " " .VERSION. " (" .RELEASE_DATE. ")</b><br>Telepített verzió: <b>" .$adat['RELEASE_TYPE']. " " .$adat['VERSION']. " (" .$adat['RELEASE_DATE']. ")</b><br>Bővebb információ: 
	<a href='' onClick=\"window.open('includes/help.php?cmd=Update', 'popupwindow', 'width=570,height=320'); return false;\">kattints ide</a>");
	

 if ($pagename != "admin.php") // Az admin.php-n ezeknek NEM kell megjelenniük
 {
	Fejlec(); // Fejléc
	
	print("<td class='left' valign='top'>"); // Bal odali doboz
	$user->CheckIfLoggedIn($_SESSION['username']); // Megnézzük, hogy belépett-e már a user
	
	$templates->DoLeft(); // Bal oldali modulok
	
	print("</td>
    <td class='center' valign='top'>"); // Középső doboz
	//$templates->DoCenter($pagename); // Középső modulok
 }
}

function DoFooter() // Középső rész elküldése után
{
 print("</td><td class='right' valign='top'>"); // Jobb oldali doboz
 
 global $templates;
 $templates->DoRight(); // Jobb oldali modulok
 
 Lablec(); // Lábléc
}

function SetTitle( $fejlec ) // HTML fejléc létrehozása
{
global $cfg; // Konfigurációs tömb

 if ($fejlec == '')
 {
	// Ha nincs fejléc paraméter megadva a hívókódban
	print("<title>" .$cfg['pname']. "</title>"); // Csak a weblap neve a fejléc
 } else {
	// Ellenkező esetben, ha van
	print("<title>" .$cfg['pname']. " - " .$fejlec. "</title>"); // Weblap neve - weblap címe
 }
}
?>