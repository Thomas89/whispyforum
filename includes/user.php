<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/user.php
   felhasználó és munkamenetfolyamat (session) kezelési osztály
*/

class user // Definiáljuk az osztályt (felhasználók)
{
	function DoLoginForm() // Bejelentkezési űrlap létrehozása
	{
		global $wf_debug;
		if ( $_POST['id'] != $NULL )
		{
			// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
			$getid = $_POST['id'];
		} else {
			// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
			if ( $_GET['id'] != $NULL )
			{
				// Ha gettel érkezik, az lesz az érték
				$getid = $_GET['id'];
			} else {
				// Sehogy nem érkezett adat
				$getid = $NULL;
			}
		}
		
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
	<span class='formHeader'>Bejelentkezés</span>
 <p class='formText'>Felhasználói név: <input type='text' name='username'></p>
 <p class='formText'>Jelszó: <input type='password' name='pwd'></p>
 <input type='submit' value='Bejelentkezés'>
 <input type='hidden' name='cmd' value='loginusr'>
 <input type='hidden' name='id' value='" .$getid. "'><br>
 <a href='registration.php'>Regisztráció</a></form><br>");
		
		$wf_debug->RegisterDLEvent("Bejelentkezési űrlap létrehozva");
	}
	
	function Login ( $un, $pw ) // Bejelentkeztetés
	{
		global $cfg, $sql, $session, $wf_debug;
		$sql->Connect();
		
		$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE username='" .mysql_real_escape_string($un). "'"));
		
		if ( (md5($pw) == $adat['pwd']) && ($adat['activated'] == 1 ) )
		{
			if ( $adat['userLevel'] != -1 )
			{
				$wf_debug->RegisterDLEvent("Felhasználó bejelentkezése...");
				$session->StartSession($un, $pw); // Munkamenet indítása
				$this->GetUserData();
			} else {
				Hibauzenet("ERROR", "Nem sikerült a bejelentkezés", "A felhasználód ki van tiltva az oldalról, így nem léphetsz be!");
			}
		} else {
			Hibauzenet("WARNING", "Nem sikerült a bejelentkezés", "A felhasználónév nem megfelelő, vagy még nem aktiváltad a felhasználód.<br><a href='useractivate.php?username=" .$un. "'>Aktiválási űrlap megnyitása</a>");
			$wf_debug->RegisterDLEvent("A bejelentkezés nem sikerült");
		}
	}
	
	function Logout() // Kijelentkezés
	{
		global $cfg, $sql, $session, $wf_debug;
		$sql->Connect();
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "user SET loggedin='0', cursessid='', curip='0.0.0.0' WHERE username='" .mysql_real_escape_string($_SESSION['username']). "' AND pwd='" .md5(mysql_real_escape_string($_SESSION['pass'])). "'");
		$wf_debug->RegisterDLEvent("Felhasználó kijelentkezése");
		
		
		// Session kiűrítése
		$session->Purge();
	}
	
	function DoControlForm() // Felhasználói panel
	{
		global $wf_debug;
		
		print("<div class='userbox'><span class='formHeader'>Üdvözlünk, ");
		
		$this->GetUserData();
		
		// Köszöntjük a felhasználót a valódi nevén (ha megadata)
		if ($_SESSION['realName'] == $NULL)
		{
			print ($_SESSION['username']);
		} else { // Ha nem, a usernevén
			print ($_SESSION['realName'] ." (". $_SESSION['username'] .")");
		}
		
		print("!</span><br>&nbsp;");
		if ( file_exists("uploads/" .md5($_SESSION['username']). ".pict") )
		{
			print("<img src='uploads/" .md5($_SESSION['username']). ".pict' width='128' height='128' alt='" .$_SESSION['username']. " megjelenítendő képe'>");
		} else {
			print("<img src='themes/" .$_SESSION['themeName']. "/anon.png' width='128' height='128' alt='" .$_SESSION['username']. " megjelenítendő képe'>");
		}
		
		print("<p class='formText'>Felhasználói szinted: " .$_SESSION['userLevelTXT']. "<br><a href='profile.php?id=" .$_SESSION['userID']. "'>Profilod megtekintése</a><br><a href='ucp.php'>Felhasználói vezérlőpult</a>");
		if ( $_SESSION['userLevel'] == 3) // Ha a felhasználó admin linket írunk az admin vezérlőpultra
			print("<br><a href='admin.php'>Adminisztrátori vezérlőpult</a>");
		
		if ( $_POST['id'] != $NULL )
		{
			// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
			$getid = $_POST['id'];
		} else {
			// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
			if ( $_GET['id'] != $NULL )
			{
				// Ha gettel érkezik, az lesz az érték
				$getid = $_GET['id'];
			} else {
				// Sehogy nem érkezett adat
				$getid = $NULL;
			}
		}

		print("</p><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
		<input type='hidden' name='cmd' value='logoutusr'>
		<input type='hidden' name='id' value='" .$getid. "'>
		<input type='submit' value='Kijelentkezés'></form>");
		print("</div>");
		$wf_debug->RegisterDLEvent("Felhasználói panel létrehozva");
	}
	
	function GetUserData() // Felhasználó adatok cachelése sessionbe
	{
		global $cfg, $sql, $wf_debug;
		$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE username='" .mysql_real_escape_string($_SESSION['username']). "' AND pwd='" .md5(mysql_real_escape_string($_SESSION['pass'])). "'")); // Bekérjük az adatokat
		
		$_SESSION['userLevel'] = $adat['userLevel']; // Tároljuk a felhasználó szintjét
		
		switch ($adat['userLevel']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
		{
			case -1:
				$_SESSION['userLevelTXT'] = 'Kitiltva';
				break;
			case 0:
				$_SESSION['userLevelTXT'] = 'Nincs aktiválva';
				$_SESSION['userLevel'] = 0;
				break;
			case 1:
				$_SESSION['userLevelTXT'] = 'Felhasználó';
				break;
			case 2:
				$_SESSION['userLevelTXT'] = 'Moderátor';
				break;
			case 3:
				$_SESSION['userLevelTXT'] = 'Adminisztrátor';
				break;
		}
		$_SESSION['realName'] = $adat['realName']; // Tároljuk a felhasználó igazi nevét
		$_SESSION['postCount'] = $adat['postCount']; // Tároljuk a felhasználó hozzászólásszámát
		$_SESSION['regdate'] = $adat['regdate']; // Regisztrálás időpontja
		$_SESSION['userID'] = $adat['id']; // Felhasználó ID
		
		/* Téma értékének tárolása. Ha nincs téma az adatbázisban a felhasználóhoz rendelve
			a DEFAULT témát adjuk meg */
		$_SESSION['themeName'] = $adat['theme'];
		if ($adat['theme'] == $NULL)
			$_SESSION['themeName'] = "default";
		
		$wf_debug->RegisterDLEvent("Felhasználó adatai betöltve és tárolva");
	}
	
	function ForcedLogout() // Kényszerített kiléptetés
	{
		global $wf_debug;
		$wf_debug->RegisterDLEvent("Kényszerített kijelentkeztetés...");
		
		Hibauzenet("WARNING", "Ki lettél jelentkeztetve"); // Hibaüzenet megjelenítése
		$this->Logout; // Kiléptetjük a usert
		$this->DoLoginForm(); // Beléptető ablak
	}
	
	function CheckIfLoggedIn( $username ) // A felhasználó bejelentkezettségének ellenörzése
	{
		global $cfg, $sql, $session, $wf_debug;
		
		$wf_debug->RegisterDLEvent("Felhasználó bejelentkezettségének ellenörzése");
		
		if ($username != '')
		{
			$session->CheckSession(session_id(), $_SERVER['REMOTE_ADDR']); // Ellenörzés
			$this->DoControlForm(); // Belépett panel létrehozása a login helyén
		} else {
			$this->DoLoginForm(); // Loginpanel létrehozása
		}
	}
}

class session // Munkamenet (session) kezelő osztály
{
	function StartSession( $username, $pass ) // Munkamenet elindítása, bejelentkeztetés
	{
		global $cfg, $sql, $wf_debug;
		
		session_start(); // Indítás
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "user SET lastip='" .$_SERVER['REMOTE_ADDR']. "', lastsessid='" .session_id()."', loggedin='1', cursessid='" .session_id(). "', curip='" .$_SERVER['REMOTE_ADDR']. "', lastlogintime='" .time(). "' WHERE username='" .mysql_real_escape_string($username). "' AND pwd='" .md5(mysql_real_escape_string($pass)). "'");
		$_SESSION['username'] = $username;
		$_SESSION['pass'] = $pass;
		
		$wf_debug->RegisterDLEvent("Munkamenet elindítva");
	}
	
	function CheckSession($sid, $ip) // Belépettség megjelenítése
	{
		global $cfg, $user, $sql, $wf_debug;
		
		$wf_debug->RegisterDLEvent("Session ellenörzése");
		
		$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE username='" .mysql_real_escape_string($_SESSION['username']). "' AND pwd='" .md5(mysql_real_escape_string($_SESSION['pass'])). "'")); // Bekérjük a session adatokat és IP-t
		
		if ( ($sid == $adat['cursessid']) && ($ip == $adat['curip']) ) // Egyezés ellenörzése (ip cím és session ID)
		{
			$_SESSION['loggedin'] = 1; // Be vagyunk jelentkeztetve
			
		} else {
			$user->ForcedLogout(); // Kényszerített kiléptetés
		}
	}
	
	function Purge() // Session kiürítése
	{
		global $wf_debug;
		
		$_SESSION['username'] = "";
		$_SESSION['pass'] = "";
		$_SESSION['userLevel'] = "";
		$_SESSION['userLevelTXT'] = "";
		$_SESSION['loggedin'] = 0;
		$_SESSION['realName'] = "";
		$_SESSION['postCount'] = 0;
		$_SESSION['regDate'] = 0;
		$_SESSION['userID'] = 0;
		$_SESSION['themeName'] = "";
		
		$wf_debug->RegisterDLEvent("Session kiürítve");
		
		session_destroy();
		$wf_debug->RegisterDLEvent("Session törölve");
	}
}
	
	// Létrehozzuk a globális $user változót
	// mellyel meghívhatjuk az osztály függvényeit
	global $user;
	$user = new user();
	// Létrehozzuk a globális $session változót
	// mellyel kezelhetjük a munkameneteket
	global $session;
	$session = new session();
	
	/* Ha van bejövő felhasználónév-jelszó kombó, beléptetjük a usert */
	if ( ($_POST['username'] != $NULL) && ($_POST['pwd'] != $NULL) && ($_POST['cmd'] == 'loginusr') )
		$user->Login($_POST['username'], $_POST['pwd']);
	
	if ( $_POST['cmd'] == 'logoutusr' )
		$user->Logout(); // Felhasználó kiléptetése
?>