<?php

/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Un field para linkear a videos. Si el video no existe tira error inválido (VALIDAR!)
 * 
 * La tabla debe tener el field video VARCHAR y videoDuration INT 
 */
class VideoField extends Textbox {
	
	public $autoSave = true; 
	private $stubs = array( 
		'YouTube' => '(http|https)://(?:video\.google\.(?:com|com\.au|co\.uk|de|es|fr|it|nl|pl|ca|cn)/(?:[^"]*?))?(?:(?:www|au|br|ca|es|fr|de|hk|ie|in|il|it|jp|kr|mx|nl|nz|pl|ru|tw|uk)\.)?youtube\.com(?:[^"]*?)?(?:&|&amp;|/|\?|;|\%3F|\%2F)(?:video_id=|v(?:/|=|\%3D|\%2F))([0-9a-z-_]{11})',
		'Vimeo' => '(http|https)://(?:www\.)?vimeo\.com/([0-9]{1,12})');
	private $xmlData = array (
		'YouTube' => 'http://gdata.youtube.com/feeds/api/videos/',
		'Vimeo' => 'http://vimeo.com/api/oembed.xml?url='
	);
	
	function __construct($label = null, $helptext = '', $defaultValue = '') {
		$helptext = 'Link a un video de YouTube o Vimeo. ' . $helptext;
		parent::__construct($label, $helptext, $defaultValue);
	}
	
	function save($table, $id) {
		$ci =& get_instance();
		
		if ( $_POST[$this->name] )
		{
			$videoDuration = $this->getVideoDuration($_POST[$this->name]);

			$ci->db->where('id', $id);
			$ci->db->update($table, array('video' => $_POST[$this->name], 'videoDuration' => $this->getVideoDuration($_POST[$this->name])));
		}
		else
		{
			$ci->db->where('id', $id);
			$ci->db->update($table, array('video' => '', 'videoDuration' => ''));
		}
	}
	
	function delete($table, $id) {
		return;
	}
	
	function setFieldValue($table, $id, $row) {
		$this->value = $row['video'];
	}
	
	private function getVideoDuration($url)
	{
		foreach ( $this->stubs as $stubName => $stub )
		{
			preg_match('~'.$stub.'~imu', $url, $match);
			
			if ( $match[2] )
			{	
				$provider = $stubName;
				$videoId = $match[2];
			}
		}
		
		if ( $videoId )
		{
			switch ($provider) {
				case 'YouTube':
					$xml = @file_get_contents($this->xmlData[$provider] . $videoId);
					
					if ( !$xml )
						return false;
					
					$yt = 'http://gdata.youtube.com/schemas/2007';
					
					$doc = new DOMDocument;
					$doc->loadXML($xml);
					
					$seconds = $doc->getElementsByTagNameNS($yt, 'duration')->item(0)->getAttribute('seconds');
					break;

				case 'Vimeo':
					$xml = @file_get_contents($this->xmlData[$provider] . $url);
					
					if ( !$xml )
						return false;
					
					$doc = new DOMDocument;
					$doc->loadXML($xml);
					$seconds = $doc->getElementsByTagName('duration')->item(0)->nodeValue;
					
					
				default:
					break;
			}
			
			return $seconds;
			
		}
		else
			return false;
	}
	
	public function validate($rules = 'valid')
	{	
		if ( !$_POST[$this->name] )
			return;
		
		if ( $this->getVideoDuration($_POST[$this->name]) )
			return;
		
		$ci =& get_instance();
		$ci->form_validation->set_error( lang('valid_url'), $this->name );
	}
}

?>
