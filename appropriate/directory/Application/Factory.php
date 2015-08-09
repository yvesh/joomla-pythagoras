<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Cms\Application;

use Joomla\Application\AbstractApplication;
use Joomla\Cms\Cli\ArgvParser;

/**
 * Joomla! Application Factory
 */
class Factory
{
	/**
	 * @param string $client
	 * @param array  $server
	 * @param array  $config
	 *
	 * @return AbstractApplication
	 */
	public function create($client, $server, $config = [])
	{
		if (isset($server['argv']))
		{
			$vars        = $this->getVarsFromCommandLine($server);
			$contentType = $this->getCliContentType($vars);
		}
		elseif (isset($server['REQUEST_URI']))
		{
			$vars        = $this->getVarsFromServer($server);
			$contentType = $this->getHttpContentType($server);
		}
		else
		{
			throw new \InvalidArgumentException('Unsupported application type');
		}

		if (isset($vars['option']) && !preg_match('~^com_~', $vars['option']))
		{
			$vars['option'] = 'com_' . $vars['option'];
		}

		$renderer = (new \Joomla\Cms\Renderer\Factory)->create($contentType);

		$appClass = 'JApplication' . ucfirst(strtolower($client));

		/** @var AbstractApplication $app */
		$app = new $appClass(new \JInput($vars), $config);
		$app->setRenderer($renderer);

		return $app;
	}

	/**
	 * @param $server
	 *
	 * @return array
	 */
	private function getVarsFromCommandLine($server)
	{
		$vars = (new ArgvParser())->parse($server['argv']);

		unset($vars['script']);

		return $vars;
	}

	/**
	 * @param $vars
	 *
	 * @return array
	 */
	private function getCliContentType(&$vars)
	{
		$contentType = 'text/plain';
		if (isset($vars['ansi']))
		{
			$contentType = 'text/ansi';
			unset($vars['ansi']);
		}

		return $contentType;
	}

	/**
	 * @param $server
	 *
	 * @return array
	 */
	private function getVarsFromServer($server)
	{
		$uri = parse_url($server['REQUEST_URI']);

		$vars = array();
		if (isset($uri['query']))
		{
			parse_str($uri['query'], $vars);
		}

		$segments = array_values(array_filter(explode('/', $uri['path'])));
		if (count($segments) > 0 && $segments[0] == 'index.php')
		{
			array_shift($segments);
		}
		if (count($segments) > 0)
		{
			$vars['option'] = array_shift($segments);
		}
		if (count($segments) > 0)
		{
			$vars['path'] = $uri['path'];

			return $vars;
		}

		return $vars;
	}

	/**
	 * @param $server
	 *
	 * @return string
	 */
	private function getHttpContentType($server)
	{
		return isset($server['HTTP_ACCEPT']) ? $server['HTTP_ACCEPT'] : 'text/html';
	}
}
