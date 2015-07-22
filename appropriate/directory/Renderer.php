<?php
namespace Joomla\Cms;

abstract class Renderer
{
	protected $options;

	public function __construct($options)
	{
		$this->options = $options;
	}
}
