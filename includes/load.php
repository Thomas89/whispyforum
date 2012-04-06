<?php
/**
 * WhispyForum
 * 
 * /includes/load.php
*/

// Define that the system is loaded.
define('WHISPYFORUM', TRUE);

if ( file_exists("config.php") == 1 ) 
{
	require("config.php");
} elseif ( file_exists("config.php") == 0 )
{
	die("Missing configuration file.");
}

// Load the requested libraries
require("includes/functions.php");
require("includes/mysql.php");
require("includes/template.php");
require("includes/user.php");

global $template, $sql, $user;
$template = new template();
$sql = new mysql( $cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname'] );
$user = new user(0, FALSE);

/* DEVELOPEMENT */
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
prettyVar($_GET, true);
print "<h4>POST</h4>";
prettyVar($_POST, true);
print "<h4>FILES</h4>";
prettyVar($_FILES, true);

print "<h4>SESSION</h4>";
prettyVar($_SESSION, true);
// print "<h4>SERVER</h4>";
// prettyVar($_SERVER, true);
// print "<h4>REQUEST</h4>";
// prettyVar($_REQUEST, true);
// print "<h4>ENVIRONMENT</h4>";
// prettyVar($_ENV, true);
// print "<h4>COOKIES</h4>";
// prettyVar($_COOKIE, true);
// print "<h4>Configuration</h4>";
// prettyVar($cfg, true);
// print "<h4>Localization</h4>";
// prettyVar($wf_lang, true);
/* DEVELOPEMENT */

$template->load_template("framework", TRUE);

print $template->parse_template("header", array(
	'GLOBAL_TITLE'	=>	config("global_title"),
	'THEME_NAME'	=>	( $user->get_value("theme") === USER_NO_KEY ? config("theme") : $user->get_value("theme") ) ));

$template->create_stack("left");

function DoFooter()
{
	global $template, $sql, $user;
	
	prettyVar($user);
	prettyVar($sql);
	prettyVar($template);
	
	unset($user);
	unset($sql);
	unset($template);
	exit;
}

function dieOnModule() { }

?>
