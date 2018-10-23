<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Theme
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;

$app	= JFactory::getApplication();
$doc	= JFactory::getDocument();
$config = $gantry['config'];
$atoms 	= $config->get('page.head.atoms');

foreach($atoms as $k => $v) {
	
	if (isset($v['type']) && $v['type'] == 'phoca-options') {
		
		$a = $v['attributes'];
		if (isset($a['enabled']) && $a['enabled'] == 1) {
			
			// Specific libraries loaded at start (not possible inside particle twig)
			// templates\phoca_premiere\particles\phoca-options.html.twig
			if (isset($a['phoca_load_bootstrap_full_css']) && $a['phoca_load_bootstrap_full_css'] == 1) {
				$doc->addStyleSheet(JURI::base(true).'/templates/'.$app->getTemplate().'/bootstrap/css/bootstrap.min.css');
			}
			if (isset($a['phoca_load_bootstrap_base_css']) && $a['phoca_load_bootstrap_base_css'] == 1) {
				$doc->addStyleSheet(JURI::base(true).'/templates/'.$app->getTemplate().'/bootstrap/css/bootstrap.base.min.css');
			}
			if (isset($a['phoca_load_bootstrap_glyphicons_css']) && $a['phoca_load_bootstrap_glyphicons_css'] == 1) {
				$doc->addStyleSheet(JURI::base(true).'/templates/'.$app->getTemplate().'/bootstrap/css/bootstrap.glyphicons.min.css');
			}
			if (isset($a['phoca_load_bootstrap_base_js']) && $a['phoca_load_bootstrap_base_js'] == 1) {
				$doc->addScript(JURI::root(true).'/templates/'.$app->getTemplate().'/bootstrap/js/bootstrap.min.js');
			}
		}
		break;
	}
}
?>