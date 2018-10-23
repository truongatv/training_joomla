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

//NOTE: Import joomla controller library
jimport('joomla.application.component.controller');

if(!defined('MAGICTOOLBOX_LEGACY_CONTROLLER_DEFINED')) {
    define('MAGICTOOLBOX_LEGACY_CONTROLLER_DEFINED', true);
    if(JVERSION_256) {
        class MagicToolboxLegacyController extends JControllerLegacy {}
    } else {
        class MagicToolboxLegacyController extends JController {}
    }
}

class Virtuemart_MagicslideshowController extends MagicToolboxLegacyController {

    public function display($cachable = false, $urlparams = false) {
        JRequest::setVar('view', JRequest::getCmd('view', 'default'));
        parent::display($cachable, $urlparams);
        return $this;
    }

    public function install() {

        $app = JFactory::getApplication();
        $database = JFactory::getDBO();

        $pluginPackageDir = dirname(__FILE__).DS.'virtuemart_plugin';

        //NOTE: to fix URL's in css files
        $this->fixCSS();

        jimport('joomla.installer.installer');

        if(!JVERSION_16) {
            //NOTE: it is important, that XML file name matches module name. Otherwise, Joomla wouldn't show parameters and additional info stored in XML.
            copy($pluginPackageDir.DS.'vmmagicslideshow_j15.xml', $pluginPackageDir.DS.'vmmagicslideshow.xml');
        }

        $installer = new JInstaller();//JInstaller::getInstance();
        $installer->setOverwrite(true);
        if($installer->install($pluginPackageDir)) {
            $app->enqueueMessage(JText::_('COM_VIRTUEMART_MAGICSLIDESHOW_INSTALL_PLUGIN_SUCCESS'), 'message');
            //NOTE: enable plugin
            if(JVERSION_16) {
                $query = "UPDATE `#__extensions` SET `enabled`=1 WHERE `name`='plg_system_vmmagicslideshow'";
            } else {
                $title = JText::_('COM_VIRTUEMART_MAGICSLIDESHOW_PLUGIN_TITLE');
                $query = "UPDATE `#__plugins` SET `published`=1, `name`='{$title}' WHERE `name`='plg_system_vmmagicslideshow'";
            }
            $database->setQuery($query);
            if(!$database->query()) {
                $app->enqueueMessage(JText::_($database->getErrorMsg()), 'error');
            }

        } else {
            $app->enqueueMessage(JText::_('COM_VIRTUEMART_MAGICSLIDESHOW_INSTALL_PLUGIN_ERROR'), 'error');
        }

        $this->setRedirect(JRoute::_('index.php?option=com_virtuemart_magicslideshow', false));

        return $this;

    }

    public function fixCSS() {

        //NOTE: to fix URL's in css files

        $path = dirname(__FILE__).DS.'virtuemart_plugin'.DS.'media';
        $list = glob($path.'/*');
        $files = array();
        if(is_array($list)) {
            for($i = 0; $i < count($list); $i++) {
                if(is_dir($list[$i])) {
                    if(!in_array(basename($list[$i]), array('.svn', '.git'))) {
                        $add = glob($list[$i].'/*');
                        if(is_array($add)) {
                            $list = array_merge($list, $add);
                        }
                    }
                } else if(preg_match('#\.css$#i', $list[$i])) {
                    $files[] = $list[$i];
                }
            }
        }

        foreach($files as $file) {
            if(!is_writable($file)) {
                continue;
            }
            $cssPath = dirname($file);
            $cssRelPath = str_replace($path, '', $cssPath);
            $toolPath = JURI::root(true).'/media/plg_system_vmmagicslideshow'.$cssRelPath;
            $pattern = '#url\(\s*(\'|")?(?!data:|mhtml:|http(?:s)?:|/)([^\)\s\'"]+?)(?(1)\1)\s*\)#is';
            $replace = 'url($1'.$toolPath.'/$2$1)';
            $fileContents = file_get_contents($file);
            $fixedFileContents = preg_replace($pattern, $replace, $fileContents);
            //preg_match_all($pattern, $fileContents, $matches, PREG_SET_ORDER);
            //debug_log($matches);
            if($fixedFileContents != $fileContents) {
                $fp = fopen($file, 'w+');
                if($fp) {
                    fwrite($fp, $fixedFileContents);
                    fclose($fp);
                }
            }
        }

    }

    public function apply() {
        $this->saveParamsToDB();
        $this->setMessage(JText::_('COM_VIRTUEMART_MAGICSLIDESHOW_SAVE_TEXT'), 'message');
        $profile = JRequest::getVar('profile', false, 'post');
        $profile = ($profile ? '&profile='.$profile : '');
        $this->setRedirect(JRoute::_('index.php?option=com_virtuemart_magicslideshow'.$profile, false));
        return $this;
    }

    public function save() {
        $this->saveParamsToDB();
        $this->setMessage(JText::_('COM_VIRTUEMART_MAGICSLIDESHOW_SAVE_TEXT'), 'message');
        $this->setRedirect(JRoute::_('index.php?option=com_virtuemart_magicslideshow', false));
        return $this;
    }

    public function cancel() {
        $view = JRequest::getVar('view', false, 'post');
        $profile = JRequest::getVar('profile', false, 'post');
        $id = JRequest::getVar('productId', false, 'post');
        $target = JRequest::getVar('target', false, 'post');
        if($profile) {
            $this->setRedirect(JRoute::_('index.php?option=com_virtuemart_magicslideshow', false));
        } else {
            $this->setRedirect(JRoute::_('index.php', false));
        }
        return $this;
    }

    public function saveParamsToDB() {
        $post = JRequest::get('post');
        $database = JFactory::getDBO();
        $profile = JRequest::getVar('profile', false, 'post');
        if(!empty($post) && !empty($post['config']) && is_array($post['config']) && !empty($profile)) {
            $cases = array();
            $names = array();
            foreach($post['config'] as $name => $value) {
                    //$database->setQuery("UPDATE `#__virtuemart_magicslideshow_config` SET `value`='{$value}', `disabled`='0' WHERE profile='{$profile}' AND name='{$name}'");
                    //$database->query();
                    //$cases[] = "WHEN '{$name}' THEN '{$value}'";
                    $cases[] = "WHEN '{$name}' THEN ".$database->quote($value);
                    $names[] = "'{$name}'";
            }
            $database->setQuery("UPDATE `#__virtuemart_magicslideshow_config` SET `value` = CASE `name` ".implode(' ', $cases)." END WHERE `name` IN (".implode(', ', $names).") AND profile='{$profile}'");
            $database->query();
        }
    }

}
