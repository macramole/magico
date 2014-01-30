<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model {

	const OPERATION_CREATE = 'create';
	const OPERATION_EDIT = 'edit';

	const FIELD_LISTABLE_ALL = 'all';
	const CLEAN_URLS_TABLE = 'clean_urls';

	const FOREIGNKEY_TYPE_ONE_TO_MANY = 1;
	const FOREIGNKEY_TYPE_MANY_TO_MANY = 2;

	//Objeto de codeigniter
	protected $ci;
	//Array de objetos que extienden de field. Se establece en la clase hija
	public $fields = array();
	/**
		* Si es foreignKey un array con los content fields que dependen de él, al eliminar el contenido, eliminará todo lo asociado. 
		* Tambien es importante si se usa el field ForeignContentType
		* 
		* Caso FOREIGNKEY_TYPE_ONE_TO_MANY El campo foreign debe llamarse id<nombre_de_contentType>
		* El formato es array( 'nombreContentType', 'nombreContentType2', etc )
		* 
		* Ejemplo: Artistas y Obras, el artista debería tener este campo como array('Obra'). Al borrar el artista se borrar sus obras asociadas
		* 
		* Caso FOREIGNKEY_TYPE_MANY_TO_MANY El campo foreign debe llamarse id<nombre_de_contentType>
		* El formato es array( 'nombreContentType' => 'nombreTablaManyToMany', 'nombreContentType2' => 'nombreTablaManyToMany2', etc )
		* Si el contenido quedó sin relaciones se eliminará
		* 
		* Ejemplo: Artistas y Obras pero donde las obras puede estar hechas por muchos artistas. Supongamos que tenemos las tablas
		* 'artistas' 'obras' y 'artistas_obras'. Al borrar, se borrá el registro del artista de artistas_obras
		* 
		* TODO: De momento todos los content_types son de un tipo o de otro, se debería poder definir por cada uno el FOREIGNKEY_TYPE
		*/
	public static $isForeignKey = false;
	public static $foreignKeyType = self::FOREIGNKEY_TYPE_ONE_TO_MANY;

	/**
		* Hay veces que el content type es foreign key pero que el otro contenido puede existir sin este.
		* Mâgico no pedirá confirmación en estos casos
		* 
		* Caso FOREIGNKEY_TYPE_ONE_TO_MANY
		* En vez de eliminar el contenido asociado se pondrá un 0 en el campo id<nombre_de_contentType>
		* 
		* Caso FOREIGNKEY_TYPE_MANY_TO_MANY
		* Si el contenido ha quedado sin relaciones no se eliminará
		* 
		* 
		* 
		* @var array formato es array( 'nombreContentType', 'nombreContentType2', etc ) (los que son soft)
		*/
	public static $isSoftForeignKey = false;

	//Esta es la tabla a la que se refiere este content type (si es más de una se deberá overridear el método save)
	public static $table;
	//Este es el nombre que se mostrará para este tipo de contenido
	public static $name;
	//Donde volverá cuando se agregue ej: document/{id}/{title} (sin barra ni al comienzo ni al final)
	public static $returnURL = '';
	/**
		* Si este contenido está en distintos idiomas. 
		* Si lo está, un array con los campos que no cambian entre idioma o TRUE si ningún campo se mantiene (las imágenes siempre se mantienen)
		* La tabla deberá tener un campo "language" VARCHAR 2 PK
		*/
	public static $i18n = false;
	//Si hay, el listado mostrará una acción extra para ver la página de ese contenido
	public static $hayPaginaIndividual = true;

	/**
		* Al crear un contenido en un idioma se clona el mismo a los demás. El usuario puede mas tarde traducir los campos
		* Si en el config el parámetro magico_auto_clone_i18n es true entonces sobreescribe este seteo (osea es siempre true)
		* 
		* @var boolean
		*/
	public static $autoCloneI18N = false;

	//Si está editando
	public $id = null;

	//Cuantos items por página cuando se lista el contenido desde el backend /TODO
	public $itemsPorPaginaList = 10;
	//Para establecer un mensaje de borrado especial para este content
	public $mensajeBorrado = null;
	
	//Si el contenido no esta disponible en el idioma pero existe el mismo contenido en otro idioma (uso interno)
	protected $translating = false;

	function __construct($id = null, $language = null)
	{
		$this->ci =& get_instance();

		$this->setFieldsParent();

		if ( $id )
		{
			$this->loadId($id, $language);			
		}

		spl_autoload_register(array($this,'_autoIncludeFields'));
	}

	private function _autoIncludeFields($name)
	{
		@include_once(MAGICO_PATH_LIB . "fields/$name.php");
		$name = strtolower($name);
		@include_once("application/models/$name.php");
	}
	
	function loadId($id, $language = null )
	{
		if ( $id )
		{
			$this->id = $id;
			$this->setFieldsValues($language);
		}
	}

	/**
		* A veces es necesario saber el padre del field, asi que lo establezclo automaticamente aqui
		* 
		* Es recursiva para darle el parent a los fields de los fields si es que hay
		*/
	protected function setFieldsParent( &$context = null)
	{
		if ( $context == null )
			$context =& $this;

		foreach ($context->fields as &$field)
		{
			$field->setParent($context);

			if ( $field->fields )
				$this->setFieldsParent( $field );
		}
	}

	function setFieldsValues($language = null)
	{
		$arrContent = $this->ci->db->get_where(static::$table, array('id' => $this->id))->result_array();

		if ( !count($arrContent) )
			throw new Exception('MY_Model: No such id ' . $this->id);

		if ( static::$i18n )
			$lang = $language ? $language : $this->ci->lang->lang_abm();
		
		if ( !$lang )
			$row = $arrContent[0];
		else
		{
			$row = null;

			foreach ( $arrContent as $content )
			{
				if ( $content['language'] == $lang )
				{
					$row = $content;
					break;
				}
			}

			if ( $row == null )
			{
				$row = $arrContent[0];
				$this->translating = true;
			}
		}

		foreach ( $row as $name => $field)
		{
			if ( isset($this->fields[$name]) )
			{
				$this->fields[$name]->value = $field;
			}
		}

		foreach ( $this->fields as $field )
		{
			if ( $field->autoSave )
					$field->setFieldValue(static::$table, $this->id, $row);
		}
	}

	/**
		* Se pasan los nombres de los fields como array y los marca como listables (asi es más rápido).
		* Si se manda un field que es foreign key el listado mostrara el title de la tabla
		* Tambien se peude mandar la constante FIELD_LISTABLE_ALL para todos
		*/
	function setListableFields($arrFields)
	{
		if (is_array($arrFields) )
		{
			foreach ($arrFields as $field)
			{
				$this->fields[$field]->setListable();
			}
		}
		elseif ( $arrFields == self::FIELD_LISTABLE_ALL )
		{
			foreach ( $this->fields as $key => $field )
			{
				$this->fields[$key]->setListable();
			}
		}
	}

	/*
		* Devuelve los fields que son listables. $onlyNames devuelve sólo el nombre.
		*/
	function getListableFields( $onlyNames = false )
	{
		$arrFields = array();

		foreach ( $this->fields as $field )
		{
			if ( $field->isListable() )
			{
				if ($onlyNames)
					$arrFields[] = $field->name;
				else
					$arrFields[] = $field;
			}
		}

		return $arrFields;
	}

	/**
		* Devuelve array con la lista paginada (o null para sin paginar) /TODO
		* 
		* @param array $where
		* @param int $page
		* @return array
		*/
	function getList($where = null, $page = null)
	{
		$arrFields = $this->getListableFields(true);
		$arrFields[] = 'id';

		//checkeo por foreign keys y agrego nombre de tabla a los fields. El foreign key debe tener un field title.
		foreach ( $arrFields as $key => $field )
		{
			if ( $this->fields[$field]->isForeignKey )
			{
				$arrFields[$key] = "{$this->fields[$field]->isForeignKey}.title AS `{$this->fields[$field]->label}`";
				$fieldContentType = $this->fields[$field]->content_type;

				if ( ( $fieldContentType && !$fieldContentType::$i18n ) || !$fieldContentType )
					$joinOn = "{$this->fields[$field]->isForeignKey}.id = " . static::$table . ".$field";
				else
					$joinOn = "{$this->fields[$field]->isForeignKey}.id = " . static::$table . ".$field AND {$this->fields[$field]->isForeignKey}.language = '{$this->ci->uri->segment(1)}'";

				$this->ci->db->join($this->fields[$field]->isForeignKey, $joinOn, 'left');
			}
			else
			{
				$arrFields[$key] = static::$table . ".$field";
			}
		}

		$this->ci->db->select($arrFields);
		$this->ci->db->order_by('id ASC');

		if ( !static::$i18n )
			return $this->ci->db->get_where(static::$table, $where)->result_array();
		else
		{
			$where = array_merge(array(static::$table . ".language" => $this->ci->lang->lang_abm()), $where ? $where : array());
			return $this->ci->db->get_where(static::$table, $where)->result_array();
		}

	}

	function getListJSON($page = null)
	{
		echo json_encode($this->getList($page));
	}

	/**
		* Devuelve el tipo de operación, create o edit
		*/
	function getOperation()
	{
		if ( !$this->id )
			return self::OPERATION_CREATE;
		else
			return self::OPERATION_EDIT;
	}

	/**
		* Guarda los datos por POST en la base de datos. Se llama desde el controller ABM
		* 
		* @return int El id 
		*/
	function save()
	{
		$saveFields = array();

		foreach ( $this->fields as &$field )
		{
			if ( !$field->autoSave && !$field->disabled )
			{
				$field->value = $_POST[$field->name] ? $_POST[$field->name] : '';

				if ( $field->safeHtml )
					$field->value = htmlentities($field->value, ENT_NOQUOTES , 'UTF-8' );

				$saveFields[$field->name] = $field->value;
			}

		}
		
		//I'll save fields shared between languages now (if any)
		if ( static::$i18n )
		{
			$saveFields['language'] = $this->ci->lang->lang_abm();
			
			if ( $this->getOperation() == self::OPERATION_EDIT && is_array(static::$i18n) )
			{
				$arrI18NContent = $this->ci->db->query("SELECT language FROM " . static::$table . " WHERE id = ? AND language <> ? ", array( $this->id, $saveFields['language'] ))->result_array();

				if ( count($arrI18NContent) )
				{
					$i18nSaveFields = array();

					foreach ( static::$i18n as $i18nField )
					{
						if ( !$this->fields[$i18nField]->autoSave )
							$i18nSaveFields[$i18nField] = $saveFields[$i18nField];
					}

					$this->ci->db->where('id', $this->id);
					$this->ci->db->update( static::$table, $i18nSaveFields );
				}
			}

			if ( $this->isTranslating() )
				$saveFields['id'] = $this->id;
		}
			
		
		// Ok now, let's save it for real
		if ( $this->getOperation() == self::OPERATION_CREATE || $this->isTranslating() )
		{
			$this->ci->db->insert(static::$table, $saveFields);

			if ( !$this->isTranslating() )
			{
				$this->id = $this->ci->db->insert_id();

				if ( static::$i18n )
				{
					/**
					 * @todo Autosaving fields don't get cloned, luckly they are usually language agnostic (like images)
					 * and if they are not you will usually don't want them to be cloned
					 */
					
					if ( $this->ci->config->item('magico_auto_clone_i18n') || static::$autoCloneI18N  )
					{
						$actualLanguage = $saveFields['language'];
						$saveFields['id'] = $this->id;
						
						foreach ( $this->ci->lang->getLanguagesCodes() as $language )
						{
							if ( $language != $actualLanguage )
							{
								$saveFields['language'] = $language;
								$this->ci->db->insert(static::$table, $saveFields);
							}
						}
					}
				}

			}

		}
		else
		{
			if ( !static::$i18n )
				$this->ci->db->where('id', $this->id);
			else
				$this->ci->db->where( array( 'id' => $this->id, 'language' => $saveFields['language'] ) );

			$this->ci->db->update(static::$table, $saveFields);
		}
		
		//And now let's save autosaving fields
		foreach ( $this->fields as &$field )
		{
			if ( $field->autoSave && !$field->disabled )
				$field->save(static::$table, $this->id);
		}

		if ( static::$hayPaginaIndividual )
			$this->saveClean();

		return $this->id;
	}
	/**
	 * Saves just one field. It's now being used by CKEDITOR's "editables". $_POST['data'] should have the data.
	 * 
	 * @param string $field Nombre del field
	 */
	public function saveField( $field )
	{
		if ( !static::$i18n )
			$this->ci->db->where('id', $this->id);
		else
			$this->ci->db->where( array( 'id' => $this->id, 'language' => $this->ci->uri->segment(1) ) );

		if ( $this->fields[$field]->safeHtml )
			$data = htmlentities($_POST['data'], ENT_NOQUOTES , 'UTF-8' );
		else
			$data = $_POST['data'];

		$this->ci->db->update(static::$table, array( $field => $data ) );
	}

	public static function cleanURL( $string )
	{
		setlocale(LC_CTYPE, 'es_ES');

		$string = html_entity_decode($string);
		$string = utf8_encode($string);
		$string = iconv("UTF-8", "us-ascii//TRANSLIT", $string); // TRANSLIT does the whole job
		$string = strtolower($string);
		$string = preg_replace('~[^-a-z0-9_ ]+~', '', $string); // keep only letters, numbers, '_', space and separator
		$string = trim($string);
		$string = preg_replace ('~[ ]+~', '-', $string);
		return $string;
	}

	/**
		* Guarda en clean_urls la url del content_type. Si el content es i18n actualiza todos los del mismo idioma
		*/
	function saveClean()
	{	
		$arrDelete = array('table' => static::$table, 'node_id' => $this->id);
		$this->ci->db->delete(self::CLEAN_URLS_TABLE, $arrDelete);

		if ( !static::$i18n ) //Si no es i18n todo tranca se guarda de una
			$this->saveCleanOne();
		else
		{
			//si esta creando y automaticamente se crean los demás, que los cree directo
			if ( $this->getOperation() == self::OPERATION_CREATE && ( $this->ci->config->item('magico_auto_clone_i18n') || static::$autoCloneI18N  ) )
			{
				$this->saveCleanOne( $this->ci->lang->getLanguagesCodes() );
			}
			else //si no habrá que cargar uno por uno y recargarles
			{
				$actualLanguage = $this->ci->lang->lang_abm();
				$this->saveCleanOne( $actualLanguage ); //guardo el actual
				
				foreach ( $this->ci->lang->getLanguagesCodes() as $language )
				{
					if ( $language != $actualLanguage )
					{
						$content = new static($this->id, $language);
						$content->saveCleanOne($language);
					}
				}

			}
		}
	}

	/**
		* Guarda en clean_urls la url del content_type. Si ya existe, le agrega un numero. Uso interno. Usar saveClean si es necesario.
		*  
		* @param mixed $language null | array of strings | string
		*/
	private function saveCleanOne($language = null)
	{
		$urlClean = $this->buildReturnURL(false);
		$num = 0; //si ya existe agregarle un número

		$rowClean = $this->ci->db->get_where(self::CLEAN_URLS_TABLE, array('url' => $urlClean))->row_array();
		$urlCleanNum = '';

		while ( $rowClean && $rowClean['node_id'] != $this->id )
		{
			$urlCleanNum = $urlClean . '-' . $num;
			$rowClean = $this->ci->db->get_where(self::CLEAN_URLS_TABLE, array('url' => $urlCleanNum))->row_array();
			$num++;
		}

		if ( $urlCleanNum == '')
			$urlToAdd = $urlClean;
		else
			$urlToAdd = $urlCleanNum;

		$arrInsert = array('table' => static::$table, 'node_id' => $this->id, 'url' => $urlToAdd);

		if ( $language )
		{
			if ( !is_array($language) )
			{
				$arrInsert['language'] = $language;
				$this->ci->db->insert(self::CLEAN_URLS_TABLE, $arrInsert);
			}
			else
			{
				foreach( $language as $lang )
				{
					$arrInsert['language'] = $lang;
					$this->ci->db->insert(self::CLEAN_URLS_TABLE, $arrInsert);
				}
			}
		}
		else 
			$this->ci->db->insert(self::CLEAN_URLS_TABLE, $arrInsert);
	}

	function getCleanUrl()
	{

		$arrGet = array('table' => static::$table, 'node_id' => $this->id);

		if ( static::$i18n )
			$arrGet['language'] = $this->ci->lang->lang_abm();
		
		$row = $this->ci->db->get_where(self::CLEAN_URLS_TABLE, $arrGet)->row_array();

		if ( $row )
			return site_url($row['url']);
	}

	function getReturnURL()
	{
		if (static::$hayPaginaIndividual)
			return $this->getCleanUrl();
		else
			return $this->buildReturnURL();
	}

	/**
		* Devuelve la URL del contenido
		* 
		* @param boolean $absolute Si devuelve la url absoluta o relativa
		* @return String La url 
		*/
	function buildReturnURL($absolute = true)
	{
		$returnUrl = static::$returnURL;

		foreach ( $this->fields as $field )
		{
			if ( is_array($_POST[$field->name]) ) //sino tira error el urlencode
				continue;

			/*
				* Si tiene foreignKey se fija el clean de ese contenido. Esto sirve para categorias
				*/
			if ( !$field->isForeignKey )
				$returnUrl = str_replace('{' . $field->name . '}', $this->cleanURL($field->value), $returnUrl);
			else
			{
				$fieldContentType = $field->model;

				if ( !$fieldContentType::$i18n )
					$foreignCleanUrl = magico_urlclean($field->isForeignKey, $field->value, null, false);
				else
					$foreignCleanUrl = magico_urlclean($field->isForeignKey, $field->value, $this->ci->uri->segment(1), false);

				$returnUrl = str_replace('{' . $field->name . '}', $foreignCleanUrl, $returnUrl);
			}
		}

		$returnUrl = str_replace('{id}', $this->id, $returnUrl);

		if ( $absolute )
			return site_url($returnUrl);
		else
			return $returnUrl;
	}

	function delete()
	{
		if (!$this->id)
			return;
		else
		{

			if ( !static::$i18n )
			{
				//Primero los campos que se eliminan solos
				foreach ( $this->fields as $field )
				{
					if ( $field->autoSave )
						$field->delete(static::$table, $this->id);
				}

				$this->ci->db->delete(static::$table, array( 'id' => $this->id) );
			}
			else
			{
				//Si borras un contenido traducible se borra en todos los idiomas en conjunto con todas las cosas relacionadas (campos autograbables)

				//Si queda solo este borrar los campos que se autograban (estos no son traducibles de momento)
				foreach ( $this->fields as $field )
				{
					if ( $field->autoSave )
						$field->delete(static::$table, $this->id);
				}

				$this->ci->db->delete(static::$table, array( 'id' => $this->id ) );//, 'language' => $lang) );
			}

			//Si tiene clean url elimino la entrada
			if ( static::$hayPaginaIndividual )
				$this->ci->db->delete('clean_urls', array('node_id' => $this->id, 'table' => static::$table));
		}
	}

	function getRow()
	{
		if (!$this->id)
			return;
		else
		{
			return $this->ci->db->get_where(static::$table, array( 'id' => $this->id ))->row_array();
		}
	}

	/**
		* Tendrá los datos por post, debe devolver un array ( 'field_name' => 'error' ) o null si no hay error
		*/
	function validate()
	{
		if ($this->ci->form_validation->run() == true)
			return null;
		else
		{
			return $this->ci->form_validation->get_error_array();
		}
	}

	/**
		* Devuelve si el contenido está siendo traducido
		* 
		* @return type 
		*/
	function isTranslating()
	{
		return $this->translating;
	}
}

/* End of file */
