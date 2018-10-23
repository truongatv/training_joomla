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

//ini_set('display_errors', true );
//error_reporting(E_ALL & ~E_NOTICE);

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

if(!defined('MAGICTOOLBOX_JURI_BASE')) {
    $url = JURI::base(true);//NOTE: without / at the end
    //NOTE: JURI::base() return URI according to $live_site variable in configuration
    //      this leads to problem with wrong protocol prefix (http/https)
    //      so this is a fix
    if(empty($_SERVER['HTTPS']) || (strtolower($_SERVER['HTTPS']) == 'off')) {
        $url = preg_replace('#^https:#i', 'http:', $url);
    } else {
        $url = preg_replace('#^http:#i', 'https:', $url);
    }
    define('MAGICTOOLBOX_JURI_BASE', $url);
}

class PlgSystemVMMagicSlideshow extends JPlugin {

    protected static $instance = null;

    protected $magicslideshowSupportedBlocks = array('default', 'details');

    var $vmVersion = '';
    var $mediaURL = '';
    var $vmPage = '';
    var $bodyContent = '';
    var $contentBuffer = '';
    var $conf = null;

    var $latestProd = '';
    var $featuredProd = '';
    var $randomProd = '';
    
    var $preserveAdditionalThumbnailsPositions= false;
    var $shouldBeReplaced = array('patterns' => array(), 'replacements' => array());
    var $needHeaders = false;
    var $needScroll = false;

    public function __construct(&$subject, $config = array()) {
        parent::__construct($subject, $config);
        if(is_null(self::$instance)) {
            self::$instance = $this;
        }
        //$this->loadLanguage();

        //$vmXML = file_get_contents(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.xml');
        $vmXML = file_get_contents(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.xml');
        $vmVersion = preg_replace('#^.*?<version[^>]*+>([^<]*+)</version>.*#is', '$1', $vmXML);
        $vmVersion = substr($vmVersion, 0, 3);
        $this->vmVersion = $vmVersion;


        $this->mediaURL = MAGICTOOLBOX_JURI_BASE.'/media/plg_system_vmmagicslideshow';

        $this->vmPage = trim(JRequest::getVar('page', ''));
        if(empty($this->vmPage)) $this->vmPage = trim(JRequest::getVar('page', '', 'get'));

    }

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new PlgSystemVMMagicSlideshow(JEventDispatcher::getInstance(), JPluginHelper::getPlugin('system', 'vmmagicslideshow'));
        }
        return self::$instance;
    }

    public function onAfterRender() {

        if(!class_exists('ps_DB')) {
        //if(!defined('_VM_PARSER_LOADED')) {
            //NOTE: some modules (e.g. scroll) can be used on other pages like home page
            require_once(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart_parser.php');
        }

        //NOTE: unsuported VM version
        if($this->vmVersion != '1.0' && $this->vmVersion != '1.1') return;

        $this->coreClass = $this->getToolObj();
        $this->scrollClass = $this->getToolObj(true);
        $this->conf = & $this->coreClass->params;

        $this->bodyContent = JResponse::toString();
        $this->contentBuffer = & $this->bodyContent;


        //NOTE: support child products (ajax-loaded)
        $only_vm_page = JRequest::getVar('magicslideshowtool_vm_only_page', 0);
        if($only_vm_page) {
            $this->bodyContent = preg_replace("/^.*<div id=\"vmMainPage\">/iUs", '', $this->bodyContent);
            $this->bodyContent = preg_replace("/<div class=\"moduletable\">.*$/iUs", '', $this->bodyContent);
            $this->bodyContent = preg_replace("/<div id=\"statusBox\".*$/iUs", '', $this->bodyContent);
        }
        $this->contentBuffer = preg_replace('/(onchange="[^"]*)loadNewPage/s', "$1MagicSlideshowToolVMloadNewPage", $this->contentBuffer);

        if(($this->vmPage == 'shop.browse'  || isset($_POST['option']) && $_POST['option']=='com_customfilters' || isset($_GET['option']) && $_GET['option']=='com_customfilters') && !$this->conf->checkValue('enable-effect', 'No', 'browse')) {

            $this->vmPage = 'shop.browse';

            $this->conf->setProfile('browse');

            /* backup latest prod */
            $modContentL = $this->getModuleContent('virtuemart_latestprod');
            $this->contentBuffer = str_replace($modContentL, '__MAGICTOOLBOX_LATEST_PROD_BACKUP__', $this->contentBuffer);

            /* backup featured prod */
            $modContentF = $this->getModuleContent('virtuemart_featureprod');
            $this->contentBuffer = str_replace($modContentF, '__MAGICTOOLBOX_FEATURED_PROD_BACKUP__', $this->contentBuffer);

            /* backup random prod */
            $modContentF = $this->getModuleContent('virtuemart_randomprod');
            $this->contentBuffer = str_replace($modContentF, '__MAGICTOOLBOX_RANDOM_PROD_BACKUP__', $this->contentBuffer);

            $GLOBALS['magictoolbox_rewrite_done'] = false;

            // for VM 1.0 (browser_1.php page) 
            // and also for sites with Joomla SEF or sh404SEF plugin enabled in Joomla 1.5.x
            $pattern = '/<(script)\s*([^>]*)(?:>(.*?)<\/\1>|\/>)/s';
            $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback_VM10'), $this->contentBuffer);

            // for VM 1.1 with Joomla SEF or sh404SEF plugin (!)enabled
            if($GLOBALS['magictoolbox_rewrite_done'] == false) {
                $pattern = "/<a[^>]*?href=\"[^\"]*\"[^>]*>\s*<img[^>]*?alt=\"[^\"]*\"[^>]*>.*?<\/a>/is";
                $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback_VM10'), $this->contentBuffer);
            }

            // for VM 1.1 with Joomla SEF or sh404SEF plugin disabled
            if($GLOBALS['magictoolbox_rewrite_done'] == false) {
                $pattern = "/<a[^>]*?href=\"[^\"]*shop.product_details[^\"]*product_id=(\d+)[^\"]*\"[^>]*>\s*<img[^>]*>.*?<\/a>/is";
                $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback'), $this->contentBuffer);
            }

            /* restore latest prod */
            $this->contentBuffer = str_replace('__MAGICTOOLBOX_LATEST_PROD_BACKUP__', $modContentL, $this->contentBuffer);

            /* restore featured prod */
            $this->contentBuffer = str_replace('__MAGICTOOLBOX_FEATURED_PROD_BACKUP__', $modContentF, $this->contentBuffer);

            /* restore random prod */
            $this->contentBuffer = str_replace('__MAGICTOOLBOX_RANDOM_PROD_BACKUP__', $modContentF, $this->contentBuffer);
        }

        if(($this->vmPage == 'shop.product_details' || $this->vmPage == 'shop.cart') && !$this->conf->checkValue('enable-effect', 'No', 'details')) {

            $this->conf->setProfile('details');

            $old_content = $this->contentBuffer;
            $pattern = "/(<a[^>]*virtuemart\/shop_image\/product.*?\.[^>]*>\s*<img[^>]*?src=\"([^\"]*?virtuemart\/shop_image\/product.*?\.(jpg|gif|png))[^\"]*\"[^>]*>.*?<\/a>)/is";
            $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback'), $this->contentBuffer);

            if($old_content === $this->contentBuffer) {
                /* following pattern used for some fly_pages */
                $pattern = "/(<a[^>]*virtuemart\/shop_image\/product.*?\.[^>]*>[^<]*?<img[^>]*?src=\"([^\"]*?products\/images\/.*?\.(jpg|gif|png))[^\"]*\"[^>]*>.*?<\/a>)/ims";
                $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback'), $this->contentBuffer);
            }
            
            if($old_content === $this->contentBuffer) {
                /* following pattern used for some fly_pages */
                $pattern = "/(<img[^>]*?src=\"([^\"]*?virtuemart\/shop_image\/product.*?\.(jpg|gif|png))[^\"]*\"[^>]*>)/i";
                $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback'), $this->contentBuffer);
            }

            if($old_content === $this->contentBuffer) {
                /* following pattern used for some fly_pages */
                $pattern = "/(<a[^>]*virtuemart\/shop_image\/product.*?\.[^>]*>[^<]*?<img[^>]*?src=\"([^\"]*?filename=resized.*?\.(jpg|gif|png))[^\"]*\"[^>]*>.*?<\/a>)/ims";
                $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback'), $this->contentBuffer);
            }

            $this->contentBuffer = preg_replace('/<a[^>]*>\s*(<img[^>]*>\s*)?(<br[^>]*>\s*)?View More Image[^<]*(<br[^>]*>\s*)?\s*<\/a>/is', '', $this->contentBuffer);

            /* this used for any fly_pages */
            $pattern = "/(<a[^>]*virtuemart\/show_image_in_imgtag\.php[^>]*>\s*<img[^>]*src=\"([^\"]*virtuemart\/show_image_in_imgtag\.php.*?\.(jpg|gif|png))[^\"]*\"[^>]*>.*?<\/a>)/is";
            $this->contentBuffer = preg_replace_callback($pattern, array(&$this, 'loadIMGCallback'), $this->contentBuffer);

            if($this->preserveAdditionalThumbnailsPositions == false && $this->needHeaders) {
                $this->contentBuffer = preg_replace('/<div[^>]*class=\"additional_images\"[^>]*>.*?<\/div>/is', '', $this->contentBuffer);
                $this->contentBuffer = preg_replace('/<div[^>]*class=\"thumbnailListContainer\"[^>]*>.*?<\/div>/is', '', $this->contentBuffer);

                //more general pattern, because the following two failed
                //TODO: maybe we should find a better way
                //$_image_pattern = '<img[^>]+?class="browseProductImage"[^>]*>[^<]*';
                //$_a_with_image_pattern = '<a[^>]+?(?:(?:rel="lightbox\[product\d+\]")|(?:href="[^"]+?product_id=\d+[^"]+"))[^>]*>[^<]*'.$_image_pattern.'<\/a>[^<]*';
                //$_selectors_pattern = '/(?:'.$_a_with_image_pattern.')+/is';
                //$this->contentBuffer = preg_replace($_selectors_pattern, '', $this->contentBuffer);
                //NOTE: this way should be better
                $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : null;
                if($product_id !== null) {
                    
                    if(class_exists('ps_product_files')) {
                        $files = ps_product_files::getFilesForProduct($product_id);
                    }

                    $tpl = new $GLOBALS['VM_THEMECLASS']();
                    $vmlistAdditionalImageshtml = $tpl->vmlistAdditionalImages($product_id, $files['images']);
                    $this->contentBuffer = str_replace($vmlistAdditionalImageshtml, '', $this->contentBuffer);
                }

                /* remove any additional images on any fly_pages */
                $this->contentBuffer = preg_replace('/<a[^>]*?href=\"[^\"]*?virtuemart[^\"]*\"[^>]*><img[^>]*?src=\"[^\"]*?virtuemart\/show_image_in_imgtag[^\"]*\"[^>]*>.*?<\/a>/is', '', $this->contentBuffer);
                $this->contentBuffer = preg_replace('/<a[^>]*?href=\"[^\"]*?virtuemart[^\"]*\"[^>]*><img[^>]*?src=\"[^\"]*?virtuemart\/shop_image[^\"]*\"[^>]*?class="browseProductImage"[^>]*>.*?<\/a>/is', '', $this->contentBuffer);

                /* remove additional images from yagendoo template (yagendoo_gallery_items) */
                //$this->contentBuffer = preg_replace('/<div id="yagendoo_gallery_items">.*?yagendoo_vm_fly1_br.*?<\/div>\s*<div class="yagendoo_clear"><\/div>/is', '</div><div class="yagendoo_clear"></div>', $this->contentBuffer);
                $this->contentBuffer = preg_replace('/<div id="yagendoo_gallery_items">.*?yagendoo_vm_fly1_br.*?<\/div>.*?<\/div>.*?<\/div>/is', '', $this->contentBuffer);

            }
        }

        if(!$this->conf->checkValue('enable-effect', 'No', 'latest')) {
                $this->conf->setProfile('latest');
                $modContent = $this->getModuleContent('virtuemart_latestprod');
                if($modContent) {
                    $this->latestProd = true;
                    if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                        $content = preg_replace_callback("/<table[^>]*>.*?<\/table>/is", array(&$this, 'loadCircleModuleCallback'), $modContent);
                    } else {
                        $old_content = $modContent;
                        $content = preg_replace_callback("/<a[^>]*?product_id=([0-9]*)[^>]*>\s*<img[^>]*>\s*<\/a>/is", array(&$this, 'loadIMGCallback'), $modContent);
                        if ($old_content == $content) {
                            $content = preg_replace_callback("/<a[^>]*?".">\s*<img[^>]*>\s*<\/a>/is", array(&$this, 'loadIMGCallback_VM10'), $modContent);
                        }
                    }
                    $this->latestProd = false;
                    $this->bodyContent = str_replace($modContent, $content, $this->bodyContent);
                }
        }

        if(!$this->conf->checkValue('enable-effect', 'No', 'featured')) {
                $this->conf->setProfile('featured');
                $modContent = $this->getModuleContent('virtuemart_featureprod');
                if($modContent) {
                    $this->featuredProd = true;
                    if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                        $content = preg_replace_callback("/<table[^>]*>.*?<\/table>/is", array(&$this, 'loadCircleModuleCallback'), $modContent);
                    } else {
                        $old_content = $modContent;
                        $content = preg_replace_callback("/<a[^>]*?product_id=([0-9]*)[^>]*>\s*<img[^>]*>\s*<\/a>/is", array(&$this, 'loadIMGCallback'), $modContent);
                        if ($old_content == $content) {
                            $content = preg_replace_callback("/<a[^>]*?".">\s*<img[^>]*>\s*<\/a>/is", array(&$this, 'loadIMGCallback_VM10'), $modContent);
                        }
                    }
                    $this->featuredProd = false;
                    $this->bodyContent = str_replace($modContent, $content, $this->bodyContent);
                }
        }

        if(!$this->conf->checkValue('enable-effect', 'No', 'random')) {
                $this->conf->setProfile('random');
                $modContent = $this->getModuleContent('virtuemart_randomprod');
                if($modContent) {
                    $this->randomProd = true;
                    if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                        $content = preg_replace_callback("/<table[^>]*>.*?<\/table>/is", array(&$this, 'loadCircleModuleCallback'), $modContent);
                    } else {
                        $old_content = $modContent;
                        $content = preg_replace_callback("/<a[^>]*?product_id=([0-9]*)[^>]*>\s*<img[^>]*>\s*<\/a>/is", array(&$this, 'loadIMGCallback'), $modContent);
                        if ($old_content == $content) {
                            $content = preg_replace_callback("/<a[^>]*?".">\s*<img[^>]*>\s*<\/a>/is", array(&$this, 'loadIMGCallback_VM10'), $modContent);
                        }
                    }
                    $this->randomProd = false;
                    $this->bodyContent = str_replace($modContent, $content, $this->bodyContent);
                }
        }

        $this->conf->setProfile('default');

        /* load JS and CSS */
        if($this->needHeaders && !defined('MagicSlideshow_HEADERS_LOADED')) {

            $headers = '<script type="text/javascript" src="' . $this->mediaURL . '/utils.js"></script>';
            $headers .= $this->coreClass->getHeadersTemplate($this->mediaURL);

            $this->bodyContent = preg_replace('#</head>#is', $headers.'</head>', $this->bodyContent, 1);

            define('MagicSlideshow_HEADERS_LOADED', true);
        }

        // for preserve additional thumbnails positions
        //dmp($this->shouldBeReplaced);
        $this->contentBuffer = preg_replace($this->shouldBeReplaced['patterns'], $this->shouldBeReplaced['replacements'], $this->contentBuffer);

        JResponse::setBody($this->bodyContent);



        return true;

    }

    public function getToolObj($getScrollTool = false) {
        static $mainCoreClass = null;
        static $scrollCoreClass = null;
        global $magicslideshowSupportedBlocks;
        if($mainCoreClass === null) {
            require_once(dirname(__FILE__).DS.'vmmagicslideshow'.DS.'classes'.DS.'magicslideshow.module.core.class.php');
            $mainCoreClass = new MagicSlideshowModuleCoreClass();
            $database = JFactory::getDBO();
            $database->setQuery("SELECT `profile`, `name`, `value` FROM `#__virtuemart_magicslideshow_config`");
            //$database->setQuery("SELECT `profile`, `name`, `value` FROM `#__virtuemart_magicslideshow_config` WHERE `disabled`='0'");
            $results = $database->loadAssocList();
            if(!empty($results)) {
                foreach($results as $row) {
                    $mainCoreClass->params->setValue($row['name'], $row['value'], $row['profile']);
                }
                //foreach($mainCoreClass->params->getProfiles() as $profile) {
                foreach($magicslideshowSupportedBlocks as $profile) {
                    $width = $mainCoreClass->params->getValue('selector-max-width', $profile);
                    $height = $mainCoreClass->params->getValue('selector-max-height', $profile);
                    if($width) {
                        $selectorsSize = $width;
                        if($height) {
                            $selectorsSize = $selectorsSize.'x'.$height;
                        }
                    } else if($height) {
                        $selectorsSize = $height;
                    } else {
                        $selectorsSize = 70;
                    }
                    $mainCoreClass->params->setValue('selectors-size', $selectorsSize, $profile);
                }
            }
        }
        return $getScrollTool ? $scrollCoreClass : $mainCoreClass;
    }

    function getModuleContent($name) {
        $mod = JModuleHelper::getModule($name);
        if(!$mod) return false;
        return $mod->content;
    }

    function loadCircleModuleCallback($matches) {
        if(preg_match_all("/<a[^>]*?product_id=([0-9]*)[^>]*>\s*<img[^>]*>\s*<\/a>/is", $matches[0], $listMatches)) {
            $list = array();
            foreach($listMatches[0] as $k => $m) {
                $list[] = $this->loadIMGCallback(array($m, $listMatches[1][$k]));
            }
            $id = $this->randomProd ? 'random' : $this->latestProd ? 'latest' : 'featured';
            return $this->coreClass->getMainTemplate($list, array('id' => $id));
        }
        return $matches[0];
    }

    //used only for browse pages in VM 1.0
    function loadIMGCallback_VM10($matches) {
        if(preg_match_all("/https?:\/\/(.*?)\.(jpg|jpeg|png|gif)(.*?)[\\\\\"\']/is", $matches[0], $images)){
            $img_big_src = substr($images[0][0], 0, strlen($images[0][0])-1);
            $img_big_src = urldecode($img_big_src);

            $db = new ps_DB;
            $q='SELECT * FROM #__{vm}_product WHERE product_full_image LIKE \'%'.basename($img_big_src).'\' AND product_publish=\'Y\'';

            if(preg_match('/show_image_in_imgtag/is', $img_big_src)) {
                $img_big_src = preg_replace('/^(.*?\.(?:jpg|png|gif))(?:\&|\?).*$/is', '$1', $img_big_src);
                $img_big_src = preg_replace('/^.*?show_image_in_imgtag\.php\?filename=/is', '', $img_big_src);
                $q='SELECT * FROM #__{vm}_product WHERE product_thumb_image LIKE \'%'.basename($img_big_src).'\' AND product_publish=\'Y\'';
            }

            if(preg_match('/resized/is', $img_big_src)) {
                $img_big_src = preg_replace('/^(.*?\.(?:jpg|png|gif))(?:\&|\?).*$/is', '$1', $img_big_src);
                $img_big_src = preg_replace('/^.*?=resized\//is', '', $img_big_src);
                $q='SELECT * FROM #__{vm}_product WHERE product_thumb_image LIKE \'%'.basename($img_big_src).'\' AND product_publish=\'Y\'';
            }

            $db->query($q);
            if($db->num_rows() > 0) {
                $marr = array();
                $marr[0] = $matches[0];
                $marr[1] = $db->f("product_id");
                $GLOBALS['magictoolbox_rewrite_done'] = true;
                return $this->loadIMGCallback($marr);
            }
        }

        return $matches[0];
    }

    function loadIMGCallback($matches, $returnArray = false, $_pid = 0) {
        if(preg_match('/.*?class=(\'|")[^\'"]*?(Magic(Zoom|Thumb|Magnify|Slideshow|Scroll|Touch|360)(Plus)?)[^\'"]*?(\'|").*/is', $matches[0])) return $matches[0];

        // allow to show product when click on image (in latestProd module and browse pages)
        /*if($this->vmPage == 'shop.browse') {
            $linkHref = preg_replace("/^.*?<a[^>]*?href=\"([^\"]*)\".*$/iUs", "$1", $matches[0]);
        } else {
            $linkHref = preg_replace("/^\s*<a[^>]*?href=\"([^\"]*)\".*$/iUs", "$1", $matches[0]);
        }
        if($linkHref == $matches[0]) {
            $linkHref = false;
            $linkOnclick = false;
        } else if(preg_match("/^\s*javascript\s*\:.*$/is", $linkHref)) {
            $linkOnclick = preg_replace("/^\s*javascript\s*\:(.*)$/is", "$1", $linkHref);
            $linkOnclick = str_replace("\\'", "'", $linkOnclick);
            $linkHref = false;
        } else {
            $linkOnclick = "document.location.href = '{$linkHref}';";
        }*/

        if(($this->vmPage == 'shop.product_details' || $this->vmPage == 'shop.cart') && isset($GLOBALS['mtdone']) && $this->conf->getProfile()=='details') {
             return $matches[0];
        }

        if($this->conf->getProfile() == 'details') {
             $GLOBALS['mtdone'] = true;
        }

        $db = new ps_DB;
        $zoom_id = '';
        $images = array();

        $title = '';
        $description_short = '';
        $description = '';

        if ($returnArray || $this->latestProd == true || $this->featuredProd == true || $this->randomProd == true || $this->vmPage == 'shop.browse') {
            $product_id = $matches[1];

            if (empty($product_id) && !empty($_GET['product_id'])) {
                $product_id = intval($_GET['product_id']);
            }

            if (empty($product_id) && !empty($_REQUEST['product_id'])) {
                $product_id = intval($_REQUEST['product_id']);
            }

            if (empty($product_id)) {
                $img_big_src = preg_replace("/.*src=\"(.*?)\".*/ims","$1",$matches[0]);
                if(preg_match('/resized/is', $img_big_src)) {
                    $img_big_src = preg_replace('/^(.*?\.(?:jpg|png|gif))(?:\&|\?).*$/is', '$1', $img_big_src);
                    $img_big_src = preg_replace('/^.*\/(.*?)$/is', '$1', $img_big_src);
                    $q='SELECT * FROM #__{vm}_product WHERE product_thumb_image LIKE \'%'.basename($img_big_src).'\' AND product_publish=\'Y\'';
                }
                $db->query($q);
                if($db->num_rows() > 0) {
                    $product_id = $db->f("product_id");
                }
            }

            $useLink = $this->conf->checkValue('link-to-product-page', 'Yes');
            if($useLink) {
                $sess = new ps_session();
                $link_parameters = 'page=shop.product_details&amp;flypage=' . FLYPAGE . '&amp;product_id=' . intval($product_id);
                $link = MAGICTOOLBOX_JURI_BASE . $sess->url($link_parameters);
            } else {
            $link = '';
            }

            $product = $this->getProductInfo($product_id);

            if(!$_pid && ($returnArray || $this->latestProd || $this->featuredProd || $this->randomProd) && ($this->coreClass->type == 'category' || $this->coreClass->type == 'circle')) {
                if(empty($product['img'])) $product['img'] = 'noimage';
                if(empty($product['thumb'])) $product['thumb'] = 'noimage';
            }

            $description = $product['description'];
            $description_short = $product['description_short'];
            $title = $product['title'];

            if(!empty($product['img'])) {
                $img_big_src = $this->resolveImageUrl($product['img']);
            }else{
                return $matches[0];
            }

            //$img_big_path = IMAGEPATH."product/".$product['img'];

            if(!empty($product['thumb'])) {
                $img_small = $this->resolveImageUrl($product['thumb']);
            }else{
                //return $matches[0];
            }

            //$db->query('SELECT * FROM #__{vm}_product WHERE product_id='.$product_id.' AND product_publish=\'Y\'');
            //$img_big_src = IMAGEURL."product/".$db->f("product_full_image");
            //$img_small = "product/".$db->f("product_thumb_image");         
            //$description = $db->f("product_desc");
            //$description_short = $db->f("product_s_desc");
            //$title = $db->f("product_name");
            //$img_small_src = IMAGEURL."product/".$db->f("product_thumb_image");
            if($this->latestProd == true) $zoom_id = "LatestProd" . md5($img_big_src);
            if($this->featuredProd == true) $zoom_id = "FeaturedProd" . md5($img_big_src);
            if($this->randomProd == true) $zoom_id = "RandomProd" . md5($img_big_src);
            if($returnArray) $zoom_id = "Custom" . md5($img_big_src);
        }

        if($_pid || !$returnArray && $this->latestProd == false && $this->featuredProd == false && $this->randomProd == false && ($this->vmPage == 'shop.product_details' || $this->vmPage == 'shop.cart')) {

            $link = '';

            if($_pid) $product_id = $_pid;
            if($this->vmVersion == '1.1') $product_id = intval( vmGet($_REQUEST, "product_id", null) );
            else $product_id = intval( mosGetParam($_REQUEST, "product_id", null) );

            if (empty($product_id) && !empty($_GET['product_id'])) {
                $_pid = $product_id = intval($_GET['product_id']);
            }
            if (empty($product_id) && !empty($_REQUEST['product_id'])) {
                $_pid = $product_id = intval($_REQUEST['product_id']);
            }
            if (empty($_pid)) {
                $_pid = $product_id;
            }


            $zoom_id = $product_id;

            $product = $this->getProductInfo($product_id);

            if(!empty($product['link'])) {
                $link = $product['link'];
            }

            $description = $product['description'];
            $description_short = $product['description_short'];
            $title = $product['title'];

            if(!empty($product['img'])) {
                $img_big_src = $this->resolveImageUrl($product['img']);
                $img_big_path = $this->resolveImagePath($product['img']);
            } else {
                return $matches[0];
            }
            if(!empty($product['thumb'])) {
                $img_small = $this->resolveImageUrl($product['thumb']);
            } else {
                return $matches[0];
            }

            if (!$this->isUrl($img_big_path) && !file_exists($img_big_path)) return $matches[0];

            //$img_small_src = IMAGEURL."product/".$product['product_thumb_image'];
            //$img_small_path = IMAGEPATH."product/".$product['product_thumb_image'];
            //if (!file_exists($img_small_path)) $img_small_src = $img_big_src;

            $path_big = pathinfo($img_big_src);
            //$path_small = pathinfo($img_small_src);

            //$path_big['basename'] = urlencode($path_big['basename']);
            //$path_small['basename'] = urlencode($path_small['basename']);

            //preg_match('/'.preg_quote($path_big['basename']).'/is', $matches[0], $img_big_match);
            //preg_match('/'.preg_quote($path_small['basename']).'/is', $matches[0], $img_small_match);
            preg_match('/'.preg_quote($path_big['basename']).'|'.preg_quote(rawurlencode($path_big['basename'])).'/is', $matches[0], $img_big_match);
            if(!empty($product['url'])) {
                preg_match('/'.preg_quote($product['url'], '/').'|'.preg_quote(rawurlencode($product['url']), '/').'/is', $matches[0], $product_url_match);
            } else {
                $product_url_match = false;
            }

            if (!$_pid && !$img_big_match /*&& !$img_small_match*/ && !$product_url_match) return $matches[0];

            /*$dbi = new ps_DB();
            $dbi->query( "SELECT * FROM #__{vm}_product_files WHERE file_product_id='$product_id' AND file_is_image='1' AND file_published='1'" );
            $images = $dbi->record;*/

            $dbi = new ps_DB();
            $query = "SELECT pf.* FROM #__{vm}_product_files AS pf WHERE pf.file_product_id='%u' AND pf.file_is_image='1' AND pf.file_published='1'";
            $dbi->query(sprintf($query,$product_id));

            //if product has no images inherit them from parent product
            if(!$dbi->next_record()){
                $dbi->query("SELECT product_parent_id FROM #__{vm}_product WHERE product_id='$product_id'");

                $product_parent_id  = $dbi->f("product_parent_id");
                if($product_parent_id) $product_id = $product_parent_id;
                $dbi->query(sprintf($query,$product_id));
            }

            $images = $dbi->record;

        }

        //$img_small_src = $img_big_src;

        if (!empty($img_big_src)) {
            if($this->coreClass->type == 'circle' && !$this->coreClass->isEnabled(count($images) + 1, $product_id)) {
                return $matches[0];
            }
            if(JModuleHelper::getModule('virtuemart_magic360flash')) {
                $GLOBALS['magictoolbox']['magic360flashVM']->conf->setProfile($this->conf->getProfile());
                if($GLOBALS['magictoolbox']['magic360flashVM']->coreClass->isEnabled(count($images) + 1, $product_id)) {
                    return $matches[0];
                }
                $GLOBALS['magictoolbox']['magic360flashVM']->conf->setProfile('default');
            }
            if(JModuleHelper::getModule('virtuemart_magic360')) {
                $GLOBALS['magictoolbox']['magic360VM']->conf->setProfile($this->conf->getProfile());
                if($GLOBALS['magictoolbox']['magic360VM']->coreClass->isEnabled(count($images) + 1, $product_id)) {
                    return $matches[0];
                }
                $GLOBALS['magictoolbox']['magic360VM']->conf->setProfile('default');
            }
            $this->needHeaders = true;


            if(!$_pid && ($returnArray || $this->latestProd || $this->featuredProd || $this->randomProd) && ($this->coreClass->type == 'category' || $this->coreClass->type == 'circle')) {
                return array(
                    //"img" => $this->makeThumb($img_big_src, "original", $product_id, $img_big_src),
                    "id" => $zoom_id,
                    "title" => $title,
                    //"description" => $description,
                    "img" => $this->makeThumb($img_big_src, "thumb", $product_id, $img_small),
                    "thumb" => $this->makeThumb($img_big_src, "selector", $product_id),
                    "fullscreen" => $this->makeThumb($img_big_src, "original", $product_id),
                    'link' => $link
                );
            }

            $ret = array();

            $main = array();
            $thumbs = array();

            if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                $list = array();
            }

            /*$alt = '';
            preg_match("/alt=\"(.*?)\"/is", $matches[0], $alt);
            if (count($alt)) $alt = $alt[1];
            else $alt = ''; */

            $t = array(
                "img" => $this->makeThumb($img_big_src, 'original', $product_id, $img_big_src),
                "id" => $zoom_id,
                "title" => $title,
                "shortDescription" => $description_short,
                "description" => $description,
                "thumb" => $this->makeThumb($img_big_src, 'thumb', $product_id, $img_small),
                "link" => $link
            );

            if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                //$list[] = $t;
            } else {
                $t = $this->coreClass->getMainTemplate($t);

                if($this->latestProd == false && $this->featuredProd == false && $this->randomProd == false && $this->conf->checkValue("preserve-lightbox","Yes")) {
                    $t = str_replace('<a ','<a onclick="magicLightBox(this);" ',$t);
                }

                if($this->latestProd == true || $this->featuredProd == true || $this->randomProd == true || $this->conf->checkValue("centered-thumbnails", "Yes")) {
                    $t = str_replace("<a ","<a style=\"margin:0 auto;\" ",$t);
                }



                $main = $t;
            }

            if(($this->vmPage == "shop.product_details" || $this->vmPage == 'shop.cart') && count($images) > 0) {

                //$style = '';
                $style = array(
                    'margin-bottom' => $this->conf->getValue("margin-between-thumbs").'px',
                    'margin-right' => $this->conf->getValue("margin-between-thumbs").'px',
                );
                if($this->conf->checkValue('magicscroll', 'No')) {
                    $style = array_merge($style, array(
                        'display' => 'block',
                        'float'   => 'left',
                    ));
                }
                $style = 'style="'.$this->renderStyle($style).'"';

                $t = array(
                    "img" => $this->makeThumb($img_big_src, "original", $product_id, $img_big_src),
                    "id" => $zoom_id,
                    "title" => $title,
                    "description" => $description,
                    "medium" => $this->makeThumb($img_big_src, "thumb", $product_id, $img_small),
                    "thumb" => $this->makeThumb($img_big_src, "selector", $product_id)
                );
                $t['fullscreen'] = $t['img'];
                $t['img'] = $t['medium'];

                if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                    $list[] = $t;
                } else {
                    if($this->conf->checkValue('multiple-images', 'Yes')) {
                        $t = $this->coreClass->getSelectorTemplate($t);
                        $thumbs[] = str_replace("<a ","<a " . $style . " ",$t);
                    }
                }


                if($this->conf->checkValue("multiple-images", "Yes") || $this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                    $tp = false;
                    foreach($images as $img){
                        $tp = array(
                            "img" => $this->makeThumb($img->file_url, "original", $product_id, $img->file_url),
                            "id" => $zoom_id,
                            "title" => $this->conf->checkValue('use-individual-titles', 'Yes') ? stripslashes($img->file_title) : $title,
                            "shortDescription" => $description_short,
                            "description" =>$this->conf->checkValue('use-individual-titles', 'Yes') ? '' : $description,
                            "medium" => $this->makeThumb($img->file_name,"thumb", $product_id),
                            "thumb" => $this->makeThumb($img->file_name,"selector", $product_id)
                        );
                        $tp['fullscreen'] = $tp['img'];
                        $tp['img'] = $tp['medium'];
                        if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                            $list[] = $tp;
                        } else {
                                $t = $this->coreClass->getSelectorTemplate($tp);
                            if($this->conf->checkValue("preserve-additional-thumbnails-positions", "Yes")) {
                                $this->replaceThumbInFlypage($img, $t);
                            }
                            $thumbs[] = str_replace("<a ","<a " . $style . " ",$t);
                        }
                    }
                    if($this->preserveAdditionalThumbnailsPositions === true || $tp === false /* some additional images can be hotspots and not is_alternate */ ) {
                        $thumbs = array();
                    }
                }

                /*if($this->preserveAdditionalThumbnailsPositions == false) {
                    $ret[] = '<div class="MagicToolboxSelectorsContainer" style="margin-top:'.$this->conf->getValue("thumbnail-top-margin").'px;">'.join($thumbs, ' ').'</div>';
                }*/
            }

            /*if($this->conf->checkValue('show-message', 'Yes')) {
                $message = $this->conf->getValue('message');
            } else $message = '';*/

            if($this->coreClass->type == 'category' || $this->coreClass->type == 'circle') {
                if(count($list) < 2) {
                    return $matches[0];
                }
                foreach($list as $k => $v) {
                    unset($list[$k]['description']);
                }
                if($returnArray) {
                    return $list;
                } else {
                    return $this->coreClass->getMainTemplate($list, array('id' => 'detailed' . $product_id));
                }
            } else {

                $magicscrollOptions = '';
                return $this->renderTemplate(array(
                    'main' => $main,
                    'thumbs' => $thumbs,
                    'magicscrollOptions' => $magicscrollOptions,
                    //'message' => $message,
                ));

            }

            //return '<div class="MagicToolboxContainer" style="text-align: ' . (($this->latestProd == true || $this->featuredProd == true || $this->randomProd == true || $this->conf->checkValue("centered-thumbnails", "Yes")) ? 'center' : 'left') . ' !important; ' . ($this->conf->checkValue("use-original-vm-thumbnails", "Yes")?'':('width: ' .$this->conf->getValue("thumb-max-width").'px;')) . '" >'.join($ret, ' ').'</div>';
        }
        else return $matches[0];
    }

    function replaceThumbInFlypage($img, $tpl) {
        $patterns = array(
            "/<a[^>]*>\s*<img[^>]*?src=\"(" . preg_quote($img->file_url, "/") . "[^\"]*)\"[^>]*>.*?<\/a>/is",
            "/<a[^>]*>\s*<img[^>]*?src=\"([^\"]*?virtuemart\/show_image_in_imgtag\.php\?[^\"]*?" . preg_quote($img->file_name, "/") . "[^\"]*)\"[^>]*>.*?<\/a>/is",
            "/<a[^>]*>\s*<img[^>]*?src=\"([^\"]*?virtuemart\/show_image_in_imgtag\.php\?[^\"]*?" . preg_quote(urlencode($img->file_name), "/") . "[^\"]*)\"[^>]*>.*?<\/a>/is"
        );
        foreach($patterns as $pattern) {
            if(preg_match($pattern, $this->contentBuffer, $matches)) {
                if($this->conf->checkValue("use-original-vm-thumbnails", "Yes")) {
                    $tpl2 = preg_replace('/src=\".*?\"/is', 'src="' . $matches[1] . '"', $tpl);
                } else $tpl2 = $tpl;
                $this->preserveAdditionalThumbnailsPositions = true;
                // we can't replace becase main preg_replace will be restore all chnages
                //$this->contentBuffer = preg_replace($pattern, $tpl, $this->contentBuffer);
                $this->shouldBeReplaced['patterns'][] = $pattern;
                $this->shouldBeReplaced['replacements'][] = $tpl2;
                break;
            }
        }
    }

    function getProductInfo($id, $field = null, $value = null) {
        if($field !== null && $value !== null && !empty($value)) return $value;

        if(intval($id) < 1) return false;

        if(!isset($GLOBALS['magictoolbox']['products_cache'])) $GLOBALS['magictoolbox']['products_cache'] = array();

        if(isset($GLOBALS['magictoolbox']['products_cache'][$id])) {
            // get from magictoolbox cashe
            $product = $GLOBALS['magictoolbox']['products_cache'][$id];
        } else if(isset($GLOBALS['product_info']) && isset($GLOBALS['product_info'][$id]) && isset($GLOBALS['product_info'][$id]['product_full_image'])) {
            // get from globals (virtuemart cashe)
            $parentID = $GLOBALS['product_info'][$id]['product_parent_id'];
            $product = array();
            $product['title'] = $GLOBALS['product_info'][$id]['product_name'];
            $product['description'] = $this->getProductInfo($parentID, "description", $GLOBALS['product_info'][$id]['product_desc']);
            $product['description_short'] = $this->getProductInfo($parentID, "description_short", $GLOBALS['product_info'][$id]['product_s_desc']);

            $product['img'] = $this->getProductInfo($parentID, "img", $GLOBALS['product_info'][$id]['product_full_image']);
            $product['thumb'] = $this->getProductInfo($parentID, "thumb", $GLOBALS['product_info'][$id]['product_thumb_image']);

            $product['url'] = $this->getProductInfo($parentID, "url", $GLOBALS['product_info'][$id]['product_url']);
        } else {
            //get from DB
            $db = new ps_DB;
            $db->query('SELECT * FROM #__{vm}_product WHERE product_id='.$id.' AND product_publish=\'Y\'');
            $parentID = $db->f("product_parent_id");
            $product = array();
            $product['title'] = $db->f("product_name");
            $product['description'] = $this->getProductInfo($parentID, "description", $db->f("product_desc"));
            $product['description_short'] = $this->getProductInfo($parentID, "description_short", $db->f("product_s_desc"));

            $product['img'] = $this->getProductInfo($parentID, "img", $db->f("product_full_image"));
            $product['thumb'] = $this->getProductInfo($parentID, "thumb", $db->f("product_thumb_image"));

            $product['url'] = $this->getProductInfo($parentID, "url", $db->f("product_url"));
        }

        // add to cashe
        $GLOBALS['magictoolbox']['products_cache'][$id] = $product;

        if($field !== null) return $product[$field];
        else return $product;
    }

    function makeThumb($filename, $size, $pid = null, $origThumb = '', $returnSize = false) {
        if(!empty($origThumb) && $this->conf->checkValue('use-original-vm-thumbnails', 'Yes')) {
            if($this->isUrl($origThumb)) {
                return $origThumb;
            }
            //if(file_exists(IMAGEPATH . $origThumb)) {
            //    return IMAGEURL . $origThumb;
            //}
            if(file_exists(JPATH_ROOT .'/'. $origThumb)) {
                return JURI::base() . $origThumb;
            }
        }

        $isUrl = $this->isUrl($filename);
        if($isUrl && strpos($filename, JURI::base()) !== false) {
            $filename = str_replace(JURI::base(), '/', $filename);
            $isUrl = false;
        }

        $noImage = VM_THEMEURL.'images/'.NO_IMAGE;

        $filename = str_replace('%20', ' ', $filename);
        $info = pathinfo($filename);
        if(intval(phpversion()) < 5 || !isset($info['filename'])) {
            //$info['filename'] = basename($info['basename'], ".".$info['extension']);
            $info['filename'] = preg_replace("/\." . preg_quote($info['extension']) . "$/is", '', $info['basename']);
        }

        $imgpath = str_replace(JPATH_SITE, '', IMAGEPATH);

        $path_full = IMAGEPATH . "product/" . $info['basename'];
        $path_rel = $imgpath . "product/" . $info['basename'];

        if($isUrl && !file_exists($path_full)) {
            $remote_file = @file_get_contents($info['dirname'].'/'.rawurlencode($info['basename']));
            if($remote_file){
                file_put_contents($path_full, $remote_file);
            } else {
                return $noimage;
            }
        }
        if(!$isUrl) {
            $path_rel = ($info['dirname'] != '/' ? preg_replace('/^(?:\/)?(.*)$/is', '/$1', $info['dirname']) : '').'/'.$info['basename'];
            $path_full = JPATH_SITE.$path_rel;
        }
        if(!file_exists($path_full) || filesize($path_full) == 0) {
            return $noImage;
        }

        if($returnSize === true) {
            $maxW = intval(str_replace("px", '', $this->conf->getValue($size . '-max-width')));
            $maxH = intval(str_replace("px", '', $this->conf->getValue($size . '-max-height')));
            $size = getimagesize($path_full);
            $originalW = $size[0];
            $originalH = $size[1];
            if(!$maxW && !$maxH) {
                return (object)array('w'=>$originalW,'h'=>$originalH);
            } elseif(!$maxW) {
                $maxW = ($maxH * $originalW) / $originalH;
            } elseif(!$maxH) {
                $maxH = ($maxW * $originalH) / $originalW;
            }
            $sizeDepends = $originalW/$originalH;
            $placeHolderDepends = $maxW/$maxH;
            if($sizeDepends > $placeHolderDepends) {
                $newW = $maxW;
                $newH = $originalH * ($maxW / $originalW);
            } else {
                $newW = $originalW * ($maxH / $originalH);  
                $newH = $maxH;
            }
            return (object)array('w'=>round($newW),'h'=>round($newH));
        }

        require_once(dirname(__FILE__).DS.'vmmagicslideshow'.DS.'classes'.DS.'magictoolbox.imagehelper.class.php');

        $helper = new MagicToolboxImageHelperClass(JPATH_SITE, $imgpath . 'product/resized/magictoolbox_cache', $this->conf, null, MAGICTOOLBOX_JURI_BASE);
        return $helper->create($path_rel, $size, $pid);
    }

    function isUrl($string) {
        return preg_match('/^https?:\/\//is',$string);
    }

    function resolveImageUrl($string) {
        if(!$this->isUrl($string) && $string != 'noimage') {
            $string = IMAGEURL.'product/'.$string;
        }
        return $string;
    }

    function resolveImagePath($string,$thumb = false) {
        if(!$this->isUrl($string) && !file_exists($string)) {
            $string = IMAGEPATH.'product/'.($thumb?'resized/':'').basename($string);
        }
        return $string;
    }

    function renderStyle($css){
        $style = array();

        foreach($css as $attr => $value){
            $style[] = "$attr: $value";
        }
        return join('; ',$style);
    }

    function renderTemplate($options){
        //require_once(dirname(__FILE__).DS.'vmmagicslideshow'.DS.'classes'.DS.'magictoolbox.templatehelper.class.php');
        //MagicToolboxTemplateHelperClass::setPath(dirname(__FILE__).DS.'vmmagicslideshow'.DS.'templates');
        //MagicToolboxTemplateHelperClass::setOptions($this->conf);
        return MagicToolboxTemplateHelperClass::render($options);
    }

    function getRow(&$db) {
        return $db->record[$db->row];
    }

}
