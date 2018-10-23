<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;


/*?>
<div class="ph-cart-module-box<?php echo $moduleclass_sfx ;?>">xxx
	<div class="dropdown parent g-menu-overlay" data-g-hover-expand="true" style="opacity: 0;">
		<div class="g-menu-parent-indicator" data-toggle="dropdown" data-hover="dropdown" data-delay="500" data-close-others="true" data-g-menuparent="true"><span class="'.PhocacartRenderIcon::getClass('globe').'"></span> <sup class="ph-cart-count-sup phItemCartBoxCount" id="phItemCartBoxCount"><?php echo $cart->getCartCountItems(); ?></sup></div>

		<div class="g-dropdown g-dropdown-right g-fade g-inactive">
			<div id="phItemCartBox" class="ph-item-cart-box phItemCartBox"><?php echo $cart->render(); ?></div>
		</div>
	</div>
</div> */ ?>

<?php 

    // tODO:
	/*
	min-width: 80%
	
	for mobiles
	30 % for large
	
	
	*/
 ?>
<nav class="g-main-nav g-menu-hastouch  ph-main-nav-mod" role="navigation" data-g-hover-expand="true">
    <ul class="g-toplevel">
		<li class="g-menu-item g-menu-item-type-component g-parent g-fullwidth g-menu-item-link-parent ">
            <div class="g-menu-item-container"><span class="g-menu-item-content">
				<span class="<?php echo PhocacartRenderIcon::getClass('shopping-cart') ?>"></span> 
				<sup class="ph-cart-count-sup phItemCartBoxCount" id="phItemCartBoxCount"><?php echo $cart->getCartCountItems(); ?></sup></span>
				<span class="g-menu-parent-indicator" data-g-menuparent=""></span>
			</div>
            <div class="g-dropdown g-inactive g-fade g-dropdown-left ph-dropdown-cart"><div id="phItemCartBox" class="ph-item-cart-box phItemCartBox"><?php echo $cart->render(); ?></div>
			</div>
		</li>
	</ul>
</nav>

<?php 
// Get count of items and get Total (include coupons)
// Both variables can be used e.g. if the cart is hidden and slide up is used
// Add them to DIV ID because they will change per AJAX
//$count = $cart->getCartCountItems(); // <div id="phItemCartBoxCount"><Xphp echo $cart->getCartCountItems(); X></div>
//$total = $cart->getCartTotalItems(); // <div id="phItemCartBoxTotal"><Xphp echo $cart->getCartTotalItems(); X></div>
?>
