<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument head renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererHead extends JDocumentRenderer
{
	/**
	 * Renders the document head and returns the results as a string
	 *
	 * @param   string  $head     (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 *
	 * @note    Unused arguments are retained to preserve backward compatibility.
	 */
	public function render($head, $params = array(), $content = null)
	{
		return $this->fetchHead($this->_doc);
	}

	/**
	 * Generates the head HTML and return the results as a string
	 *
	 * @param   JDocument  $document  The document for which the head will be created
	 *
	 * @return  string  The head hTML
	 *
	 * @since   11.1
	 */
	public function fetchHead($document)
	{
		// Convert the tagids to titles
		if (isset($document->_metaTags['standard']['tags']))
		{
			$tagsHelper = new JHelperTags;
			$document->_metaTags['standard']['tags'] = implode(', ', $tagsHelper->getTagNames($document->_metaTags['standard']['tags']));
		}

		// Trigger the onBeforeCompileHead event
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeCompileHead');

		// Get line endings
		$lnEnd = $document->_getLineEnd();
		$tab = $document->_getTab();
		$tagEnd = ' />';
		$buffer = '';

		// Generate charset when using HTML5 (should happen first)
		if ($document->isHtml5())
		{
			$buffer .= $tab . '<meta charset="' . $document->getCharset() . '" />' . $lnEnd;
		}

		// Generate base tag (need to happen early)
		$base = $document->getBase();
		if (!empty($base))
		{
			$buffer .= $tab . '<base href="' . $document->getBase() . '" />' . $lnEnd;
		}

		// Generate META tags (needs to happen as early as possible in the head)
		foreach ($document->_metaTags as $type => $tag)
		{
			foreach ($tag as $name => $content)
			{
				if ($type == 'http-equiv' && !($document->isHtml5() && $name == 'content-type'))
				{
					$buffer .= $tab . '<meta http-equiv="' . $name . '" content="' . htmlspecialchars($content) . '" />' . $lnEnd;
				}
				elseif ($type == 'standard' && !empty($content))
				{
					$buffer .= $tab . '<meta name="' . $name . '" content="' . htmlspecialchars($content) . '" />' . $lnEnd;
				}
			}
		}

		// Don't add empty descriptions
		$documentDescription = $document->getDescription();
		if ($documentDescription)
		{
			$buffer .= $tab . '<meta name="description" content="' . htmlspecialchars($documentDescription) . '" />' . $lnEnd;
		}

		// Don't add empty generators
		$generator = $document->getGenerator();
		if ($generator)
		{
			$buffer .= $tab . '<meta name="generator" content="' . htmlspecialchars($generator) . '" />' . $lnEnd;
		}

		$buffer .= $tab . '<title>' . htmlspecialchars($document->getTitle(), ENT_COMPAT, 'UTF-8') . '</title>' . $lnEnd;

		// Generate link declarations
		foreach ($document->_links as $link => $linkAtrr)
		{
			$buffer .= $tab . '<link href="' . $link . '" ' . $linkAtrr['relType'] . '="' . $linkAtrr['relation'] . '"';
			if ($temp = JArrayHelper::toString($linkAtrr['attribs']))
			{
				$buffer .= ' ' . $temp;
			}
			$buffer .= ' />' . $lnEnd;
		}

		// Generate stylesheet links
		foreach ($document->_styleSheets as $strSrc => $strAttr)
		{
			$buffer .= $tab . '<link rel="stylesheet" href="' . $strSrc . '"';

			if (!is_null($strAttr['mime']) && (!$document->isHtml5() || $strAttr['mime'] != 'text/css'))
			{
				$buffer .= ' type="' . $strAttr['mime'] . '"';
			}

			if (!is_null($strAttr['media']))
			{
				$buffer .= ' media="' . $strAttr['media'] . '"';
			}

			if ($temp = JArrayHelper::toString($strAttr['attribs']))
			{
				$buffer .= ' ' . $temp;
			}

			$buffer .= $tagEnd . $lnEnd;
		}

		// Generate stylesheet declarations
		foreach ($document->_style as $type => $content)
		{
			$buffer .= $tab . '<style type="' . $type . '">' . $lnEnd;

			// This is for full XHTML support.
			if ($document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . '<![CDATA[' . $lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if ($document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . ']]>' . $lnEnd;
			}
			$buffer .= $tab . '</style>' . $lnEnd;
		}

		// Generate script file links
		foreach ($document->_scripts as $strSrc => $strAttr)
		{
			 // Code to disable mootools for your site (still loads it for your admin)
    // Written by Brad Markle w/ InMotionHosting.com
    $ex_src = explode("/",$strSrc);
    $js_file_name = $ex_src[count($ex_src)-1];
    $js_to_ignore = array("mootools-core.js","mootools-more.js","core.js","tabs-state.js","caption.js","jquery-noconflict.js","jquery-migrate.min.js");

    if( in_array($js_file_name,$js_to_ignore) AND substr_count($document->baseurl,"/administrator") < 1 AND isset($_GET['view']) AND $_GET['view'] != 'form')
        continue;
			$buffer .= $tab . '<script src="' . $strSrc . '"';
			$defaultMimes = array(
				'text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript'
			);

			if (!is_null($strAttr['mime']) && (!$document->isHtml5() || !in_array($strAttr['mime'], $defaultMimes)))
			{
				$buffer .= ' type="' . $strAttr['mime'] . '"';
			}

			if ($strAttr['defer'])
			{
				$buffer .= ' defer="defer"';
			}

			if ($strAttr['async'])
			{
				$buffer .= ' async="async"';
			}

			$buffer .= '></script>' . $lnEnd;
		}

		// Generate script declarations
		foreach ($document->_script as $type => $content)
		{
			$buffer .= $tab . '<script type="' . $type . '">' . $lnEnd;

			// This is for full XHTML support.
			if ($document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . '<![CDATA[' . $lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if ($document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . ']]>' . $lnEnd;
			}
			$buffer .= $tab . '</script>' . $lnEnd;
		}

		// Generate script language declarations.
		if (count(JText::script()))
		{
			$buffer .= $tab . '<script type="text/javascript">' . $lnEnd;
			$buffer .= $tab . $tab . '(function() {' . $lnEnd;
			$buffer .= $tab . $tab . $tab . 'var strings = ' . json_encode(JText::script()) . ';' . $lnEnd;
			$buffer .= $tab . $tab . $tab . 'if (typeof Joomla == \'undefined\') {' . $lnEnd;
			$buffer .= $tab . $tab . $tab . $tab . 'Joomla = {};' . $lnEnd;
			$buffer .= $tab . $tab . $tab . $tab . 'Joomla.JText = strings;' . $lnEnd;
			$buffer .= $tab . $tab . $tab . '}' . $lnEnd;
			$buffer .= $tab . $tab . $tab . 'else {' . $lnEnd;
			$buffer .= $tab . $tab . $tab . $tab . 'Joomla.JText.load(strings);' . $lnEnd;
			$buffer .= $tab . $tab . $tab . '}' . $lnEnd;
			$buffer .= $tab . $tab . '})();' . $lnEnd;
			$buffer .= $tab . '</script>' . $lnEnd;
		}

		foreach ($document->_custom as $custom)
		{
			$buffer .= $tab . $custom . $lnEnd;
		}

		return $buffer;
	}
}
