<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version		1.2.12
 * @license		GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright	(C) 2008-2015 Kuneri Ltd.
 * @date		March 2015
 */
defined('_JEXEC') or die('Restricted access');

function modChrome_iphone($module, &$params, &$attribs)
{
	/** @var JRegistry $params */
	if(!empty($module->content))
	{
		?><div class="container moduletable<?php echo $params->get('moduleclass_sfx'); ?>"><?php
		if($module->showtitle)
		{
			?><div class="h3"><?php echo $module->title; ?></div><?php
		}
		echo $module->content;
		?></div><?php
	}
}
