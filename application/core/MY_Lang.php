<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// CodeIgniter i18n library by Jérôme Jaglale
// http://maestric.com/en/doc/php/codeigniter_i18n
// version 10 - May 10, 2012
//
// Modified by Pârleboo

class MY_Lang extends CI_Lang {

	/**************************************************
	 configuration
	***************************************************/

	// languages
	var $languages = null;

	// special URIs (not localized)
	var $special = array (
		"magico", "magico_logout", "magico_login" //,""
	);
	
	// set admin language instead of site's language (added by Pârleboo)
	var $adminPages = array(
		"abm"
	);

	/**************************************************/
	
	
	function __construct()
	{
		parent::__construct();		
		global $CFG;
		global $URI;
		
		$this->languages = $CFG->item('languages');
		
		if ( is_array($CFG->item('additional_not_i18n')) )
			$this->special = array_merge($this->special, $CFG->item('additional_not_i18n'));
		
		if ( !$this->has_multiple_languages() )
			return;
		
		$segment = $URI->segment(1);
		
		
		if ( $this->is_admin_page() )
		{
			$CFG->set_item('language', $CFG->item('admin_language') );
			
		}
		else if (isset($this->languages[$segment]))	// URI with language -> ok
		{
			$language = $this->languages[$segment];
			$CFG->set_item('language', $language);
			
		}
		else if($this->is_special($segment)) // special URI -> no redirect
		{
			return;
		}
		else	// URI without language -> redirect to default_uri
		{
			$preferedLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			$lang = $this->default_lang();
			
			if ( $this->languages[$preferedLang] )
				$lang = $preferedLang;
			
			// set default language
			//$CFG->set_item('language', );

			// redirect
			header("Location: " . $CFG->base_url($lang . '/' . $URI->uri_string), TRUE, 302);
			exit;
		}
	}
	
	function getAdminLanguage()
	{
		global $CFG;
		return $CFG->item('admin_language');
	}
	
	/**
	 * Get current language code. 
	 * ex: return 'en' if language in CI config is 'english' 
	 * 
	 * Note that if this is called from inside abm it will return admin_language ( and not content's current language ).
	 * In this case, use lang_abm() instead.
	 * 
	 * @global type $CFG
	 * @return string 
	 */
	function lang()
	{
		global $CFG;
		$language = $CFG->item('language');
		
		$lang = array_search($language, $this->languages);
		if ($lang)
		{
			return $lang;
		}
		
		return NULL;	// this should not happen
	}
	
	/**
	 * Internal use. Return content's current language code when in abm
	 * 
	 */
	function lang_abm()
	{
		global $URI;
		return $URI->segment(1);
	}
	
	//check if it's an admin page (added by Pârleboo)
	function is_admin_page()
	{	
		global $URI;
		
		//is a multi-language site
		if(isset($this->languages[ $URI->segment(1) ]))
		{
			if (in_array($URI->segment(2), $this->adminPages))
				return TRUE;
		}
		else if ( in_array($URI->segment(1), $this->adminPages) )
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	function is_special($uri)
	{
		$exploded = explode('/', $uri);
		if (in_array($exploded[0], $this->special))
		{
			return TRUE;
		}
		if(isset($this->languages[$uri]))
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function switch_uri($lang)
	{
		$CI =& get_instance();

		$uri = $CI->uri->uri_string();
		if ($uri != "")
		{
			$exploded = explode('/', $uri);
			if($exploded[0] == $this->lang())
			{
				$exploded[0] = $lang;
			}
			$uri = implode('/',$exploded);
		}
		return $uri;
	}
	
	/**
	 * Nombre del idioma del default language: first element of $this->languages
	 * 
	 * @return type  
	 */
	function default_language()
	{
		return $this->languages[0];
	}
	
	// is there a language segment in this $uri?
	function has_language($uri = null)
	{
		if ($uri == null)
			$uri = uri_string();
		
		$first_segment = NULL;
		
		$exploded = explode('/', $uri);
		
		if(isset($exploded[0]))
		{
			if($exploded[0] != '')
			{
				$first_segment = $exploded[0];
			}
			else if(isset($exploded[1]) && $exploded[1] != '')
			{
				$first_segment = $exploded[1];
			}
		}
		
		if($first_segment != NULL)
		{
			return isset($this->languages[$first_segment]);
		}
		
		return FALSE;
	}
	
	/**
	 * Código de idioma del default language: first element of $this->languages
	 * 
	 * @return type  
	 */
	function default_lang()
	{
		if ( $this->languages == null )
			return null;
		
		foreach ($this->languages as $lang => $language)
		{
			return $lang;
		}
	}
	
	// add language segment to $uri (if appropriate)
	function localized($uri)
	{
		if ( !count($this->languages) )
			return $uri;
		
		if ( $this->is_admin_page() && !$this->has_language() )
			return $uri;
		
		if($this->has_language($uri)
				|| $this->is_special($uri)
				|| preg_match('/(.+)\.[a-zA-Z0-9]{2,4}$/', $uri))
		{
			// we don't need a language segment because:
			// - there's already one or
			// - it's a special uri (set in $special) or
			// - that's a link to a file
		}
		else
		{
			$CI =& get_instance();
			
			if ($this->has_language())
				$uri = $CI->uri->segment(1) . '/' . $uri;
			else
				$uri = $this->default_lang() . '/' . $uri;
		}
		
		return $uri;
	}
	
	public function has_multiple_languages()
	{
		return count($this->languages) > 1;
	}
	
	/**
	 * Devuelve un array con los idiomas que existen en el formato [ 'abreviatura' => 'nombreDelIdioma'  ] (ej: [ 'es' => 'español' ])
	 * 
	 * @return type 
	 */
	public function getLanguages()
	{
		return $this->languages;
	}
	
	/**
	 * Devuelve un array con un array con las abreviaturas de idiomas que existen
	 * 
	 * @return type 
	 */
	public function getLanguagesCodes()
	{
		return array_keys($this->languages);
	}
}

/* End of file */
