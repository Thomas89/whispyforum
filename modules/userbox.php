<?php
/**
 * WhispyForum
 * 
 * This module serves as the user's personal box on the sidebars, handling
 * queries to the login/logout transition pages.
 * 
 * /modules/userbox.php
*/

// Some dieout statements to prevent loading the module without appropriate framework.
if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

if ( !isset($this) || !is_object($this) )
	die("Module loaded without module context.");

switch ( $part )
{
	case "general_execute":
		global $user, $sql, $template;
		
		$template->load_template("userbox", TRUE);
		
		if ( $_SESSION['id'] === 0 || $user->userid === 0 )
		{
			// Output a login box if the user is not logged in.
			$ret = $template->parse_template("user login", array(
				'LANG_LOGIN'	=>	lang_key("USERBOX LOGIN"),
				'LANG_USERNAME'	=>	lang_key("USERNAME"),
				'LANG_PASSWORD'	=>	lang_key("PASSWORD"),
				'LANG_REGISTER'	=>	lang_key("USERBOX REGISTER"),
				'LANG_PWDRECOVER_LINK'	=>	lang_key("USERBOX RECOVER"),
				'RETURN_TO'	=>	selfURL()
			) );
		} elseif ( $_SESSION['id'] !== 0 && $user->userid !== 0 )
		{
			// If the user is logged in, output a userbox for logged-in users.
			
		}
		
		break;
	case NULL:
	default:
		$ret = TRUE;
		break;
}
?>