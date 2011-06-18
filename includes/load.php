<?php
 /**
 * WhispyForum function file - load.php
 * 
 * Loads all required libraries for usage.
 * 
 * WhispyForum
 */
 
/* Libraries */
// Template conductor (we load it before everything because templates are needed to get error messages)
require("templates.class.php");
global $Ctemplate; // Class is global
$Ctemplate = new class_template;
/* Libraries */

// Load boot-time localizations (it's a lite edition of the general English localization, only containing strings which are required before initializing the user array)
include("language/bootlocal.php");

/* Preload checks */
// Check whether configuration file exists
if ( ( file_exists("config.php") == 1 ) && ( file_exists("config.md5") == 1 ) )
{
	// Check whether the installation was successful and the config file is valid
	if ( file_get_contents("config.md5") != md5(file_get_contents("config.php")) )
	{
		// We embed the default (winky) stylesheet so the error message will appear properly
		echo '<link rel="stylesheet" type="text/css" href="themes/winky/style.css">';
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_package_settings.png", // Text file icon
			'TITLE'	=>	"{LANG_LOAD_CORRUPTION}", // Error title
			'BODY'	=>	"{LANG_LOAD_CORRUPTION_BODY}", // Error text
			'ALT'	=>	"{LANG_LOAD_CORRUPTION_ALT}" // Alternate picture text
		), FALSE); // We output an error message
		exit; // Terminate execution
	} elseif ( file_get_contents("config.md5") === md5(file_get_contents("config.php")) )
	{
		require("config.php"); // Load the configuration file
	}
} elseif ( file_exists("config.php") == 0 ) // If not
{
	// We embed the default (winky) stylesheet so the error message will appear properly
	echo '<link rel="stylesheet" type="text/css" href="themes/winky/style.css">';
	
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Unavailable file icon
		'TITLE'	=>	"{LANG_LOAD_NOCFG}", // Error title
		'BODY'	=>	"{LANG_LOAD_NOCFG_BODY}", // Error text
		'ALT'	=>	"{LANG_FILE_UNAVAILABLE}" // Alternate picture text
	), FALSE); // We output an error message
	exit; // Terminate execution
}

/* Preload checks */

/* Libraries */
// mySQL database layer
require("mysql.class.php");
global $Cmysql; // Class is global
$Cmysql = new class_mysql;

// users & session manager 
require("users.class.php");
global $Cusers; // Class is global
$Cusers = new class_users;

// general functions
require("includes/functions.php");
/* Libraries */

/* DEVELOPEMENT */
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_GET,true))."<br>";
print "<h4>POST</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_POST,true))."<br>";
print "<h4>FILES</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_FILES,true))."<br>";

/* START GENERATION */
$Cmysql->Connect(); // Connect to database
$Cusers->Initialize(); // We initialize the userdata
// User initialization also loads the language file

// Badge manager
require("badges.class.php");
global $Cbadges; // Class is global
$Cbadges = new class_badges;
$Cbadges->Init(); // Load and define the badge array

// Generate framework header
$Ctemplate->useTemplate("framework/header", array(
	'GLOBAL_TITLE'	=>	config("global_title")
), FALSE);
/* HEADER */

/* DEVELOPEMENT */
print "<h4>SESSION</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_SESSION,true))."<br>";
// print "<h4>SERVER</h4>";
// print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_SERVER,true))."<br>";
// print "<h4>REQUEST</h4>";
// print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_REQUEST,true))."<br>";
// print "<h4>ENVIRONMENT</h4>";
// print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_ENV,true))."<br>";
// print "<h4>COOKIES</h4>";
// print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_COOKIE,true))."<br>";
// print "<h4>Configuration</h4>";
// print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($cfg,true))."<br>";
// print "<h4>Localization</h4>";
// print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($wf_lang,true))."<br>";

/* FRAMEWORK */

$Ctemplate->useStaticTemplate("framework/left", FALSE); // Center table and left menubar begin
/* Left menubar */
	$Cusers->DoUserForm(); // Do login form or userbox
	$Ctemplate->DoMenuBars('LEFT'); // Do right menubar
/* Left menubar */

$Ctemplate->useStaticTemplate("framework/center", FALSE); // Closing left menubar and opening center

function DoFooter()
{
	global $Ctemplate, $Cmysql; // Load classes
	
	$Ctemplate->useStaticTemplate("framework/right", FALSE); // Close center table and right menubar begin
		$Ctemplate->DoMenuBars('RIGHT'); // Do right menubar
	$Ctemplate->useStaticTemplate("framework/footer", FALSE); // Close right menubar and generate footer
	/* FOOTER */
	
	$Ctemplate->useStaticTemplate("framework/footer_close", FALSE); // Close footer
	
	$Cmysql->Disconnect(); // Disconnect from database
	
}

/* FRAMEWORK */

/* END GENERATION */
?>
