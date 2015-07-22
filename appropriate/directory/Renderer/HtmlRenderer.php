<?php
namespace Joomla\Cms\Renderer;

use Joomla\Cms\Renderer;
use Joomla\Cms\Http\Client\ScriptStrategy;

class HtmlRenderer extends Renderer
{
	/** @var  ScriptStrategy */
	private $clientScript;

	public function setScriptStrategy(ScriptStrategy $strategy)
	{
		$this->clientScript = $strategy;
	}
}
