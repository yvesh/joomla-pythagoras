<?php
namespace Joomla\Command;

use Joomla\Cms\Input;
use Joomla\Cms\Output;

class CommandProcessor
{
	/** @var  Dispatcher */
	protected $dispatcher;

	public function executeCommand(Command $command, Input $input, Output $output)
	{
		if ($this->dispatcher->trigger('beforeExecute', $command))
		{
			$command->setDispatcher($this->dispatcher);
			$command->execute($input, $output);
			$this->dispatcher->trigger('afterExecute', $command);
		}
	}
}
