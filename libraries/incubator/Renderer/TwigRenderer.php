<?php
/**
 * Part of the Joomla Framework Renderer Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Renderer;

use Joomla\Content\Type\Attribution;
use Joomla\Content\Type\Compound;
use Joomla\Content\Type\Headline;
use Joomla\Content\Type\Paragraph;

/**
 * Class TwigRenderer
 *
 * @package  Joomla/Renderer
 *
 * @since    1.0
 */
class TwigRenderer extends Renderer
{
	/** @var string The MIME type */
	protected $mediatype = 'text/html';

	/** @var  ScriptStrategyInterface */
	private $clientScript;

	private $twig;

	private $replacements = array();

	/**
	 * TwigRenderer constructor. Initializes the twig enviornment
	 */
	public function __construct($options)
	{
		// This should be moved to a twig template file sometime
		$loader = new \Twig_Loader_Array(array(
			'article' => '{{ headline | raw }} <p><small>{{attribution}}</small></p> <p>{{paragraph | raw}}</p>',
		));

		$this->twig = new \Twig_Environment($loader, array(
		));

		parent::__construct($options);
	}


	/**
	 * @param   ScriptStrategyInterface  $strategy  The scripting startegy (library) to use
	 *
	 * @return  void
	 */
	public function setScriptStrategy(ScriptStrategyInterface $strategy)
	{
		$this->clientScript = $strategy;
	}

	/**
	 * @return  array
	 */
	protected function collectMetadata()
	{
		$metaData                                  = parent::collectMetadata();
		$metaData['wrapper_data']['client_script'] = empty($this->clientScript) ? null : get_class($this->clientScript);

		return $metaData;
	}

	/**
	 * Render a headline.
	 *
	 * @param   Headline $headline The headline
	 *
	 * @return  integer Number of bytes written to the output
	 */
	public function visitHeadline(Headline $headline)
	{
		return $this->add('headline', "<h{$headline->level}>{$headline->text}</h{$headline->level}>\n");
	}

	/**
	 * Render an attribution to an author
	 *
	 * @param   Attribution $attribution The attribution
	 *
	 * @return  integer Number of bytes written to the output
	 */
	public function visitAttribution(Attribution $attribution)
	{
		return $this->add('attribution', "{$attribution->label} {$attribution->name}\n");
	}

	/**
	 * Render a paragraph
	 *
	 * @param   Paragraph $paragraph The paragraph
	 *
	 * @return  integer Number of bytes written to the output
	 */
	public function visitParagraph(Paragraph $paragraph)
	{
		$text = $paragraph->text;

		switch ($paragraph->variant)
		{
			case Paragraph::EMPHASISED:
				$text = "<em>{$text}</em>";
				break;
		}

		return $this->add('paragraph', "{$text}\n");
	}

	/**
	 * Render a compound (block) element
	 *
	 * @param   Compound $compound The compound
	 *
	 * @return  integer Number of bytes written to the output
	 */
	public function visitCompound(Compound $compound)
	{
		$this->write("<{$compound->type}>\n");

		foreach ($compound->items as $item)
		{
			$item->accept($this);
		}

		$this->write("</{$compound->type}>\n");

		$html = $this->twig->render("article", $this->replacements);

		$this->output .= $html;

		return strlen($html);
	}

	/**
	 * Add replacement for twig
	 *
	 * @param   string  $key      Replacement key {{ key }}
	 * @param   string  $content  The inner content
	 *
	 * @return  int Number of bytes added
	 */
	public function add($key, $content)
	{
		$this->replacements[$key] = $content;

		return strlen($content);
	}
}
