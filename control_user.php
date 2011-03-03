<?php
 /**
 * WhispyForum script file - control_user.php
 * 
 * User control panel. Usage: help individuals set user-specific properties.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("user/cp_head", FALSE); // Header

// We define the $site variable
$site = "";

if ( $_SESSION['log_bool'] == FALSE )
{
	// If the user is a guest
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_NO_GUESTS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_LOGGEDIN}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
	), FALSE ); // We give an unaviable error
} elseif ( $_SESSION['log_bool'] == TRUE)
{
// If user is logged in, the control panel is accessible

if ( isset($_POST['site']) )
{
	// If site is passed by POST
	// the site variable is the POSTed value
	
	$site = $_POST['site'];
} elseif ( !isset($_POST['site']) )
{
	// If the POSTed variable is NULL
	// we see if there's site variable GET
	
	if ( isset($_GET['site']) )
	{
		// If there is, site is the GET value
		$site = $_GET['site'];
	} elseif ( !isset($_GET['site']) )
	{
		// If not, site is NULL
		$site = NULL;
	}
}

// Now, the site variable is either NULL or set from HTTP POST/GET

switch ($site)
{
	case "avatar_upload":
		// Avatar uploading
		if ( isset($_POST['av_upload']) ) // If there's uploading
		{
			if ( $_FILES['pic_file']['size'] > 2097152 )
			{
				// Big size (larger than 2 MBs)
				$Ctemplate->useTemplate("user/cp_avatar_upload_toobigfile_error", array(
					'FILE_SIZE'	=>	DecodeSize($_FILES['pic_file']['size'])
				), FALSE); // Give error
			} else {
				if ( in_array($_FILES['pic_file']['type'], array("image/gif", "image/jpeg", "image/png")) )
				{
					if ( @move_uploaded_file($_FILES['pic_file']['tmp_name'], "upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp") ) // Move the file to the temp location
					{
						// Uploaded successfully
						
						$fnToken = generateHexTokenNoDC(); // Generate a filename token
						
						if ( $_FILES['pic_file']['type'] == "image/jpeg" )
						{
							// If the file is a JPEG file
							
							saveThumbnailJPEG("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp", 150, "upload/usr_avatar/".$fnToken); // Save the thumbnail
							
							unlink("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp"); // Delete the original uploaded file
							
							$fExt = ".jpg"; // Set the file extension
						}
						
						if ( $_FILES['pic_file']['type'] == "image/png" )
						{
							// If the file is a PNG file
							
							saveThumbnailPNG("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp", 150, "upload/usr_avatar/".$fnToken); // Save the thumbnail
							
							unlink("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp"); // Delete the original uploaded file
							
							$fExt = ".png"; // Set the file extension
						}
						
						if ( $_FILES['pic_file']['type'] == "image/gif" )
						{
							// If the file is a GIF file
							
							saveThumbnailGIF("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp", 150, "upload/usr_avatar/".$fnToken); // Save the thumbnail
							
							unlink("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp"); // Delete the original uploaded file
							
							$fExt = ".gif"; // Set the file extension
						}
						
						$Cmysql->Query("UPDATE users SET avatar_filename='" .$fnToken.$fExt. "' WHERE id='" .$_SESSION['uid']. "'"); // Update database
						
						@unlink("upload/usr_avatar/" .$_SESSION['avatar_filename']); // Remove the old avatar file
						
						$_SESSION['avatar_filename'] = $fnToken.$fExt; // Update session with new avatar filename (refreshing avatar does not need user relog)
						
						// Successful upload
						$Ctemplate->useTemplate("user/cp_avatar_upload_success", array(
							'AVATAR_FILENAME'	=>	$fnToken.$fExt
						), FALSE); // Give success
					} else {
						// Error during upload
						$Ctemplate->useStaticTemplate("user/cp_avatar_upload_error", FALSE); // Give error
					}
				} else {
					// Wrong filetype
					$Ctemplate->useTemplate("user/cp_avatar_upload_filetype_error", array(
						'FILE_TYPE'	=>	$_FILES['pic_file']['type']
					), FALSE); // Give error
				}
			}
		} else {
			// If there's no upload request
			$Ctemplate->useTemplate("user/cp_avatar_upload", array(
				'AVATAR_FILENAME'	=>	$_SESSION['avatar_filename'], // Current avatar filename (needs implementation)
			), FALSE); // We output the upload form
		}
		break;
	case "site_preferences":
		// Setting site preferences (theme, language)
		// Parsing form input
		if ( isset($_POST['set_type']) )
		{
			if ( ( $_POST['set_type'] == "language" ) && ( isset($_POST['new_lang']) ) )
			{
				// Change the language in the database
				$Lmod = $Cmysql->Query("UPDATE users SET language='" .$Cmysql->EscapeString($_POST['new_lang']). "' WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'");
				
				// $Lmod is TRUE if we succeed and FALSE if we fail
				if ( $Lmod == FALSE )
				{
					// If we failed
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"{LANG_SITEPREF_MODIFY_LANGUAGE_ERROR}", // Error title
						'BODY'	=>	"", // Error text
						'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
					), FALSE ); // We give an unaviable error
				} elseif ( $Lmod == TRUE )
				{
					// If we succeeded
					$Ctemplate->useTemplate("successbox", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_home.png", // House (user CP header)
						'TITLE'	=>	"{LANG_SITEPREF_MODIFY_LANGUAGE_SUCCESS}", // Success title
						'BODY'	=>	"{LANG_SITEPREF_MODIFY_LANGUAGE_SUCCESS_1}", // Success text
						'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
					), FALSE ); // We give a success message
					
					// Modify the session so the next page load
					// will load the new language
					$_SESSION['usr_language'] = $_POST['new_lang'];
				}
			}
		} else {
			$Ctemplate->useStaticTemplate("user/cp_siteprefs", FALSE);
			
			/* Language settings */
			$Ldir = "./language/"; // Language home dir
			$Lexempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
			
			$Ctemplate->useStaticTemplate("user/cp_siteprefs_lang_form", FALSE); // Opening the form
			
			if (is_dir($Ldir)) 
			{
				if ($Ldh = opendir($Ldir))
				{
					while (($Lfile = readdir($Ldh)) !== false)
					{
						if(!in_array(strtolower($Lfile),$Lexempt))
						{
							if ( filetype($Ldir . $Lfile) == "dir" )
							{
								// We're now querying all language directories
								if ( ( file_exists($Ldir . $Lfile . "/language.php") ) && ( file_exists($Ldir . $Lfile . "/definition.php") ) )
								{
									// We only list directories containing the language AND the definition file
									include($Ldir.$Lfile."/definition.php"); // This will load in $wf_lang_def (containing the definition)
									
									$Ctemplate->useTemplate("user/cp_siteprefs_lang_option", array(
										'SELECTED'	=>	($Lfile == $_SESSION['usr_language'] ? " selected " : " "), // Selected is ' ' if it's another language, ' selected ' if it's the current. It makes the current language automatically re-selected
										'DIR_NAME'	=>	$Lfile, // Name of the language's directory
										'LOCALIZED_NAME'	=>	$wf_lang_def['LOCALIZED_NAME'], // The language's own, localized name (so it's Deutch for German)
										'SHORT_NAME'	=>	$wf_lang_def['SHORT_NAME'], // The language's English name (so it's German for German)
										'L_CODE'	=>	$wf_lang_def['LANG_CODE'] // Language code (it's de for German)
									), FALSE);
								}
							}
						}
					}
					closedir($Ldh);
				}
			}
			
			$Ctemplate->useStaticTemplate("user/cp_siteprefs_lang_foot", FALSE); // Closing the form
			/* Language settings */
			
			/* Theme settings */
/*
			$Tdir = "./themes/"; // Language home dir
			$Texempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
			
			$Ctemplate->useStaticTemplate("", FALSE); // Theme header
			
			if (is_dir($Tdir)) 
			{
				if ($Tdh = opendir($Tdir))
				{
					while (($Tfile = readdir($Tdh)) !== false)
					{
						if(!in_array(strtolower($Tfile),$Texempt))
						{
							if ( filetype($Tdir . $Tfile) == "dir" )
							{
								// We're now querying all language directories
								if ( file_exists($Tdir . $Tfile . "/style.css") )
								{
									// We only list directories containing the stylesheet file
									
								}
							}
						}
					}
					closedir($Tdh);
				}
			}
*/
			/* Theme settings */
		}
		break;
}

}
$Ctemplate->useStaticTemplate("user/cp_foot", FALSE); // Footer
DoFooter();
?>