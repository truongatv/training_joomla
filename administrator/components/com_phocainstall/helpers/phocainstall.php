<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

class PhocaInstallHelper
{	
	function getPhocaXMLItems()
	{
		$folder = JPATH_ADMINISTRATOR .DS. 'components'.DS.'com_phocainstall';
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, '.xml$');
		} else {
			$folder = JPATH_SITE .DS. 'components'.DS.'com_phocainstall';
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}
		}

		$data = array();
		if (count($xmlFilesInDir))
		{
			foreach ($xmlFilesInDir as $xmlfile)
			{
				$xml = & JFactory::getXMLParser('Simple');
				if ($xmlfile == 'phocainstall.xml') {
					if (!$xml->loadFile($folder.DS.$xmlfile)) {
						unset($xml);
						return false;
					}
	
					if ( !is_object($xml->document) || ($xml->document->name() != 'install')) {
						unset($xml);
						return false;
					}
					$element = & $xml->document->pinstructions[0];
					$data['pinstructions'] = $element ? $element->data() : '';
					$element = & $xml->document->psql[0];
					$data['psql'] = $element ? $element->data() : '';
					
					$dataFiles = array();
					if(isset($xml->document->administration[0]->files)) {
						foreach ($xml->document->administration[0]->files as $key => $value) {
						
							if(isset($value->filename)) {
								foreach($value->filename as $key2 => $value2) {
							
									if (stripos($value2->data(), 'installfiles/') === 0) {
										$dataFiles[] = $value2->data();
									}
								}
							}
						}
					}
					$data['pfiles'] = $dataFiles;
				}	
			}
		}
		return $data;
	}
}
?>