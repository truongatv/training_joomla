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
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.filesystem.folder' );

function com_install()
{
	
	if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocainstall'.DS.'helpers'.DS.'phocainstall.php')) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocainstall'.DS.'helpers'.DS.'phocainstall.php');
	} else {
		return JError::raiseError('Error', 'Helper Phoca Install file could not be found in system');
	}
	
	$js = 'var enablePIB = 0;'
	 .'function enablePhocaInstallButton() {'
	 .' if (enablePIB == 0) {'
	 .'   document.forms[\'piform\'].elements[\'pilicensesubmit\'].disabled=false;'
	 .'   enablePIB = 1;'
	 .' } else {'
	 .'   document.forms[\'piform\'].elements[\'pilicensesubmit\'].disabled=true;'
	 .'   enablePIB = 0;'
	 .' }'
	 .'};';
	 
	 $jsClick = 'enablePhocaInstallButton();';
	 
	$bSt = 'style="border: 1px solid #777;padding: 5px; margin: 5px;-moz-border-radius: 5px;-webkit-border-radius: 5px;border-radius: 5px; height: 30px"';
	
	$xmlItems = PhocaInstallHelper::getPhocaXMLItems();

	$instructions = '';
	if (isset($xmlItems['pinstructions']) && $xmlItems['pinstructions'] != '' ) {
		$instructions = $xmlItems['pinstructions'];
	}
	$sql = '';
	if (isset($xmlItems['psql']) && $xmlItems['psql'] != '' ) {
		$sql = $xmlItems['psql'];
	}
	
	$files = '';
	if (isset($xmlItems['pfiles']) && $xmlItems['pfiles'] != '' ) {
		$files = $xmlItems['pfiles'];
	}
	
	?>
	<script type="text/javascript" ><?php echo $js; ?></script>

	<div style="padding:20px;border:1px solid #b36b00;background:#fff">
		<a style="text-decoration:underline" href="http://www.phoca.cz/" target="_blank"><?php
			echo  JHTML::_('image', 'administrator/components/com_phocainstall/assets/images/icon-logo-product.png', 'Phoca.cz');
		?></a>
		<div style="position:relative;float:right;">
			<?php echo  JHTML::_('image', 'administrator/components/com_phocainstall/assets/images/icon-logo-seal.png', 'Phoca.cz');?>
		</div>
		<p>&nbsp;</p>
		<div style="clear:both">&nbsp;</div>
	
		<div><?php echo $instructions ?></div>
		
		
		<h2>File Copy</h2>
		<div><textarea cols="100" rows="10" readonly="true"><?php
		if(!empty($files) && is_array($files)) {
			foreach($files as $key => $value) {
				echo str_replace('installfiles/', '', $value) . "\n";
			}
		}		
		
		?></textarea></div>
		
		<h2>Database Query</h2>
		<div><textarea cols="100" rows="10" readonly="true"><?php echo $sql; ?></textarea></div>
		
		<div style="font-size:small" >
		<h3>Read Me, Terms Of Use</h3>
		<p>Before you click on Install button, please read carefully the following instructions and confirm that you understand them.</p>
		<ul style="color:#cc0000">
			<li>This component is designed to install files listed in <strong>Files Copy Box</strong> (see above) and execuste SQL queries listed in <strong>Database Query Box</strong> (see above). It should be used by advanced users which have basic knowledge about Joomla! framework and SQL.</li>
			
			<li>Files listed in <strong>File Copy Box</strong> (see above) will overwrite existing files in your Joomla! system. Please, check files and folders on your server which can be overwriten. Don't click on Install button in case, the script can overwrite existing files an folders (in case e.g. they are not backed up).</li>
			
			<li>SQL queries listed in <strong>Database Query Box</strong> (see above) will be executed in your Joomla! system. Please, check your database for existing tables or columns which can be overwriten by this query. Don't click on Install button in case, the query can overwrite existing tables or columns and can delete data stored in such tables or columns.</li>
			
			<li>Backup your system (files and database data). Click on Install button after you have backed up your entire system.</li>
			<li>Phoca Install component offers the installation of files and data and can be used by different developers. Please make sure you've downloaded the modified component from a trusted source before you click on Install button.</li>
			<li>Phoca Install component is distributed in the hope that it will be useful, but without any warranty. It is distributed under GPL 2 version.</li>
			<li>Clicking the Install button you will start the installation process at your own risk.</li>
			</ul>
		</div>
	
	
		
		<div style="text-align:center"><center><table border="0" cellpadding="20" cellspacing="20">
			<tr>
				<td align="center" valign="middle" style="tex-align:center"><center>
					<form action="index.php?option=com_phocainstall&controller=phocainstallinstall&task=install" method="post" name="piform" id="piform" >
					<div style="text-align: left">
					<input type="checkbox" name="license_agree" onclick="<?php echo $jsClick;?>" /> <span>I agree to the terms listed above.</span><br /><br />
					<input type="checkbox" name="overwrite_files" /> <span>Overwrite existing files</span><br />
					<input type="checkbox" name="ignore_sql" /> <span>Ignore SQL query</span><br />
					<br /></div>
					<input <?php echo $bSt; ?> type="submit" name="pilicensesubmit" disabled="disabled" value="      Install      " />
					<input type="hidden" name="controller" value="phocainstallinstall" />
					<input type="hidden" name="task" value="install" />
					</form></center>
				</td>
				
				<td align="center" valign="middle style="tex-align="center"><center>
					<form action="index.php?option=com_phocainstall&controller=phocainstallinstall&task=cancel" method="post" name="piformc">
					<div style="text-align: left">
					<br /><br />
					<br />
					<br /><br />
					<br /></div>
					<input <?php echo $bSt; ?> type="submit" name="submit" value="      Cancel      " />
					<input type="hidden" name="controller" value="phocainstallinstall" />
					<input type="hidden" name="task" value="cancel" />
					</form></center>
				</td>
			</tr>
		</table></center></div>
		
		
		<p>&nbsp;</p><p>&nbsp;</p>
		<p>
		<a href="http://www.phoca.cz/phocainstall/" target="_blank">Phoca Install Main Site</a><br />
		<a href="http://www.phoca.cz/documentation/" target="_blank">Phoca Install User Manual</a><br />
		<a href="http://www.phoca.cz/forum/" target="_blank">Phoca Install Forum</a><br />
		</p>
		
		<p>&nbsp;</p>
		<p><center><a style="text-decoration:underline" href="http://www.phoca.cz/" target="_blank">www.phoca.cz</a></center></p>		
<?php	
}
?>