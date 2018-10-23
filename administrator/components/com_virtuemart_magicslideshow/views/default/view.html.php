<?php

/*------------------------------------------------------------------------
# com_virtuemart_magicslideshow - Magic Slideshow for Joomla with VirtueMart
# ------------------------------------------------------------------------
# Magic Toolbox
# Copyright 2011 MagicToolbox.com. All Rights Reserved.
# @license - http://www.opensource.org/licenses/artistic-license-2.0  Artistic License 2.0 (GPL compatible)
# Website: http://www.magictoolbox.com/magicslideshow/modules/joomla/
# Technical Support: http://www.magictoolbox.com/contact/
/*-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access.');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT.DS.'helpers'.DS.'helper.php';

//NOTE: Import joomla view library
jimport('joomla.application.component.view');

if(!defined('MAGICTOOLBOX_LEGACY_VIEW_DEFINED')) {
    define('MAGICTOOLBOX_LEGACY_VIEW_DEFINED', true);
    if(JVERSION_256) {
        class MagicToolboxLegacyView extends JViewLegacy {}
    } else {
        class MagicToolboxLegacyView extends JView {}
    }
}

class Virtuemart_MagicslideshowViewDefault extends MagicToolboxLegacyView {

    function display($tpl = null) {

        //JRequest::setVar('hidemainmenu', true);

        $this->profile = JRequest::getVar('profile', false, 'get');
        $this->profiles = array('default' => 'Default values', 'details' => 'Product details page');

        $title = JText::_('COM_VIRTUEMART_MAGICSLIDESHOW_MANAGER_SETTINGS');
        if($this->profile) {
            $title .= ' - '.$this->profiles[$this->profile];
        }
        JToolBarHelper::title($title, 'magicslideshow.png');

        if($this->profile) {
            JToolBarHelper::save('save');//Save & Close
            JToolBarHelper::apply('apply');//Save
        }
        JToolBarHelper::cancel('cancel', 'Close');//Close

        require_once(JPATH_COMPONENT.DS.'virtuemart_plugin'.DS.'site'.DS.'vmmagicslideshow'.DS.'classes'.DS.'magicslideshow.module.core.class.php');
        //$classesFolded = JVERSION_16 ? 'vmmagicslideshow'.DS.'vmmagicslideshow'.DS.'classes' : 'vmmagicslideshow'.DS.'classes';
        //require_once(JPATH_SITE.DS.'plugins'.DS.'system'.DS.$classesFolded.DS.'magicslideshow.module.core.class.php');

        $this->tool = new MagicSlideshowModuleCoreClass();
        $database = JFactory::getDBO();
        $database->setQuery("SELECT `profile`, `name`, `value` FROM `#__virtuemart_magicslideshow_config`");
        $results = $database->loadAssocList();
        if(!empty($results)) {
            foreach($results as $row) {
                $this->tool->params->setValue($row['name'], $row['value'], $row['profile']);
            }
        }

        $this->imageUrl = JURI::root().'media/com_virtuemart_magicslideshow/images/';
        $this->paramsMap = array(
			'default' => array(
				'General' => array(
					'enable-effect',
				),
				'Positioning and Geometry' => array(
					'thumb-max-width',
					'thumb-max-height',
					'selector-max-width',
					'selector-max-height',
					'square-images',
				),
				'Common settings' => array(
					'width',
					'height',
					'orientation',
					'arrows',
					'loop',
					'effect',
					'effect-speed',
					'effect-easing',
				),
				'Autoplay' => array(
					'autoplay',
					'slide-duration',
					'shuffle',
					'kenburns',
					'pause',
				),
				'Selectors' => array(
					'selectors-style',
					'selectors',
					'selectors-eye',
					'bullets-preview',
					'selectors-fill',
				),
				'Caption' => array(
					'caption',
				),
				'Other settings' => array(
					'fullscreen',
					'preload',
					'keyboard',
					'show-loader',
					'autostart',
				),
				'Miscellaneous' => array(
					'link-to-product-page',
					'use-original-vm-thumbnails',
					'show-message',
					'message',
					'imagemagick',
					'image-quality',
				),
				'Watermark' => array(
					'watermark',
					'watermark-max-width',
					'watermark-max-height',
					'watermark-opacity',
					'watermark-position',
					'watermark-offset-x',
					'watermark-offset-y',
				),
			),
			'details' => array(
				'General' => array(
					'enable-effect',
				),
				'Positioning and Geometry' => array(
					'thumb-max-width',
					'thumb-max-height',
					'selector-max-width',
					'selector-max-height',
					'square-images',
				),
				'Common settings' => array(
					'width',
					'height',
					'orientation',
					'arrows',
					'loop',
					'effect',
					'effect-speed',
					'effect-easing',
				),
				'Autoplay' => array(
					'autoplay',
					'slide-duration',
					'shuffle',
					'kenburns',
					'pause',
				),
				'Selectors' => array(
					'selectors-style',
					'selectors',
					'selectors-eye',
					'bullets-preview',
					'selectors-fill',
				),
				'Caption' => array(
					'caption',
				),
				'Other settings' => array(
					'fullscreen',
					'preload',
					'keyboard',
					'show-loader',
					'autostart',
				),
				'Miscellaneous' => array(
					'use-original-vm-thumbnails',
					'show-message',
					'message',
					'imagemagick',
					'image-quality',
				),
				'Watermark' => array(
					'watermark',
					'watermark-max-width',
					'watermark-max-height',
					'watermark-opacity',
					'watermark-position',
					'watermark-offset-x',
					'watermark-offset-y',
				),
			),
		);
        $this->groups = array();
        foreach($this->paramsMap as $profileId => $groups) {
            foreach($groups as $groupName => $params) {
                if(!isset($this->groups[$groupName])) $this->groups[$groupName] = array();
                $_params = array();
                foreach($params as $param) {
                    $_params[$param] = '';
                }
                $this->groups[$groupName] = array_merge($this->groups[$groupName], $_params);
            }
        }

        if($this->profile) {
            $tpl = 'edit';
        }

        parent::display($tpl);

    }

}
