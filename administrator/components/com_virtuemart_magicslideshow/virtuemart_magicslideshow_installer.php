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

if(!function_exists('com_install')) {
    function com_install() {
        return installMagicslideshowForVirtueMart();
    }
}

if(!function_exists('com_uninstall')) {
    function com_uninstall() {
        return uninstallMagicslideshowForVirtueMart();
    }
}

function installMagicslideshowForVirtueMart() {

    $database = JFactory::getDBO();

    $doCreateTable = true;

    $database->setQuery("SHOW TABLES LIKE '".$database->getPrefix()."virtuemart_magicslideshow_config'");
    $results = $database->loadResult();

    if($results) {
        $doCreateTable = false;
        $database->setQuery("SHOW COLUMNS FROM `#__virtuemart_magicslideshow_config`");
        $results = $database->loadObjectList();
        $fields = '';
        foreach($results as $column) {
            $fields .= $column->Field.',';
        }
        //NOTE: check for old table
        if($fields != 'id,profile,name,value,default,') {
            $doCreateTable = true;
            $database->setQuery("DROP TABLE IF EXISTS `#__virtuemart_magicslideshow_config_bak`;");
            $database->query();
            $database->setQuery("RENAME TABLE `#__virtuemart_magicslideshow_config` TO `#__virtuemart_magicslideshow_config_bak`;");
            $database->query();
        }
    }

    if($doCreateTable) {
        //NOTE: create empty table
        $database->setQuery("
CREATE TABLE IF NOT EXISTS `#__virtuemart_magicslideshow_config` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `profile` VARCHAR(128) NOT NULL DEFAULT '',
    `name` VARCHAR(128) NOT NULL DEFAULT '',
    `value` TEXT,
    `default` TEXT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
        $database->query();
    }

    $database->setQuery("SELECT COUNT(*) as `count` FROM `#__virtuemart_magicslideshow_config` LIMIT 1");
    $results = $database->loadObject();
    if($results->count == 0) {
        $query = <<<SQL
INSERT INTO `#__virtuemart_magicslideshow_config` (`profile`, `name`, `value`, `default`) VALUES
 ('default', 'enable-effect', 'No', 'No'),
 ('default', 'thumb-max-width', '200', '200'),
 ('default', 'thumb-max-height', '200', '200'),
 ('default', 'selector-max-width', '50', '50'),
 ('default', 'selector-max-height', '50', '50'),
 ('default', 'square-images', 'disable', 'disable'),
 ('default', 'width', 'auto', 'auto'),
 ('default', 'height', 'auto', 'auto'),
 ('default', 'orientation', 'horizontal', 'horizontal'),
 ('default', 'arrows', 'No', 'No'),
 ('default', 'loop', 'Yes', 'Yes'),
 ('default', 'effect', 'slide', 'slide'),
 ('default', 'effect-speed', '600', '600'),
 ('default', 'effect-easing', 'ease', 'ease'),
 ('default', 'autoplay', 'Yes', 'Yes'),
 ('default', 'slide-duration', '6000', '6000'),
 ('default', 'shuffle', 'No', 'No'),
 ('default', 'kenburns', 'No', 'No'),
 ('default', 'pause', 'Yes', 'Yes'),
 ('default', 'selectors-style', 'bullets', 'bullets'),
 ('default', 'selectors', 'none', 'none'),
 ('default', 'selectors-eye', 'Yes', 'Yes'),
 ('default', 'bullets-preview', 'top', 'top'),
 ('default', 'selectors-fill', 'No', 'No'),
 ('default', 'caption', 'No', 'No'),
 ('default', 'fullscreen', 'No', 'No'),
 ('default', 'preload', 'Yes', 'Yes'),
 ('default', 'keyboard', 'Yes', 'Yes'),
 ('default', 'show-loader', 'Yes', 'Yes'),
 ('default', 'autostart', 'Yes', 'Yes'),
 ('default', 'link-to-product-page', 'Yes', 'Yes'),
 ('default', 'use-original-vm-thumbnails', 'No', 'No'),
 ('default', 'show-message', 'No', 'No'),
 ('default', 'message', '', ''),
 ('default', 'imagemagick', 'off', 'off'),
 ('default', 'image-quality', '75', '75'),
 ('default', 'watermark', '', ''),
 ('default', 'watermark-max-width', '30%', '30%'),
 ('default', 'watermark-max-height', '30%', '30%'),
 ('default', 'watermark-opacity', '50', '50'),
 ('default', 'watermark-position', 'center', 'center'),
 ('default', 'watermark-offset-x', '0', '0'),
 ('default', 'watermark-offset-y', '0', '0'),
 ('details', 'enable-effect', 'Yes', 'Yes'),
 ('details', 'thumb-max-width', '200', '200'),
 ('details', 'thumb-max-height', '200', '200'),
 ('details', 'selector-max-width', '50', '50'),
 ('details', 'selector-max-height', '50', '50'),
 ('details', 'square-images', 'disable', 'disable'),
 ('details', 'width', '200', '200'),
 ('details', 'height', 'auto', 'auto'),
 ('details', 'orientation', 'horizontal', 'horizontal'),
 ('details', 'arrows', 'Yes', 'Yes'),
 ('details', 'loop', 'Yes', 'Yes'),
 ('details', 'effect', 'slide', 'slide'),
 ('details', 'effect-speed', '600', '600'),
 ('details', 'effect-easing', 'ease', 'ease'),
 ('details', 'autoplay', 'Yes', 'Yes'),
 ('details', 'slide-duration', '6000', '6000'),
 ('details', 'shuffle', 'No', 'No'),
 ('details', 'kenburns', 'No', 'No'),
 ('details', 'pause', 'Yes', 'Yes'),
 ('details', 'selectors-style', 'bullets', 'bullets'),
 ('details', 'selectors', 'none', 'none'),
 ('details', 'selectors-eye', 'Yes', 'Yes'),
 ('details', 'bullets-preview', 'top', 'top'),
 ('details', 'selectors-fill', 'No', 'No'),
 ('details', 'caption', 'No', 'No'),
 ('details', 'fullscreen', 'Yes', 'Yes'),
 ('details', 'preload', 'Yes', 'Yes'),
 ('details', 'keyboard', 'Yes', 'Yes'),
 ('details', 'show-loader', 'Yes', 'Yes'),
 ('details', 'autostart', 'Yes', 'Yes'),
 ('details', 'use-original-vm-thumbnails', 'No', 'No'),
 ('details', 'show-message', 'No', 'No'),
 ('details', 'message', '', ''),
 ('details', 'imagemagick', 'off', 'off'),
 ('details', 'image-quality', '75', '75'),
 ('details', 'watermark', '', ''),
 ('details', 'watermark-max-width', '30%', '30%'),
 ('details', 'watermark-max-height', '30%', '30%'),
 ('details', 'watermark-opacity', '50', '50'),
 ('details', 'watermark-position', 'center', 'center'),
 ('details', 'watermark-offset-x', '0', '0'),
 ('details', 'watermark-offset-y', '0', '0'),
 ('default', 'version', '4.9.6', '4.9.6');
SQL;
        $database->setQuery($query);
        if(!$database->query()) {
            return JError::raiseWarning(500, $database->getError());
        }
    }

    $url = 'index.php?option=com_virtuemart_magicslideshow&task=install';
?>
<style>
.magictoolbox-message-container h1 {
    color: #468847;
}
.magictoolbox-message-container {
    color: #468847;   
    background-color: #DFF0D8;
    border: 1px solid #D6E9C6;
    border-radius: 4px;
    margin-bottom: 18px;
    padding: 8px 35px 8px 14px;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
}
</style>
<div class="magictoolbox-message-container">
<h1>Please wait...</h1>
<h2>The plugin will be installed automatically...</h2>
<h2>Please click <a href="<?php echo $url; ?>" style="color: black;">here</a> if you are not automatically redirected within <span id="redirect_timer">3</span> seconds</h2>
<script language="javascript" type="text/javascript">
var intervalCounter = 3;
var intervalID = setInterval(function() {
    if(intervalCounter) {
        intervalCounter--;
        document.getElementById('redirect_timer').innerHTML = intervalCounter;
    }
    if(!intervalCounter) {
        clearInterval(intervalID);
        document.location.href = '<?php echo $url; ?>';
    }
}, 1000);
</script>
</div>
<?php
    sendVirtueMartMagicslideshowModuleStat('install');
    return true;
}

function uninstallMagicslideshowForVirtueMart() {

    if(version_compare(JVERSION, '1.6.0', '<')) {
        //NOTE: need to load lang file for uninstall string
        $lang = JFactory::getLanguage();
        $lang->load('com_virtuemart_magicslideshow', JPATH_ADMINISTRATOR, null, false);
    }

    $database = JFactory::getDBO();

    //NOTE: uninstall plugin
    if(version_compare(JVERSION, '1.6.0', '<')) {
        $query = "DELETE FROM `#__plugins` WHERE element='vmmagicslideshow'";
    } else {
        $query = "DELETE FROM `#__extensions` WHERE element='vmmagicslideshow'";
    }
    $database->setQuery($query);
    $database->query();

    if(is_file(JPATH_SITE.DS.'administrator'.DS.'language'.DS.'en-GB'.DS.'en-GB.plg_system_vmmagicslideshow.ini')) {
        JFile::delete(JPATH_SITE.DS.'administrator'.DS.'language'.DS.'en-GB'.DS.'en-GB.plg_system_vmmagicslideshow.ini');
    }
    if(is_file(JPATH_SITE.DS.'administrator'.DS.'language'.DS.'en-GB'.DS.'en-GB.plg_system_vmmagicslideshow.sys.ini')) {
        JFile::delete(JPATH_SITE.DS.'administrator'.DS.'language'.DS.'en-GB'.DS.'en-GB.plg_system_vmmagicslideshow.sys.ini');
    }
    if(is_dir(JPATH_SITE.DS.'media'.DS.'plg_system_vmmagicslideshow')) {
        JFolder::delete(JPATH_SITE.DS.'media'.DS.'plg_system_vmmagicslideshow');
    }
    if(version_compare(JVERSION, '1.6.0', '<')) {
        if(is_dir(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow')) {
            JFolder::delete(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow');
        }
        if(is_file(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow.php')) {
            JFile::delete(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow.php');
        }
        if(is_file(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow.xml')) {
            JFile::delete(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow.xml');
        }
    } else {
        if(is_dir(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow')) {
            JFolder::delete(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'vmmagicslideshow');
        }
    }


    echo '<div style="background-color: #C3D2E5;">
          <p style="color: #0055BB;font-weight: bold;">'.JText::_('COM_VIRTUEMART_MAGICSLIDESHOW_UNINSTALL_TEXT').'</p>
          </div>';

    sendVirtueMartMagicslideshowModuleStat('uninstall');
    return true;

}

class com_virtuemart_magicslideshowInstallerScript {

    function preflight($type, $parent) {
        return true;
    }

    function install($parent) {
        return installMagicslideshowForVirtueMart();
    }

    function update($parent) {
        return installMagicslideshowForVirtueMart();
    }

    function uninstall($parent) {
        return uninstallMagicslideshowForVirtueMart();
    }

    function postflight($type, $parent) {
        return true;
    }

}

function sendVirtueMartMagicslideshowModuleStat($action = '') {

    //NOTE: don't send from working copy
    if('working' == 'v4.9.6' || 'working' == 'v3.1.15') {
        return;
    }

    /*

    $hostname = 'www.magictoolbox.com';

    $url = $_SERVER['HTTP_HOST'].JURI::root(true);
    $url = urlencode(urldecode($url));

    $platformVersion = '';
    if(file_exists(JPATH_BASE.DS.'components'.DS.'com_virtuemart'.DS.'version.php')) {
        include JPATH_BASE.DS.'components'.DS.'com_virtuemart'.DS.'version.php';
        if(!isset($shortversion)) {
            $vmVersion = new vmVersion();
            $shortversion = vmVersion::$shortversion;
        }
        $platformVersion = preg_replace('/^[a-zA-Z]+\s+(\d+(?:\.\d+)*).*?$/is', '$1', $shortversion);
    }

    $path = "api/stat/?action={$action}&tool_name=magicslideshow&license=trial&tool_version=v3.1.15&module_version=v4.9.6&platform_name=virtuemart15&platform_version={$platformVersion}&url={$url}";

    $handle = @fsockopen('ssl://' . $hostname, 443, $errno, $errstr, 30);
    if($handle) {
        $headers = "GET /{$path} HTTP/1.1\r\n";
        $headers .= "Host: {$hostname}\r\n";
        $headers .= "Connection: Close\r\n\r\n";
        fwrite($handle, $headers);
        fclose($handle);
    }

    */

}
