<?php
/**
 * WhispyForum
 * 
 * /includes/module.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

class module
{
	/**
	 * Modules are executable code snippets embedded into the system.
	 * This class provides the transparent layer to load, execute and setup modules.
	 * The core code of modules are the files in the /modules folder.
	*/
	
	// _modconf and _modconf_diff contain the configuration of the concurrently loaded module.
	// (For further explanation, see .)
	private $_modconf = array();
	private $_modconf_diff = array();
	
	// _module_id is the AUTO_INCREMENT id of the concurrent module loaded
	private $_module_id = 0;
	
	// _module_file is the filename of the module script, relative to /modules
	private $_module_file = NULL;
	
	function __construct( $mod_id = 0, $mod_file = NULL )
	{
		/**
		 * The constructor executes automatically at referencing, and loads the instance.
		 * 
		 * $mod_id is the ID variable in the module table, if omitted, the module is loaded as a "new" module
		 * $mod_file is the filename of the module script relative to /modules
		*/
		
		global $sql;
		
		// Return error if nothing if specified from which we can specify the module loaded.
		if ( $mod_id === 0 && $mod_file === NULL )
		{
			echo "Error! Unable to load unspecified new module.";
			return FALSE;
		}
		
		// If the module's ID is not default, figure out the file it is linked to.
		if ( $mod_id !== 0 )
		{
			$this->_module_id = $mod_id;
			
			$result = $sql->fetch_array(
				$sql->Query("SELECT `module` FROM `modules` WHERE `id`=" .$sql->escape($mod_id). " LIMIT 1;"),
				SQL_NUM);
			
			$this->_module_file = $result[0];
			
			// If we specified a module filename but it is not what is set in the database, return error.
			if ( $mod_file !== NULL && $mod_file !== $result[0] )
			{
				echo "Error! Module #" .$mod_id. " was called with script set to " .$mod_file. " while the database points to " .$result[0]. ".";
				return FALSE;
			}
		}
		
		// If we load a new module and specify a filename, load it.
		if ( $mod_id === 0 && $mod_file !== NULL )
			$this->_module_file = $mod_file;
		
		if ( !file_exists("modules/" .$this->_module_file. ".php") )
		{
			echo "The requested module file " .$this->_module_file. " does not exist.";
			return FALSE;
		}
		
		// Extract module configuration from database.
		if ( $mod_id !== 0 )
		{
			$result = $sql->query("SELECT `extra_data` FROM `modules` WHERE `id`=" .$sql->escape($mod_id). " LIMIT 1;");
			$data = $sql->fetch_array($result, SQL_NUM);
			
			foreach ( unserialize($data[0]) as $k => $v )
				$this->_modconf[$k] = $v;
		}
		
		// Define a constant for get_value()
		@define('MODCONF_NO_KEY', "requested-key-not-present");
	}
	
	function execute( $part = NULL )
	{
		/**
		 * This function includes the module script with the set $part value.
		 * Different $part values can be used to include multiple execution scenarios into one module file.
		*/
		
		// Include loads the file into the namespace of this function, so we hook all the global classes.
		global $template, $sql, $user;
		
		include("modules/" .$this->_module_file. ".php");
		
		return ( isset($ret) ? $ret : NULL);
	}
	
	function get_value( $key, $past = FALSE )
	{
		/**
		 * This function returns the requested key ($key) from _modconf.
		 * 
		 * If the said key has been already modified in the current thread, we return the new value,
		 * unless $past is set to TRUE.
		*/
		
		if ( $past === FALSE )
		{
			if ( array_key_exists($key, $this->_modconf_diff) )
			{
				$ret = $this->_modconf_diff[$key];
			} elseif ( array_key_exists($key, $this->_modconf) && !array_key_exists($key, $this->_modconf_diff) )
			{
				$ret = $this->_modconf[$key];
			} elseif ( !array_key_exists($key, $this->_modconf) && !array_key_exists($key, $this->_modconf_diff) )
			{
				$ret = MODCONF_NO_KEY;
			}
		} elseif ( $past === TRUE )
		{
			if ( array_key_exists($key, $this->_modconf) )
			{
				$ret = $this->_modconf[$key];
			} elseif ( !array_key_exists($key, $this->_modconf) )
			{
				$ret = MODCONF_NO_KEY;
			}
		}
		
		return $ret;
	}
	
	function set_value( $key, $value )
	{
		/**
		 * This function sets the $key key of modconf to the new $value value.
		*/
		
		if ( isset($key) && isset($value) )
		{
			if ( array_key_exists($key, $this->_modconf) && $this->_modconf[$key] === $value )
			{
				return TRUE;
			} else {
				$this->_modconf_diff[$key] = $value;
				return TRUE;
			}
		}
	}
	
	function __destruct()
	{
		/**
		 * Destructor executes at dereference of the object.
		 * This codeblock saves the modified _modconf keys (_modconf_diff), if any.
		*/
		
		global $sql;
		
		// If there are no keys to modify, we return FALSE.
		if ( count( $this->_modconf_diff ) === 0 )
			return FALSE;
		
		// Merge the new values into the array of old values.
		foreach ( $this->_modconf_diff as $k => $v )
			$this->_modconf[$k] = $v;
		
		$sql->query("UPDATE `modules` SET `extra_data`='" .$sql->escape( serialize($this->_modconf) ). "' WHERE `id`=" .$sql->escape($this->_module_id). " LIMIT 1;");
	}
}
?>
