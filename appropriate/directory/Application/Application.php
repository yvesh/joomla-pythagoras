<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Cms\Application;

/**
 * Joomla! Application
 */
class Application
{
	/** @var  \Joomla\Cms\Renderer */
	private $renderer;

	private $option = '';

	private $task = '';

	private $params = [];

	public function run()
	{
		// Do something

		$this->render();
	}

	protected function render()
	{
		return 'content';
	}

	/**
	 * @return string
	 */
	public function getOption()
	{
		return $this->option;
	}

	/**
	 * @param string $option
	 *
	 * @return Application
	 */
	public function setOption($option)
	{
		$this->option = $option;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * @param string $task
	 *
	 * @return Application
	 */
	public function setTask($task)
	{
		$this->task = $task;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @param array $params
	 *
	 * @return Application
	 */
	public function setParams($params)
	{
		$this->params = $params;

		return $this;
	}

	/**
	 * @return \Joomla\Cms\Renderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * @param \Joomla\Cms\Renderer $renderer
	 *
	 * @return Application
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}
}
