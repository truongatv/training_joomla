<?php defined('_JEXEC') or die('Restricted access');?>

<form action="index.php" method="post" name="adminForm">
<div class="adminform">
<div class="cpanel-left">
	<div id="cpanel">
		
		<div style="float:right;margin:10px;font-size:large;background:#FFC2C2;border:1px solid #cc0000;color: #cc0000;margin:15px;padding:15px">
		<p>This component is used only for a single installation of special addons. It is not necessary to have it remain active in the Joomla! system. For security reasons, please <strong>uninstall it</strong> in:</p>
		<p style="text-align:right;font-size:normal"><a style="text-decoration:underline;" href="index.php?option=com_installer">Extensions - Install/Uninstall - Components</a><p>
		</div>
				
		<div style="clear:both">&nbsp;</div>
		<p>&nbsp;</p>
		<div style="text-align:center;padding:0;margin:0;border:0;">
			<iframe style="padding:0;margin:0;border:0" src="http://www.phoca.cz/adv/phocainstall" noresize="noresize" frameborder="0" border="0" cellspacing="0" scrolling="no" width="500" marginwidth="0" marginheight="0" height="125">
			<a href="http://www.phoca.cz/adv/phocainstall" target="_blank">Phoca Install</a>
			</iframe>
		</div>
	</div>
</div>
		
<div class="cpanel-right">
	<div style="border:1px solid #ccc;background:#fff;margin:15px;padding:15px">
		<div style="float:right;margin:10px;">
			<?php echo JHTML::_('image', 'administrator/components/com_phocainstall/assets/images/icon-logo-seal.png', 'Phoca.cz' );?>
		</div>
			
		<?php

		echo '<h3>'.  JText::_('Copyright').'</h3>'
		.'<p>© 2007 - '.  date("Y"). ' Jan Pavelka</p>'
		.'<p><a href="http://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>';

		echo '<h3>'.  JText::_('License').'</h3>'
		.'<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>';
		
		echo '<p>&nbsp;</p>';
		?>
		
	</div>
</div>

<div style="clear:both"></div>

</div>

<input type="hidden" name="option" value="com_phocainstall" />
<input type="hidden" name="view" value="phocainstallcp" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>