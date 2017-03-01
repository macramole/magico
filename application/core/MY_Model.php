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

	/**
	 * CodeIgniter object
	 * @var type
	 */
	protected $ci;

	/**
	 * Associated table in the database. Note that some models can be associated with more than one table using
	 * fields with $autoSave = true and have overrided corresponding methods. See Field.php for more information
	 *
	 * IT IS MANDATORY TO FILL THIS VARIABLE
	 *
	 * @var string
	 */
	public static $table;

	/**
	 * This is the name that will appear to the enduser
	 *
	 * IT IS MANDATORY TO FILL THIS VARIABLE
	 *
	 * @var string
	 */
	public static $name;

	/**
	 * Should it show up in the navigation/add menu?
	 *
	 * @var string
	 */
	public static $showInNavAdd = true;

	/**
	 * Should it show up in the navigation/configuration menu?
	 *
	 * @var string
	 */
	public static $showInNavConfig = true;

	/**
	 * Where is magico redirecting the user after a successfull add or edit content.
	 * You can use curly braces with placeholders to use fields values. This is pretty neat, use it like:
	 *
	 * contents/{title}
	 *
	 * A clear url will be created with the title of the content. You can also use foreign models here,
	 * just make sure it has the field "title", for example this model has a TextField named title and a
	 * DatabaseSelect named idCategory you can use:
	 *
	 * contents/{idCategory}/{title}
	 *
	 * @var string
	 */
	public static $returnURL = '';

	/**
	 * Set this to true if there is a page in the site that shows this model to users.
	 * It will create a fancy cleanURL and add an icon in the CRUD list to go directly to there
	 *
	 * @var boolean
	 */
	public static $hasPage = true;

	/**
	 * Is this content in different languages ?
	 * If it is an array with the fields that DOES NOT change between languages, like images, or true if no field is kept
	 * Some autoSaving fields don't support this.. yet.
	 * Make sure the table has a field "language" VARCHAR 2 PK
	 *
	 * @var mixed
	*/
	public static $i18n = false;

	/**
	 * Set this to a string if this type of content needs double delete confirmation since it also deletes associated content.
	 * For example, if this is a category and content of this category will be deleted you should set it to something like
	 * "All associated content will be deleted, proceed ?"
	 *
	 * @var mixed (false or string)
	 */
	public static $needsDeleteConfirmation = false;


	/**
	 * If set to true, and this is i18n, the content will be cloned to the other languages. Later the user can translate the
	 * fields for each other language.
	 * It's also possible to set magico_auto_clone_i18n variable inside config_magico.php to true to override this value.
	 *
	 * @var boolean
	 */
	public static $autoCloneI18N = false;

	/**
	* Is it sortable ? Table will have a weight attribute and lists will be sorted
	*/
	protected static $sortable = false;

	/**
	 * Array of Fields. Set this in child class like $this->fields['column_in_database'] = new WhateverField();
	 *
	 * This is the whole point of using Mâgico
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * The id of the current selected row. This is set internally, don't mess with this.
	 *
	 * @var type
	 */
	public $id = null;

	/**
	 * If the content is not available in the current language but the same content exists in other language. (internal use)
	 *
	 * @var type
	 */
	protected $translating = false;

	function __construct($id = null, $language = null) {
		$this->ci =& get_instance();

		$this->setFieldsParent();
		$this->setFieldsDatabaseFields();

		if ( $id ) {
			$this->loadId($id, $language);
		}

		spl_autoload_register(array($this,'_autoIncludeFields'));
	}

	private function _autoIncludeFields($name) {
		@include_once(MAGICO_PATH_LIB . "fields/$name.php");
		$name = strtolower($name);
		@include_once("application/models/$name.php");
	}

	/**
	 * Loads a content of this type
	 *
	 * @param int $id
	 * @param string $language
	 */
	public function loadId($id, $language = null ) {
		if ( $id ) {
			$this->id = $id;
			$this->setFieldsValues($language);
		}
	}
	/**
	 * OVERRIDE THIS FUNCTION IN ORDER TO VALIDATE YOUR MODELS IN CRUD FORM
	 *
	 * Field values will be in $_POST. Should return an array ( 'field_name' => 'error' ) or null if there is no error.
	 *
	 * @return null
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
	 * Sets the parents of the fields.
	 * Is recursive since some fields also has it's fields.
	 *
	 * @param type $context
	 */
	protected function setFieldsParent( &$context = null) {
		if ( $context == null )
			$context =& $this;

		foreach ($context->fields as &$field)
		{
			$field->setParent($context);

			if ( count($field->fields) )
				$this->setFieldsParent( $field );
		}
	}

	/**
	 * Call all fields to set its databaseFields (for CLI use)
	 */
	protected function setFieldsDatabaseFields() {
		foreach ( $this->fields as $field ) {
			$field->setDatabaseFields();
		}
	}

	/**
	 * Sets the value of the fields getting the data from the database in order to show them later in the CRUD form
	 * autoSaving Fields must implement a setFieldValue method.
	 *
	 * @param type $language
	 * @throws Exception
	 */
	function setFieldsValues($language = null) {
		$arrContent = $this->ci->db->get_where(static::$table, array('id' => $this->id))->result_array();

		if ( !count($arrContent) )
			throw new Exception('MY_Model: No such id ' . $this->id);

		if ( static::$i18n )
			$lang = $language ? $language : $this->ci->lang->lang_abm();

		if ( !isset($lang) )
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
	 * Array with Fields names in order to mark them as listable. If they are listables they will show up in the CRUD list.
	 * Use non autoSaving fields or foreignKey fields with title
	 * It is also possible to send the constant FIELD_LISTABLE_ALL
	 *
	 * @param array $arrFields
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

	/**
	 * Return an array of objects of type Field that are listable (internal use)
	 *
	 * @param boolean $onlyNames Only names (strings not objects) will be returned
	 * @return array
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
	 * Array with a list of contents of this model with its listable fields. CRUD list uses this. (internal use)
	 *
	 * @param type $where
	 * @param type $page not implemented yet
	 * @return type
	 */
	function getList($where = null, $page = null)
	{
		$arrFields = $this->getListableFields(true);
		$arrFields[] = 'id';

		//checkeo por foreign keys y agrego nombre de tabla a los fields. El foreign key debe tener un field title.
		foreach ( $arrFields as $key => $field )
		{
			if ( $field != 'id' && $this->fields[$field]->isForeignKey )
			{
				$arrFields[$key] = "{$this->fields[$field]->isForeignKey}.title AS `{$this->fields[$field]->label}`";
				$fieldContentType = $this->fields[$field]->model;

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

	/**
	 * Gets a list of contents of this model with its listable fields in json (internal use)
	 *
	 * @param type $page
	 */
	function getListJSON($page = null) {
		echo json_encode($this->getList($page));
	}

	/**
	 * What type of CRUD operation are we doing ? (internal use)
	 *
	 * @return type
	 */
	function getOperation()
	{
		if ( !$this->id )
			return self::OPERATION_CREATE;
		else
			return self::OPERATION_EDIT;
	}

	/**
	 * Saves POST data to the database. Controller abm.php uses this.
	 *
	 * @return int id of generated content
	 */
	function save()
	{
		$saveFields = array();

		foreach ( $this->fields as &$field )
		{
			if ( !$field->autoSave && !$field->disabled )
			{
				if ( isset($_POST[$field->name]) && $_POST[$field->name] != '' ) {
					$field->value = $_POST[$field->name];

					if ( $field->safeHtml )
						$field->value = htmlentities($field->value, ENT_NOQUOTES , 'UTF-8' );
				} else {
					if ( $field->nullable ) {
						$field->value = NULL;
					} else {
						$field->value = '';
					}
				}

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

		if ( static::$hasPage )
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

	/**
	 * Transforms the string to a cleanURL compatible one
	 *
	 * @param type $string
	 * @return type
	 */
	public static function cleanURL( $string )
	{
		setlocale(LC_ALL, 'en_US.UTF8');

        $clean = html_entity_decode($string);
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", '-', $clean);

        return $clean;
	}

	/**
	 * Saves the generated cleanURL to the clean_urls table in the database.(internal use)
	 * If the content is i18n it updates all
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
	 * Saves the generated cleanURL to the clean_urls table in the database. If the cleanURL exists adds a number.
	 * (internal use)
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

	/**
	 * Gets the cleanURL of current content
	 *
	 * @return string
	 */
	function getCleanUrl() {
		$arrGet = array('table' => static::$table, 'node_id' => $this->id);

		if ( static::$i18n )
			$arrGet['language'] = $this->ci->lang->lang_abm();

		$row = $this->ci->db->get_where(self::CLEAN_URLS_TABLE, $arrGet)->row_array();

		if ( $row )
			return site_url($row['url']);
	}

	/**
	 * Gets the returnURL of current content. If it doesn't $hasPage it builds it.
	 * @return type
	 */
	function getReturnURL()
	{
		if (static::$hasPage)
			return $this->getCleanUrl();
		else
			return $this->buildReturnURL();
	}

	/**
	 * Builds the return url replacing placeholders
	 *
	 * @param boolean $absolute Do you want the absolute or relative url ?
	 * @return String La url
	 */
	function buildReturnURL($absolute = true)
	{
		$returnUrl = static::$returnURL;

		foreach ( $this->fields as $field )
		{
			if ( isset($_POST[$field->name]) && is_array($_POST[$field->name]) ) //sino tira error el urlencode
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

	/**
	 * Deletes the current content
	 *
	 * @return type
	 */
	function delete() {
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
			if ( static::$hasPage )
				$this->ci->db->delete('clean_urls', array('node_id' => $this->id, 'table' => static::$table));
		}
	}

	function isTranslating() {
		return $this->translating;
	}

    /** CLI TOOLS **/

	/**
	 * Create all tables needed for this model to work. Foreign Models tables won't be created. Call modelToDatabase for each.
	 *
	 * This is a recursive function, hence the $context parameter. Don't use this parameter unless you know what you are doing.
	 *
	 * @param boolean $dropFirst Drop the tables first ?
	 * @param Field $context
	 */
	function createTable($dropFirst = true, $context = null) {
		$this->ci->load->dbforge();
		$fields = array();
		$verbose = false;

		if ( is_null($context) ) {
			$context = $this;
		}

		if ( $context == $this && $dropFirst ) {
			$this->dropTable();
		}

		$fields += array('id' => array(
			'type' => 'INT',
			'unsigned' => true,
			'auto_increment' => true
		));

		if ( static::$sortable ) {
			$fields += array('weight' => array(
				'type' => 'INT'
			));
		}

		if ( $context == $this ) {
			foreach ( $context->fields as $field ) {
				if ( is_null($field->table) ) {
					$fields += $field->databaseFields;
				} else {
					$this->createTable($dropFirst, $field);
				}
			}
		} else {
			$fields += $context->databaseFields;
		}

		$this->ci->dbforge->add_field($fields);
		$this->ci->dbforge->add_key('id', true);

		if ( $context == $this ) {
			echo "Creating table: '" . static::$table . "'" . PHP_EOL;
			if ( $verbose ) {
				print_r($fields);
			}

			$this->ci->dbforge->create_table( static::$table );
		} else {
			echo "Creating table: '{$context->table}' of " . get_class($context) . " '{$context->name}'" . PHP_EOL;
			if ( $verbose ) {
				print_r($fields);
			}
			$this->ci->dbforge->create_table( $context->table );
		}
	}

	/**
	 * Drop all the tables associated with this model. Foreign Models tables won't be dropped. Call dropModelTables for each.
	 *
	 * This is a recursive function, hence the $context parameter. Don't use this parameter unless you know what you are doing.
	 *
	 * @param Field $context
	 */
	function dropTable($context = null) {
		if ( is_null($context) ) {
			$context = $this;
		}

		foreach ( $context->fields as $field ) {
			if ( !is_null($field->table) ) {
				$this->dropTable($field);
			}
		}

		$this->ci->load->dbforge();

		if ( $context == $this ) {
			echo "Dropping table: '" . static::$table . "'" . PHP_EOL;
			$this->ci->dbforge->drop_table( static::$table );
		} else {
			echo "Dropping table: '" . $context->table . "'" . PHP_EOL;
			$this->ci->dbforge->drop_table( $context->table );
		}
	}

    /** STATIC METHODS (work in progress) **/

    /**
     * Get a row from the database as an array by id
     *
     * @param int $id
     * @param int $image_width
     * @param int $image_height
     * @param mixed $language
     * @return array
     */
    static function getRowArray($id, $image_width = null, $image_height = null, $image_flag = 0, $language = MAGICO_AUTO) {
        $ci =& get_instance();
        $table = static::$table;

        if ( $language == MAGICO_AUTO ) {
            if ( $ci->lang->has_language() )
                $language = $ci->lang->lang();
            else
                $language = null;
        }

        $imagen = $image_width ? "( SELECT filename FROM files f WHERE f.node_id = t.id AND f.table = '$table' AND f.flag='$image_flag' ORDER BY f.weight ASC, f.id DESC LIMIT 1 ) AS imagen," : null;

        $sql = "
            SELECT
                t.*,
                $imagen
                cu.url
            FROM
                $table t
            LEFT JOIN
                clean_urls cu ON
                cu.node_id = t.id AND cu.table = '$table'
        ";

        $where = " WHERE t.id = ?";

        if ( $language ) {
            $sql .= " AND cu.language='$language' ";
            $where .= " AND t.language='$language'";
        }

        $sql .= $where;

        $rowReturn = $ci->db->query($sql, array($id))->row_array();

        if ( $imagen )
            $rowReturn['imagen'] = magico_thumb ($rowReturn['imagen'], $image_width, $image_height);

        if ( $rowReturn['url'] )
            $rowReturn['url'] = site_url($rowReturn['url']);

        return $rowReturn;
    }

	/**
	 * Returns an array with rows from the database. It will have an extra field "url" with it's clean url if exists or null otherwise
	 *
	 * If argument "image_width" and/or "image_height" is provided it will also return a field "imagen" with the url of the image with flag "image_flag"
	 * with provided dimensions using configured cropping method (see config/magico_config.php)
	 *
	 * @param int $image_width null or required image width
	 * @param int $image_height null or required image height
	 * @param int $image_flag file flag
	 * @param string $where null or where clause (without WHERE keyword). Use "t." to refer to a field of the table, for example: t.id
	 * @param string $order_by null or order_by clause (without ORDER BY keyword). Use "t." to refer to a field of the table, for example: t.id
	 * @param string $limit null or limit clause (without LIMIT keyword)
	 * @param string $language language or MAGICO_AUTO will autodetect current language (or if there is no language)
	 * @return array
	 */
    static function getListArray($image_width = null, $image_height = null, $image_flag = 0, $where = null, $order_by = null, $limit = null, $language = MAGICO_AUTO)
    {
        $ci =& get_instance();
        $table = static::$table;

        if ( $language == MAGICO_AUTO )
        {
            if ( $ci->lang->has_language() )
                $language = $ci->lang->lang();
            else
                $language = null;
        }

        $imagen = null;
		if ($image_width || $image_height) {
			$imagen =  "( SELECT filename FROM files f WHERE f.node_id = t.id AND f.table = '$table' AND f.flag='$image_flag' ORDER BY f.weight ASC, f.id DESC LIMIT 1 ) AS imagen,";
		}

        $sql = "
            SELECT
                t.*,
                $imagen
                cu.url
            FROM
                $table t
            LEFT JOIN
                clean_urls cu ON
                cu.node_id = t.id AND cu.table = '$table'
        ";

        if ( $language )
        {
            $sql .= " AND cu.language='$language' ";

            if ( $where )
                $where .= " AND t.language='$language'";
            else
                $where = "t.language='$language'";
        }

        if ( $where )
            $sql .= "WHERE $where ";

        if ( $order_by )
            $sql .= " ORDER BY $order_by ";
		elseif ( static::$sortable ) {
			$sql .= " ORDER BY t.weight ASC ";
		}

        if ( $limit )
            $sql .= " LIMIT $limit ";

        $arrReturn = $ci->db->query($sql)->result_array();

        foreach ( $arrReturn as &$item )
        {
            if ( $imagen )
                $item['imagen'] = magico_thumb ($item['imagen'], $image_width, $image_height);

            if ( $item['url'] )
                $item['url'] = site_url($item['url']);
        }

        return $arrReturn;
    }

	/**
	 * Saves to database a row using the array provided as parameter
	 * The format of this array should be:
	 *
	 * array( 'fieldName' => 'value', 'anotherField' => 'value' )
	 *
	 * All fields must be in the model's table, otherwise a MySQL error will araise.
	 *
	 * Returns the id of the new row created.
	 *
	 * @param array $arrValues
	 * @return int
	 */
	static function saveFromArray($arrValues) {
		$ci =& get_instance();
		$ci->db->insert( static::$table, $arrValues );
		return $ci->db->insert_id();
	}

}

/* End of file */
