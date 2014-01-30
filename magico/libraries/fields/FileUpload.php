<?php 

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FileUpload extends Field {
	
	const FILE_TABLE = 'files';
	const THUMB_TABLE = 'files_thumbs';
	
	public $allowedExtensions = array();
	public $sizeLimit = 8388608; //max file size in bytes ( 8Mb )
	public $maxFilesAllowed = 0; // 0 = no limit
	public $preUploadedFiles = array(); //para los ABM
	public $flag = 0; //Los archivos pueden tener flags, por ejemplo para distinta categoria de archivos (imagenPrincipal, imagenesSecundarias, etc)
	public $hasDescription = false; //Si tienen descripción, label para la descripción
	public $dimensionesRecomendadas = ''; //Si se le agrega esto aparece al lado del help text
	
	function __construct($flag = 0, $hasDescription = false, $label = null, $helptext = 'También podés arrastrar los archivos desde tu computadora.') { 
		parent::__construct($label, $helptext);
		
		$this->autoSave = true; //Implemento save(), delete() y setFieldValue()
		$this->flag = $flag;
		$this->helptext = $helptext;
		$this->hasDescription = $hasDescription;
	}
	
	/**
	 * Solo se podrán subir imágenes 
	 */
	function isImage()
	{
		$this->allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
	}
	
	function render()
	{
		$data = array();
		
		$data['name'] = $this->name;
		$data['value'] = $this->value;
		$data['helptext'] = $this->helptext;
		
		if ( $this->dimensionesRecomendadas )
			$data['helptext'] .= ' Dimensiones recomendadas ' . $this->dimensionesRecomendadas . '.';
		
		$data['type'] = get_class($this->getParent());
		$data['maxFilesAllowed'] = $this->maxFilesAllowed;
		$data['preUploadedFiles'] = $this->preUploadedFiles;
		$data['allowedExtensions'] = $this->allowedExtensions;
		$data['hasDescription'] = $this->hasDescription;
		
		parent::render($data);
	}
	
	/** Este campo se graba por si solo (autoSave = true) **/
	function save($table, $id)
	{
		if ( $_POST['files'] )
		{
			foreach ( $_POST['files'] as $key => $fileId )
			{
				$ci =& get_instance();
				
				$saveFields = array('table' => $table, 'node_id' => $id, 'weight' => $key);
				
                if ( $this->hasDescription )
                    $saveFields['description'] = $_POST['files_description'][$key]; 
                
				if ( !$this->isLanguageAgnostic() )
					$saveFields['language'] = $ci->lang->lang_abm();
				
				$ci->db->where('id', $fileId);
				$ci->db->update(self::FILE_TABLE, $saveFields);
			}
		}
	}
	
	function delete($table, $id)
	{
		$ci =& get_instance();
		
		$where = array('node_id' => $id, 'table' => $table);
		
		if ( !$this->isLanguageAgnostic() )
			$where['language'] = $ci->lang->lang_abm();
		
		$arrFiles = $ci->db->get_where(self::FILE_TABLE, $where)->result_array();
		
		foreach ($arrFiles as $file)
			$this->deleteFile ($file['id']);
	}
	
	function setFieldValue($table, $id, $row)
	{
		$ci =& get_instance();
		
		$ci->db->order_by('weight');
		$where = array(
			'node_id' => $id,
			'table' => $table,
			'flag' => $this->flag
		);
		
		if ( !$this->isLanguageAgnostic() )
		{
			$where['language'] = $ci->lang->lang_abm();
		}
		
		$query = $ci->db->get_where('files', $where);
			
		foreach ( $query->result_array() as $file)
		{
			$this->preUploadedFiles[] = json_encode(array('id' => $file['id'], 
                                                          'filename' => $file['filename'], 
                                                          'description' => $file['description'],
                                                          'thumb' => $this->getThumb($file['filename']) ));
		}
	}
	
	function ajaxCallBack()
	{
		if ( !isset($_GET['remove']) && !isset($_GET['order']) )
		{
			$uploader = new qqFileUploader($this->allowedExtensions, $this->sizeLimit);
			$result = $uploader->handleUpload(UPLOAD_DIR);
		
			//para poder borrarlo
			if ( $result['success'] ) {
				$ci =& get_instance();
				$ci->db->insert('files', array('filename' => $result['filename'], 'flag' => $this->flag));
				$result['id'] = $ci->db->insert_id();
				
				$result['thumb'] = $this->getThumb($result['filename']);
				unset($result['filename']); //uso privado
			}
			
			// to pass data through iframe you will need to encode all html tags
			echo htmlspecialchars(json_encode($result), ENT_NOQUOTES );
		}
		else if ( isset($_GET['remove']) )
		{
			$this->deleteFile($_GET['remove']);
		}
		else if ( isset($_GET['order']) )
		{
			$this->orderFiles($_GET['ids']);
		}
	}
	
	private function getThumb($filename)
	{
		$fileExtension = substr( $filename, strrpos($filename, '.') + 1 );
        $fileExtension = strtolower($fileExtension);
		
		if (array_search( $fileExtension, array('jpg', 'jpeg', 'png', 'gif') ) !== false )
			return magico_thumb($filename, 40, 40);
		else if (array_search( $fileExtension, array('xls', 'xlsx') ) !== false )
			return 'images/magico/filetypes/xls.png';
		else if (array_search( $fileExtension, array('doc', 'docx') ) !== false )
			return 'images/magico/filetypes/doc.png';
		else if (array_search( $fileExtension, array('pdf') ) !== false )
			return 'images/magico/filetypes/pdf.png';
		else if (array_search( $fileExtension, array('rar') ) !== false )
			return 'images/magico/filetypes/rar.png';
		else if (array_search( $fileExtension, array('zip') ) !== false )
			return 'images/magico/filetypes/zip.png';
		else
			return 'images/magico/filetypes/other.png';
	}
	
	private function orderFiles($arrIds)
	{
		$ci =& get_instance();
		
		foreach ($arrIds as $key => $id)
		{
			$ci->db->where('id', $id);
			$ci->db->update(self::FILE_TABLE, array('weight' => $key) );
		}
	}
	
	private function deleteFile($id)
	{
		$ci =& get_instance();
		$where = array('id' => $id);
		
		if ( !$this->isLanguageAgnostic() )
			$where['language'] = $ci->lang->lang_abm();
		
		$query = $ci->db->get_where(self::FILE_TABLE, $where);
		$file = $query->row_array();
		
		unlink(UPLOAD_DIR . $file['filename']);
		
		//Elimino los thumbs
		$arrThumbs = $ci->db->get_where(self::THUMB_TABLE, array('idFile' => $file['id']))->result_array();
		
		if ( $arrThumbs )
		{
			foreach($arrThumbs as $thumb)
			{
				unlink(THUMBS_DIR . $thumb['filename']);
				$ci->db->delete(self::THUMB_TABLE, array('id' => $thumb['id']));
			}
		}
		
		$ci->db->delete(self::FILE_TABLE, $where);
	}
}

/******************* Ajax FileUpload Classes ***********************/

class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array('success'=>true, 'filename' => $filename . '.' . $ext);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}
