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

if(!defined('MAGICTOOLBOX_CONSTANTS_DEFINED')) {
    define('MAGICTOOLBOX_CONSTANTS_DEFINED', true);
    defined('JVERSION_16') or define('JVERSION_16', version_compare(JVERSION, '1.6.0', '>=') ? true : false);
    defined('JVERSION_25') or define('JVERSION_25', version_compare(JVERSION, '2.5.0', '>=') ? true : false);
    defined('JVERSION_256') or define('JVERSION_256', version_compare(JVERSION, '2.5.6', '>=') ? true : false);
    defined('JVERSION_30') or define('JVERSION_30', version_compare(JVERSION, '3.0.0', '>=') ? true : false);
}
