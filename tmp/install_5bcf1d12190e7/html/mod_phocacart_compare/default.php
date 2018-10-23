<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
/*
?><div class="ph-cart-module-box<?php echo $moduleclass_sfx ;?>">
	<div class="dropdown parent">
		<div class="dropdown-toggle toplevel ph-cart-dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="500" data-close-others="true"><span class="'.PhocacartRenderIcon::getClass('compare') .'"></span> <sup class="ph-cart-count-sup phItemCompareBoxCount" id="phItemCompareBoxCount"><?php echo $compare->getComapareCountItems(); ?></sup></div>

		<div class="dropdown-menu child ph-cart-dropdown">
			<div id="phItemCompareBox" class="ph-compare-module-box phItemCompareBox"><?php echo $compare->renderList(); ?></div>
		</div>
	</div>
</div>
*/ ?>

<nav class="g-main-nav g-menu-hastouch ph-main-nav-mod" role="navigation" data-g-hover-expand="true">
    <ul class="g-toplevel">
		<li class="g-menu-item g-menu-item-type-component g-parent g-fullwidth g-menu-item-link-parent ">
            <div class="g-menu-item-container"><span class="g-menu-item-content">
				<span class="<?php echo PhocacartRenderIcon::getClass('compare') ?>"></span> 
				<sup class="ph-compare-count-sup phItemCompareBoxCount" id="phItemCompareBoxCount"><?php echo $compare->getComapareCountItems(); ?></sup></span>
				<span class="g-menu-parent-indicator" data-g-menuparent=""></span>
			</div>
            <div class="g-dropdown g-inactive g-fade g-dropdown-left ph-dropdown-compare"><div id="phItemCompareBox" class="ph-item-compare-box phItemCompareBox"><?php echo $compare->renderList();  ?></div>
			</div>
		</li>
	</ul>
</nav>

