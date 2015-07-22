<?php
namespace Joomla\Content;

use Joomla\Cms\Renderer;

interface Content
{
	public function accept(Renderer $renderer);
}
