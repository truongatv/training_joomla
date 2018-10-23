<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Theme
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');


class PhocaTheme {
	
	protected $name			= '';
	protected $name_short	= '';
	
	public function __construct() {
		$app 				= JFactory::getApplication();
		$this->name			= $app->getTemplate();
		$this->name_short 	= str_replace('phoca_', '',$this->name);
	}
	
	function getCssPath() {
		return JURI::base(true).'/templates/'.$this->name.'/css/';
	}
	
	function getJsPath() {
		return JURI::base(true).'/templates/'.$this->name.'/js/';
	}
	
	function getOptionPath($item) {
		return JURI::base(true).'/templates/'.$this->name.'/options/'.htmlspecialchars($item).'/';
	}
	
	/*
	 * CSS and JS is stored in Options folder BUT SCSS not because of compiling
	 * and setting in YAML: templates\phoca_premiere\gantry\theme.yaml (only one folder will be set, not a folder for each option/particle)
	 */
	function getOptionScssPath($item = '') {
		return JURI::base(true).'/templates/'.$this->name.'/scss/'.$this->name_short.'/options/'.$item;
	}
	
	function getOptionJsPath($item = '') {
		return JURI::base(true).'/templates/'.$this->name.'/js/options/'.$item;
	}
	
	function getParticleScssPath($item = '') {
		return JURI::base(true).'/templates/'.$this->name.'/scss/'.$this->name_short.'/particles/'.$item;
	}
	
	function getParticleJsPath($item = '') {
		return JURI::base(true).'/templates/'.$this->name.'/js/particles/'.$item;
	}
	
	function getParticleCssPath($item = '') {
		return JURI::base(true).'/templates/'.$this->name.'/css/particles/'.$item;
	}
	
	function getParticleImagePath($item = '') {
		return JURI::base(true).'/templates/'.$this->name.'/images/particles/'.$item;
	}
	
	function getBootstrapPath($item = '') {
		return JURI::base(true).'/templates/'.$this->name.'/bootstrap/'.$item;
	}
	
}
$gantry['phoca_theme'] = new \PhocaTheme();
?>