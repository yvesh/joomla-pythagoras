<?php
/**
 * Part of the Joomla Framework Renderer Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Renderer;

use Joomla\Http\Header\AcceptHeader;
use Joomla\Renderer\Exception\NotFoundException;

/**
 * Class Factory
 *
 * @package  Joomla/Renderer
 *
 * @since    1.0
 */
class Factory
{
	/** @var array Mapping of MIME types to matching renderers */
	protected $mediaTypeMap = [
		// CLI formats
		'text/plain'                        => 'PlainRenderer',
		'text/ansi'                         => 'AnsiRenderer',

		// REST formats
		'application/xml'                   => 'XmlRenderer',
		'application/json'                  => 'JsonRenderer',

		// Web/Office formats
		'text/html'                         => array('HtmlRenderer', 'TwigRenderer'),
		'application/pdf'                   => 'PdfRenderer',

		// The DocBook format seems not to be registered. @link http://wiki.docbook.org/DocBookMimeType
		'application/docbook+xml'           => 'DocbookRenderer',
		'application/vnd.oasis.docbook+xml' => 'DocbookRenderer',
		'application/x-docbook'             => 'DocbookRenderer',
	];

	/**
	 * @param   string  $acceptHeader  The 'Accept' header
	 *
	 * @return  mixed
	 */
	public function create($acceptHeader = '*/*')
	{
		$header = new AcceptHeader($acceptHeader);

		$match = $header->getBestMatch(array_keys($this->mediaTypeMap));

		if (!isset($match['token']))
		{
			throw(new NotFoundException("No matching renderer found for\n\t$acceptHeader"));
		}

		$renderer = $this->mediaTypeMap[$match['token']];

		// Just a draft to have multiple renderer
		if (is_array($renderer))
		{
			if (isset($_REQUEST['renderer']))
			{
				$renderer = $renderer[(int) $_REQUEST['renderer']];
			}
			else
			{
				// Take the first one
				$renderer = $renderer[0];
			}
		}

		$classname = __NAMESPACE__ . '\\' . $renderer;

		return new $classname($match);
	}
}
