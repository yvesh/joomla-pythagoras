<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Cms\Application;

use Joomla\Cms\Cli\ArgvParser;
use Joomla\String\Inflector;

/**
 * Joomla! Application Factory
 */
class Factory
{
	/** @var Inflector  */
	private $inflector;

	public function __construct()
	{
		$this->inflector = Inflector::getInstance();
	}

	/*
	 * This factory determines, which application to launch. It is responsible for
	 * providing the input to the application in an appropriate format.
	 */
	public function create($client, $server)
	{
		if (isset($server['argv']))
		{
			$vars = (new ArgvParser())->parse($server['argv']);

			unset($vars['script']);

			$option = 'default';
			if (isset($vars['option']))
			{
				$option = 'com_' . $this->pluralize($vars['option']);
				unset($vars['option']);
			}

			$task = 'view';
			if (isset($vars['task']))
			{
				$task = $vars['task'];
				unset($vars['task']);
			}

			$contentType = 'text/plain';
			if (isset($vars['ansi']))
			{
				$contentType = 'text/ansi';
				unset($vars['ansi']);
			}
		}
		elseif (isset($server['REQUEST_URI']))
		{
			$option = 'default';
			$task   = 'view';

			$uri = parse_url($server['REQUEST_URI']);

			$vars = array();
			if (isset($uri['query']))
			{
				parse_str($uri['query'], $vars);
				if (isset($vars['option']))
				{
					$option = $vars['option'];
					unset($vars['option']);
				}
				if (isset($vars['task']))
				{
					$task = $vars['task'];
					unset($vars['task']);
				}
			}

			$segments = array_values(array_filter(explode('/', $uri['path'])));
			if (count($segments) > 0 && $segments[0] == 'index.php')
			{
				array_shift($segments);
			}
			if (count($segments) > 0)
			{
				$option = 'com_' . $this->pluralize(array_shift($segments));
			}
			if (count($segments) > 0)
			{
				$vars['id'] = array_shift($segments);
			}

			if (isset($server['REQUEST_METHOD']))
			{
				switch ($server['REQUEST_METHOD'])
				{
					case 'GET':
						$task = 'browse';
						if (isset($segments[0]) || isset($vars['id']))
						{
							$task = 'view';
						}
						break;
					case 'POST':
						$task = 'save';
						break;
					case 'DELETE':
						$task = 'delete';
						break;
					default:
						break;
				}
			}
			$contentType = isset($server['HTTP_ACCEPT']) ? $server['HTTP_ACCEPT'] : 'text/html';
		}
		else
		{
			throw new \InvalidArgumentException('Unsupported application type');
		}

		$renderer = (new \Joomla\Cms\Renderer\Factory)->create($contentType);

		$application = new Application($client);
		$application->setRenderer($renderer);
		$application->setOption($option);
		$application->setTask($task);
		$application->setParams($vars);

		return $application;
	}

	/**
	 * @param $word
	 *
	 * @return mixed
	 */
	private function pluralize($word)
	{
		return $this->inflector->isPlural($word) ? $word : $this->inflector->toPlural($word);
	}
}
