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
jimport('joomla.application.component.controller');


$view	= JRequest::getVar( 'view', '', '', 'string', JREQUEST_ALLOWRAW );
if ($view == '' || $view == 'phocainstallcp') {
	JSubMenuHelper::addEntry(JText::_('Phoca Install'), 'index.php?option=com_phocainstall', true);
}

class PhocaInstallCpController extends JController {
	function display() {
		parent::display();
	}
}
?>
