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

if(!defined('MagicSlideshowModuleCoreClassLoaded')) {

    define('MagicSlideshowModuleCoreClassLoaded', true);

    require_once(dirname(__FILE__).'/magictoolbox.params.class.php');

    /**
     * MagicSlideshowModuleCoreClass
     *
     */
    class MagicSlideshowModuleCoreClass {

        /**
         * MagicToolboxParamsClass class
         *
         * @var   MagicToolboxParamsClass
         *
         */
        var $params;

        /**
         * Tool type
         *
         * @var   string
         *
         */
        var $type = 'category';

        /**
         * Constructor
         *
         * @return void
         */
        function __construct() {
            $this->params = new MagicToolboxParamsClass();
            $this->params->setScope('magicslideshow');
            $this->params->setMapping(array(
                'arrows' => array('Yes' => 'true', 'No' => 'false'),
                'loop' => array('Yes' => 'true', 'No' => 'false'),
                'autoplay' => array('Yes' => 'true', 'No' => 'false'),
                'shuffle' => array('Yes' => 'true', 'No' => 'false'),
                'kenburns' => array('Yes' => 'true', 'No' => 'false'),
                'pause' => array('Yes' => 'true', 'No' => 'false'),
                'selectors-eye' => array('Yes' => 'true', 'No' => 'false'),
                'selectors-fill' => array('Yes' => 'true', 'No' => 'false'),
                'caption' => array('Yes' => 'true', 'No' => 'false'),
                'fullscreen' => array('Yes' => 'true', 'No' => 'false'),
                'preload' => array('Yes' => 'true', 'No' => 'false'),
                'keyboard' => array('Yes' => 'true', 'No' => 'false'),
                'show-loader' => array('Yes' => 'true', 'No' => 'false'),
                'autostart' => array('Yes' => 'true', 'No' => 'false'),
            ));
            $this->loadDefaults();
        }

        /**
         * Method to get headers string
         *
         * @param string $jsPath  Path to JS file
         * @param string $cssPath Path to CSS file
         *
         * @return string
         */
        function getHeadersTemplate($jsPath = '', $cssPath = null) {
            //to prevent multiple displaying of headers
            if(!defined('MAGICSLIDESHOW_MODULE_HEADERS')) {
                define('MAGICSLIDESHOW_MODULE_HEADERS', true);
            } else {
                return '';
            }
            if($cssPath == null) {
                $cssPath = $jsPath;
            }
            $headers = array();
            $headers[] = '<!-- Magic Slideshow VirtueMart 1.x module module version v4.9.6 [v1.6.77:v3.1.15] -->';
            $headers[] = '<script type="text/javascript">window["mgctlbx$Pltm"] = "VirtueMart 1.x module";</script>';
            $headers[] = '<link type="text/css" href="'.$cssPath.'/magicslideshow.css" rel="stylesheet" media="screen" />';
            $headers[] = '<link type="text/css" href="'.$cssPath.'/magicslideshow.module.css" rel="stylesheet" media="screen" />';
            $headers[] = '<script type="text/javascript" src="'.$jsPath.'/magicslideshow.js"></script>';
            $headers[] = $this->getOptionsTemplate();
            return "\r\n".implode("\r\n", $headers)."\r\n";
        }

        /**
         * Method to get options string
         *
         * @return string
         */
        function getOptionsTemplate() {
            $addition = '';
            if($selectorsSize = $this->params->getParam('selectors-size')) {
                if(!isset($selectorsSize['scope']) || $selectorsSize['scope'] != 'magicslideshow') {
                    $selectorsSize = $this->params->getValue('selectors-size');
                    $addition = "\n\t\t'selectors-size':'{$selectorsSize}',";
                }
            } else {
                if($this->params->checkValue('selectors', array('bottom', 'top'))) {
                    $selectorsSize = $this->params->getValue('selector-max-height');
                    if(empty($selectorsSize)) {
                        $selectorsSize = 70;
                    }
                } else if($this->params->checkValue('selectors', array('right', 'left'))) {
                    $selectorsSize = $this->params->getValue('selector-max-width');
                    if(empty($selectorsSize)) {
                        $selectorsSize = 70;
                    }
                } else {
                    $selectorsSize = 70;
                }
                $addition = "\n\t\t'selectors-size':'{$selectorsSize}',";
            }
            return "<script type=\"text/javascript\">\n\tMagicSlideshowOptions = {{$addition}\n\t\t".$this->params->serialize(true, ",\n\t\t")."\n\t}\n</script>";
        }

        /**
         * Method to get MagicSlideshow HTML
         *
         * @param array $data   MagicSlideshow items data
         * @param array $params Additional params
         *
         * @return string
         */
        function getMainTemplate($data, $params = array()) {
            $id = '';
            $width = '';
            $height = '';

            $html = array();

            extract($params);

            if(empty($width)) {
                $width = '';
            } else {
                $width = " width=\"{$width}\"";
            }
            if(empty($height)) {
                $height = '';
            } else {
                $height = " height=\"{$height}\"";
            }

            if(empty($id)) {
                $id = '';
            } else {
                $id = ' id="'.addslashes($id).'"';
            }

            $options = '';
            if($selectorsSize = $this->params->getParam('selectors-size'/*, '', true*/)) {
                if(!isset($selectorsSize['scope']) || $selectorsSize['scope'] != 'magicslideshow') {
                    $selectorsSize = $this->params->getValue('selectors-size');
                    $options = "selectors-size:{$selectorsSize};";
                }
            } else {
                if($this->params->checkValue('selectors', array('bottom', 'top'))) {
                    $selectorsSize = $this->params->getValue('selector-max-height');
                    if(empty($selectorsSize)) {
                        $selectorsSize = 70;
                    }
                } else if($this->params->checkValue('selectors', array('right', 'left'))) {
                    $selectorsSize = $this->params->getValue('selector-max-width');
                    if(empty($selectorsSize)) {
                        $selectorsSize = 70;
                    }
                } else {
                    $selectorsSize = 70;
                }
                $options = "selectors-size:{$selectorsSize};";
            }

            //NOTE: get personal options
            $options .= $this->params->serialize();
            if(empty($options)) {
                $options = '';
            } else {
                $options = ' data-options="'.$options.'"';
            }

            $html[] = '<div'.$id.' class="MagicSlideshow"'.$width.$height.$options.'>';

            foreach($data as $item) {

                $img = '';//main image
                $img2x = '';//main 2x image
                $thumb = '';//thumbnail image
                $fullscreen = '';//image shown in Full Screen
                $link = '';
                $target = '';
                $alt = '';
                $title = '';
                $description = '';
                $width = '';
                $height = '';
                $content = '';

                extract($item);

                if(empty($link)) {
                    $link = '';
                } else {
                    if(empty($target)) {
                        $target = '';
                    } else {
                        $target = ' target="'.$target.'"';
                    }
                    $link = $target.' href="'.addslashes($link).'"';
                }

                if(empty($alt)) {
                    $alt = '';
                } else {
                    $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
                }

                if(empty($title)) {
                    $caption = $title = '';
                } else {
                    $caption = $title;
                    $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                    if(empty($alt)) {
                        $alt = $title;
                    }
                    $title = " title=\"{$title}\"";
                }

                if(empty($description)) {
                    $description = '';
                } else {
                    $description = preg_replace('#<(/?)a([^>]*+)>#is', '[$1a$2]', $description);
                    $description = str_replace('"', '&quot;', $description);
                }

                if(empty($width)) {
                    $width = '';
                } else {
                    $width = " width=\"{$width}\"";
                }
                if(empty($height)) {
                    $height = '';
                } else {
                    $height = " height=\"{$height}\"";
                }

                if(!empty($content)) {
                    $mssCaption = '';//NOTE: caption is displayed under title
                    if(empty($thumb)) {
                        $thumb = '';
                        $mssThumbnail = "<div data-mss-thumbnail>{$alt}</div>";
                    } else {
                        $thumb = ' data-thumb-image="'.$thumb.'"';
                        $mssThumbnail = '';
                    }
                    $html[] = "<div class=\"mss-content-container\"{$title}{$thumb}>{$mssThumbnail}{$mssCaption}{$content}</div>";
                } else if(empty($img)) {
                    if(empty($caption)) {
                        $html[] = "<div>{$description}</div>";
                    } else {
                        //data-out-move=\"fade\"
                        $html[] = "<div><div data-mss-caption>{$caption}</div><div data-mss-thumbnail>{$description}</div></div>";
                    }
                } else {
                    if(empty($thumb)) {
                        $thumb = $img;
                    }
                    if(empty($fullscreen)) {
                        $fullscreen = $img;
                    }
                    $img = $this->params->checkValue('preload', 'Yes') ? ' src="'.$img.'"' : ' data-image="'.$img.'"';
                    if (!empty($img2x)) {
                        //$img .= ' srcset="'.$img2x.' 2x" ';
                        //$img .= ' srcset="'.$img.' 1x, '.$img2x.' 2x" ';
                        $img .= ' srcset="'.str_replace(' ', '%20', $img).' 1x, '.str_replace(' ', '%20', $img2x).' 2x"';
                    }
                    $thumb = ' data-thumb-image="'.$thumb.'"';
                    $fullscreen = ' data-fullscreen-image="'.$fullscreen.'"';
                    if(!empty($description)) {
                        $description = " data-caption=\"{$description}\"";
                    }
                    $html[] = "<a{$link}><img{$width}{$height}{$img}{$thumb}{$fullscreen}{$title}{$description} alt=\"{$alt}\" /></a>";
                }

            }

            $html[] = '</div>';

            if($this->params->checkValue('show-message', 'Yes')) {
                $html[] = '<div class="MagicToolboxMessage">'.$this->params->getValue('message').'</div>';
            }

            return implode('', $html);
        }

        /**
         * Method to load defaults options
         *
         * @return void
         */
        function loadDefaults() {
            $params = array(
				"enable-effect"=>array("id"=>"enable-effect","group"=>"General","order"=>"10","default"=>"No","label"=>"Enable effect","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
				"thumb-max-width"=>array("id"=>"thumb-max-width","group"=>"Positioning and Geometry","order"=>"10","default"=>"200","label"=>"Maximum width of thumbnail (in pixels)","type"=>"num","scope"=>"module"),
				"thumb-max-height"=>array("id"=>"thumb-max-height","group"=>"Positioning and Geometry","order"=>"11","default"=>"200","label"=>"Maximum height of thumbnail (in pixels)","type"=>"num","scope"=>"module"),
				"selector-max-width"=>array("id"=>"selector-max-width","group"=>"Positioning and Geometry","order"=>"12","default"=>"50","label"=>"Maximum width of additional thumbnails (in pixels)","type"=>"num","scope"=>"module"),
				"selector-max-height"=>array("id"=>"selector-max-height","group"=>"Positioning and Geometry","order"=>"13","default"=>"50","label"=>"Maximum height of additional thumbnails (in pixels)","type"=>"num","scope"=>"module"),
				"square-images"=>array("id"=>"square-images","group"=>"Positioning and Geometry","order"=>"310","default"=>"disable","label"=>"Create square images","description"=>"The white/transparent padding will be added around the image or the image will be cropped.","type"=>"array","subType"=>"radio","values"=>array("extend","crop","disable"),"scope"=>"module"),
				"width"=>array("id"=>"width","group"=>"Common settings","order"=>"10","default"=>"auto","label"=>"Slideshow width","description"=>"auto | pixels | percentage","type"=>"text","scope"=>"magicslideshow"),
				"height"=>array("id"=>"height","group"=>"Common settings","order"=>"20","default"=>"auto","label"=>"Slideshow height","description"=>"auto | responsive | pixels | percentage","type"=>"text","scope"=>"magicslideshow"),
				"orientation"=>array("id"=>"orientation","group"=>"Common settings","order"=>"30","default"=>"horizontal","label"=>"Slideshow direction","description"=>"vertical (up/down) / horizontal (right/left)","type"=>"array","subType"=>"radio","values"=>array("horizontal","vertical"),"scope"=>"magicslideshow"),
				"arrows"=>array("id"=>"arrows","group"=>"Common settings","order"=>"40","default"=>"No","label"=>"Show navigation arrows","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"loop"=>array("id"=>"loop","group"=>"Common settings","order"=>"45","default"=>"Yes","label"=>"Repeat slideshow after last slide","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"effect"=>array("id"=>"effect","group"=>"Common settings","order"=>"50","default"=>"slide","label"=>"Slide change effect","type"=>"array","subType"=>"select","values"=>array("slide","fade","fade-up","fade-down","dissolve","scroll","cube","bars3d","slide-in","slide-out","flip","blinds3d","slide-change","diffusion","blocks","random"),"scope"=>"magicslideshow"),
				"effect-speed"=>array("id"=>"effect-speed","group"=>"Common settings","order"=>"60","default"=>"600","label"=>"Slide-in duration (milliseconds)","description"=>"e.g. 0 = instant; 600 = 0.6 seconds","type"=>"num","scope"=>"magicslideshow"),
				"effect-easing"=>array("id"=>"effect-easing","group"=>"Common settings","order"=>"70","advanced"=>"1","default"=>"ease","label"=>"CSS3 Animation Easing","description"=>"ease | ease-in | ease-out | ease-in-out | linear | step-start | step-end | steps(n, start | end) | cubic-bezier(n, n, n, n)","type"=>"text","scope"=>"magicslideshow"),
				"autoplay"=>array("id"=>"autoplay","group"=>"Autoplay","order"=>"10","default"=>"Yes","label"=>"Autoplay slideshow","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"slide-duration"=>array("id"=>"slide-duration","group"=>"Autoplay","order"=>"20","default"=>"6000","label"=>"Display duration (milliseconds)","description"=>"e.g. 3000 = 3 seconds","type"=>"num","scope"=>"magicslideshow"),
				"shuffle"=>array("id"=>"shuffle","group"=>"Autoplay","order"=>"30","default"=>"No","label"=>"Shuffle order of slides","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"kenburns"=>array("id"=>"kenburns","group"=>"Autoplay","order"=>"40","default"=>"No","label"=>"Use Ken Burns effect","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"pause"=>array("id"=>"pause","group"=>"Autoplay","order"=>"50","default"=>"Yes","label"=>"Pause autoplay after click or hover","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"selectors-style"=>array("id"=>"selectors-style","group"=>"Selectors","order"=>"10","default"=>"bullets","label"=>"Selectors style","type"=>"array","subType"=>"radio","values"=>array("bullets","thumbnails"),"scope"=>"magicslideshow"),
				"selectors"=>array("id"=>"selectors","group"=>"Selectors","order"=>"20","default"=>"none","label"=>"Selectors position","type"=>"array","subType"=>"radio","values"=>array("bottom","top","right","left","none"),"scope"=>"magicslideshow"),
				"selectors-eye"=>array("id"=>"selectors-eye","group"=>"Selectors","order"=>"40","default"=>"Yes","label"=>"Highlight thumbnail when selected","description"=>"only available when 'selectors style' is set to thumbnails","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"bullets-preview"=>array("id"=>"bullets-preview","group"=>"Selectors","order"=>"45","default"=>"top","label"=>"Show tooltip on bullets","description"=>"","type"=>"array","subType"=>"radio","values"=>array("top","bottom","none"),"scope"=>"magicslideshow"),
				"selectors-fill"=>array("id"=>"selectors-fill","group"=>"Selectors","order"=>"50","default"=>"No","label"=>"Fit thumbnails","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"caption"=>array("id"=>"caption","group"=>"Caption","order"=>"10","default"=>"No","label"=>"Add caption under each image","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"fullscreen"=>array("id"=>"fullscreen","group"=>"Other settings","order"=>"10","default"=>"No","label"=>"Enable full-screen version of slideshow","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"preload"=>array("id"=>"preload","group"=>"Other settings","order"=>"20","default"=>"Yes","label"=>"Load images","description"=>"on page load / on demand","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"keyboard"=>array("id"=>"keyboard","advanced"=>"1","group"=>"Other settings","order"=>"30","default"=>"Yes","label"=>"Use keyboard arrows to move between slides","description"=>"always enabled in Full Screen mode","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"show-loader"=>array("id"=>"show-loader","group"=>"Other settings","order"=>"40","advanced"=>"1","default"=>"Yes","label"=>"Show loading progress bar","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"autostart"=>array("id"=>"autostart","advanced"=>"1","group"=>"Other settings","order"=>"50","default"=>"Yes","label"=>"Start Initialization on page load","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
				"link-to-product-page"=>array("id"=>"link-to-product-page","group"=>"Miscellaneous","order"=>"10","default"=>"Yes","label"=>"Link enlarged image to the product page","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
				"use-original-vm-thumbnails"=>array("id"=>"use-original-vm-thumbnails","group"=>"Miscellaneous","order"=>"20","default"=>"No","label"=>"Use original VirtueMart thumbnails?","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
				"show-message"=>array("id"=>"show-message","group"=>"Miscellaneous","order"=>"200","default"=>"No","label"=>"Show message under slideshow","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
				"message"=>array("id"=>"message","group"=>"Miscellaneous","order"=>"210","default"=>"","label"=>"Enter message to appear under slideshow","type"=>"text","scope"=>"module"),
				"imagemagick"=>array("id"=>"imagemagick","advanced"=>"1","group"=>"Miscellaneous","order"=>"550","default"=>"off","label"=>"Path to ImageMagick binaries (convert tool)","description"=>"You can set 'auto' to automatically detect ImageMagick location or 'off' to disable ImageMagick and use php GD lib instead","type"=>"text","scope"=>"module"),
				"image-quality"=>array("id"=>"image-quality","group"=>"Miscellaneous","order"=>"560","default"=>"75","label"=>"Quality of thumbnails and watermarked images (1-100)","description"=>"1 = worst quality / 100 = best quality","type"=>"num","scope"=>"module"),
				"watermark"=>array("id"=>"watermark","group"=>"Watermark","order"=>"10","default"=>"","label"=>"Watermark image path","description"=>"Enter location of watermark image on your server. Leave field empty to disable watermark","type"=>"text","scope"=>"module"),
				"watermark-max-width"=>array("id"=>"watermark-max-width","group"=>"Watermark","order"=>"20","default"=>"30%","label"=>"Maximum width of watermark image","description"=>"pixels = fixed size (e.g. 50) / percent = relative for image size (e.g. 50%)","type"=>"text","scope"=>"module"),
				"watermark-max-height"=>array("id"=>"watermark-max-height","group"=>"Watermark","order"=>"21","default"=>"30%","label"=>"Maximum height of watermark image","description"=>"pixels = fixed size (e.g. 50) / percent = relative for image size (e.g. 50%)","type"=>"text","scope"=>"module"),
				"watermark-opacity"=>array("id"=>"watermark-opacity","group"=>"Watermark","order"=>"40","default"=>"50","label"=>"Watermark image opacity (1-100)","description"=>"0 = transparent, 100 = solid color","type"=>"num","scope"=>"module"),
				"watermark-position"=>array("id"=>"watermark-position","group"=>"Watermark","order"=>"50","default"=>"center","label"=>"Watermark position","description"=>"Watermark size settings will be ignored when watermark position is set to 'stretch'","type"=>"array","subType"=>"select","values"=>array("top","right","bottom","left","top-left","bottom-left","top-right","bottom-right","center","stretch"),"scope"=>"module"),
				"watermark-offset-x"=>array("id"=>"watermark-offset-x","advanced"=>"1","group"=>"Watermark","order"=>"60","default"=>"0","label"=>"Watermark horizontal offset","description"=>"Offset from left and/or right image borders. Pixels = fixed size (e.g. 20) / percent = relative for image size (e.g. 20%). Offset will disable if 'watermark position' set to 'center'","type"=>"text","scope"=>"module"),
				"watermark-offset-y"=>array("id"=>"watermark-offset-y","advanced"=>"1","group"=>"Watermark","order"=>"70","default"=>"0","label"=>"Watermark vertical offset","description"=>"Offset from top and/or bottom image borders. Pixels = fixed size (e.g. 20) / percent = relative for image size (e.g. 20%). Offset will disable if 'watermark position' set to 'center'","type"=>"text","scope"=>"module")
			);
            $this->params->appendParams($params);
        }
    }

}

?>
