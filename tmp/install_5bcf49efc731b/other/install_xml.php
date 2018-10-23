<?php
/*********** XML PARAMETERS AND VALUES ************/
$xml_item = "component";// component | template
$xml_file = "phocatemplate.xml";		
$xml_name = "com_phocatemplate";
$xml_creation_date = "12/03/2011";
$xml_author = "Jan Pavelka (www.phoca.cz)";
$xml_author_email = "";
$xml_author_url = "www.phoca.cz";
$xml_copyright = "Jan Pavelka";
$xml_license = "GNU/GPL";
$xml_version = "1.0.0 Beta";
$xml_description = "Phoca Template";
$xml_copy_file = 1;//Copy other files in to administration area (only for development), ./front, ./language, ./other


$xml_menu = array (0 => "com_phocatemplate", 1 => "option=com_phocatemplate", 2 => "components/com_phocatemplate/assets/images/icon-16-pt-menu.png", 3 => "com_phocatemplate", 4 => 'phocatemplatecp');

$xml_submenu[0] = array (0 => "COM_PHOCATEMPLATE_CONTROLPANEL", 1 => "option=com_phocatemplate", 2 => "components/com_phocatemplate/assets/images/icon-16-pt-menu-cp.png", 3 => "COM_PHOCATEMPLATE_CONTROLPANEL", 4 => 'phocatemplatecp');

$xml_submenu[1] = array (0 => "COM_PHOCATEMPLATE_MENU_ITEMS", 1 => "option=com_phocatemplate&amp;view=phocatemplatemenus", 2 => "components/com_phocatemplate/assets/images/icon-16-pt-menu-menu.png", 3 => "COM_PHOCATEMPLATE_MENU_ITEMS", 4 => 'phocatemplatemenus');

$xml_submenu[2] = array (0 => "COM_PHOCATEMPLATE_COLUMNS", 1 => "option=com_phocatemplate&amp;view=phocatemplatecolumns", 2 => "components/com_phocatemplate/assets/images/icon-16-pt-menu-column.png", 3 => "COM_PHOCATEMPLATE_COLUMNS", 4 => 'phocatemplatecolumns');

$xml_submenu[3] = array (0 => "COM_PHOCATEMPLATE_INFO", 1 => "option=com_phocatemplate&amp;view=phocatemplateinfo", 2 => "components/com_phocatemplate/assets/images/icon-16-pt-menu-info.png", 3 => "COM_PHOCATEMPLATE_INFO", 4 => 'phocatemplateinfo');


$xml_install_file = 'install.phocatemplate.php'; 
$xml_uninstall_file = 'uninstall.phocatemplate.php';
/*********** XML PARAMETERS AND VALUES ************/
?>