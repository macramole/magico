<?php

class My_Loader extends CI_Loader
{
	var $_ci_view_paths		= array();
	
	function __construct()
	{		
		parent::__construct();
		
		$this->_ci_view_paths = array(APPPATH);
	}

	/**
	 * Add Package Path
	 *
	 * Prepends a parent path to the library, model, helper, and config path arrays
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function add_package_path($path)
	{
		$path = rtrim($path, '/').'/';

		array_unshift($this->_ci_library_paths, $path);
		array_unshift($this->_ci_model_paths, $path);
		array_unshift($this->_ci_helper_paths, $path);
		array_unshift($this->_ci_view_paths, $path);

		// Add config file path
		$config =& $this->_ci_get_component('config');
		array_unshift($config->_config_paths, $path);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Remove Package Path
	 *
	 * Remove a path from the library, model, and helper path arrays if it exists
	 * If no path is provided, the most recently added path is removed.
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function remove_package_path($path = '', $remove_config_path = TRUE)
	{
		$config =& $this->_ci_get_component('config');

		if ($path == '')
		{
			$void = array_shift($this->_ci_library_paths);
			$void = array_shift($this->_ci_model_paths);
			$void = array_shift($this->_ci_helper_paths);
			$void = array_shift($this->_ci_view_paths);
			$void = array_shift($config->_config_paths);
		}
		else
		{
			$path = rtrim($path, '/').'/';

			foreach (array('_ci_library_paths', '_ci_model_paths', '_ci_helper_paths', '_ci_view_paths') as $var)
			{
				if (($key = array_search($path, $this->{$var})) !== FALSE)
				{
					unset($this->{$var}[$key]);
				}
			}

			if (($key = array_search($path, $config->_config_paths)) !== FALSE)
			{
				unset($config->_config_paths[$key]);
			}
		}

		// make sure the application default paths are still in the array
		$this->_ci_library_paths = array_unique(array_merge($this->_ci_library_paths, array(APPPATH, BASEPATH)));
		$this->_ci_helper_paths = array_unique(array_merge($this->_ci_helper_paths, array(APPPATH, BASEPATH)));
		$this->_ci_model_paths = array_unique(array_merge($this->_ci_model_paths, array(APPPATH)));
		$this->_ci_view_paths = array_unique(array_merge($this->_ci_view_paths, array(APPPATH)));
		$config->_config_paths = array_unique(array_merge($config->_config_paths, array(APPPATH)));
	}	
	
	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 * Variables are prefixed with _ci_ to avoid symbol collision with
	 * variables made available to view files
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _ci_load($_ci_data)
	{
		// Set the default data variables
		foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val)
		{
			$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		}

		if ($_ci_path == '')
		{
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext == '') ? $_ci_view.EXT : $_ci_view;
												
			foreach ( $this->_ci_view_paths as $path)
			{
				$filepath = $path.'views/'.$_ci_file;
																	
				if ( file_exists($filepath) )
				{
					$_ci_path = $filepath;
					break;
				}
			}
		}
		else
		{
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		}

		if ( ! file_exists($_ci_path))
		{
			show_error('Unable to load the requested file: '.$_ci_file);
		}

		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.

		$_ci_CI =& get_instance();
		foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var)
		{
			if ( ! isset($this->$_ci_key))
			{
				$this->$_ci_key =& $_ci_CI->$_ci_key;
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load_vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */
		if (is_array($_ci_vars))
		{
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();

		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.

		if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
		}
		else
		{
			include($_ci_path); // include() vs include_once() allows for multiple views with the same name
		}

		log_message('debug', 'File loaded: '.$_ci_path);

		// Return the file data if requested
		if ($_ci_return === TRUE)
		{
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */
		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			$_ci_CI->output->append_output(ob_get_contents());
			@ob_end_clean();
		}
	}
	
	
	/**
	 * Load class
	 *
	 * This function loads the requested class.
	 * 
	 * modified by Pârleboo
	 *
	 * @param	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */
	protected function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		$class = str_replace('.php', '', trim($class, '/'));

		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if (($last_slash = strrpos($class, '/')) !== FALSE)
		{
			// Extract the path
			$subdir = substr($class, 0, $last_slash + 1);

			// Get the filename from the path
			$class = substr($class, $last_slash + 1);
		}

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			// Is this a class extension request?
			// Pârleboo: check in all library paths not just the main one
			foreach ($this->_ci_library_paths as $path)
			{
				$subclass = $path.'libraries/'.$subdir.config_item('subclass_prefix').$class.'.php';
				
				if (file_exists($subclass))
				{
					$baseclass = BASEPATH.'libraries/'.ucfirst($class).'.php';

					if ( ! file_exists($baseclass))
					{
						log_message('error', "Unable to load the requested class: ".$class);
						show_error("Unable to load the requested class: ".$class);
					}

					// Safety:  Was the class already loaded by a previous call?
					if (in_array($subclass, $this->_ci_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& get_instance();
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
							}
						}

						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}

					include_once($baseclass);
					include_once($subclass);
					$this->_ci_loaded_files[] = $subclass;

					return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
				}

				// Lets search for the requested library file and load it.
				$is_duplicate = FALSE;
				foreach ($this->_ci_library_paths as $path)
				{
					$filepath = $path.'libraries/'.$subdir.$class.'.php';

					// Does the file exist?  No?  Bummer...
					if ( ! file_exists($filepath))
					{
						continue;
					}

					// Safety:  Was the class already loaded by a previous call?
					if (in_array($filepath, $this->_ci_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& get_instance();
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, '', $params, $object_name);
							}
						}

						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}

					include_once($filepath);
					$this->_ci_loaded_files[] = $filepath;
					return $this->_ci_init_class($class, '', $params, $object_name);
				}
			}
		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_ci_load_class($path, $params);
		}

		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}
}