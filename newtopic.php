<?php
 /**
 * WhispyForum script file - newtopic.php
 * 
 * Adding new topic
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("forum/topics_create_head", FALSE); // Header

if ( !isset($_POST['forum_id']) )
{
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
		'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
		'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
		'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
	), FALSE ); // We give an error
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("forum/topics_create_foot", FALSE); // Footer
	DoFooter();
	exit;
}

// Get the current user's level
$uLvl = mysql_fetch_row($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'"));

// Query the minimal level for the forum
$fMLvl = mysql_fetch_row($Cmysql->Query("SELECT minLevel FROM forums WHERE id='" .$Cmysql->EscapeString($_POST['forum_id']). "'"));

if ( $uLvl[0] < $fMLvl[0] )
{
	// If the user is on lower level
	// than the currently required to view the forum
	
	// First, generate the variable which stores the
	// name of the level to be on to view the forum.
	
	switch ($fMLvl[0]) // Minimal level required to view the forum
	{
		case 0:
			// Guest
			/* It's really purposeless, the default minimum is guest and users cannot be lower than guests */
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_GUEST}'];
			break;
		case 1:
			// User
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_USER}'];
			break;
		case 2:
			// Moderator
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_MODERATOR}'];
			break;
		case 3:
			// Administrator
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_ADMINISTRATOR}'];
			break;
	}
	
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
		'BODY'	=>	$minLName, // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
	), FALSE ); // Give rights error
} elseif ( $uLvl[0] >= $fMLvl[0] )
{
	// The user has the rights to view the topic list, thus has rights to create one
	
	if ( !isset($_POST['post_do']) )
	{
		$fTitle = mysql_fetch_row($Cmysql->Query("SELECT title FROM forums WHERE id='" .$Cmysql->EscapeString($_POST['forum_id']). "'")); // Query the title of the forum
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("forum/topics_create_form", array(
				'FORUM_ID'	=>	$_POST['forum_id'],
				'FORUM_NAME'	=>	$fTitle[0],
				'TITLE'	=>	$_POST['title'], // Title of the topic
				'POST_TITLE'	=>	$_POST['post_title'], // Title of the post
				'POST_CONTENT'	=>	$_POST['post_content'] // Post body
			), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("forum/topics_create_form", array(
				'FORUM_ID'	=>	$_POST['forum_id'],
				'FORUM_NAME'	=>	$fTitle[0],
				'TITLE'	=>	"", // Title of the topic
				'POST_TITLE'	=>	"", // Title of the post
				'POST_CONTENT'	=>	"" // Post body
			), FALSE);
		}
	}
	
	if ( @$_POST['post_do'] == "do" )
	{
		// Check for missing mandatory variables...
		
		if ( $_POST['title'] == NULL ) // Title of the topic
		{
			$Ctemplate->useTemplate("forum/topics_create_variable_error", array(
				'VARIABLE'	=>	"{LANG_FORUMS_TITLE}", // Missing variable's name
				'FORUM_ID'	=>	$_POST['forum_id'],
				'TITLE'	=>	$_POST['title'], // Title of the topic (should be empty)
				'POST_TITLE'	=>	$_POST['post_title'], // Title of the post
				'POST_CONTENT'	=>	$_POST['post_content'] // Post body
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("forum/topics_create_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( $_POST['post_content'] == NULL ) // Post body
		{
			$Ctemplate->useTemplate("forum/topics_create_variable_error", array(
				'VARIABLE'	=>	"{LANG_POSTS_POST}", // Missing variable's name
				'FORUM_ID'	=>	$_POST['forum_id'],
				'TITLE'	=>	$_POST['title'], // Title of the topic
				'POST_TITLE'	=>	$_POST['post_title'], // Title of the post
				'POST_CONTENT'	=>	$_POST['post_content'] // Post body (should be empty)
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("forum/topics_create_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Every variable is entered, doing SQL work
		$topic_create = $Cmysql->Query("INSERT INTO topics(forumid, title, createuser, createdate) VALUES (
			'" .$Cmysql->EscapeString($_POST['forum_id']). "',
			'" .$Cmysql->EscapeString($_POST['title']). "',
			'" .$_SESSION['uid']. "', '" .time(). "')"); // Topic creation
		
		$post_create = $Cmysql->Query("INSERT INTO posts(topicid, forumid, title, createuser, createdate, content) VALUES (
			'" .mysql_insert_id(). "',
			'" .$Cmysql->EscapeString($_POST['forum_id']). "',
			'" .$Cmysql->EscapeString($_POST['post_title']). "',
			'" .$_SESSION['uid']. "', '" .time(). "',
			'" .$Cmysql->EscapeString($_POST['post_content']). "')"); // Post adding (to the previously created topic)
		
		if ( ( $topic_create == FALSE ) && ( $post_create == FALSE ) )
		{
		
			$Ctemplate->useTemplate("forum/topics_create_error", array(
				'FORUM_ID'	=>	$_POST['forum_id'],
				'TITLE'	=>	$_POST['title'], // Title of the topic
				'POST_TITLE'	=>	$_POST['post_title'], // Title of the post
				'POST_CONTENT'	=>	$_POST['post_content'] // Post body
			), FALSE); // Give error if we failed the creation
		} elseif ( ( $topic_create == TRUE ) && ( $post_create == TRUE ) )
		{
			$Ctemplate->useTemplate("forum/topics_create_success", array(
				'FORUM_ID'	=>	$_POST['forum_id'],
				'TITLE'	=>	$_POST['title'], // Title of the topic
			), FALSE); // Give success if we did it!
		}
	}
}

$Ctemplate->useStaticTemplate("forum/topics_create_foot", FALSE); // Footer
DoFooter();
?>