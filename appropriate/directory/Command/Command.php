<?php
namespace Joomla\Command;

use Joomla\Cms\Input;
use Joomla\Cms\Output;

interface Command
{
	public function getName();
	public function getDescription();
	public function execute(Input $input, Output $output);
	public function setDispatcher(Dispatcher $dispatcher);
}
