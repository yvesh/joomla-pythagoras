<?php
namespace Joomla\Cms\Cli;

class ArgvParser
{
	public function parse($argv)
	{
		$args = [];

		$args['script'] = array_shift($argv);

		if (!empty($argv) && !$this->isOption($argv[0]))
		{
			$args['option'] = array_shift($argv);

			if (!empty($argv) && !$this->isOption($argv[0]))
			{
				$args['task'] = array_shift($argv);
			}
		}

		while (!empty($argv))
		{
			$arg = array_shift($argv);

			if ($arg == '--')
			{
				while (!empty($argv))
				{
					$args[] = array_shift($argv);
				}
			}
			elseif ($this->isShortOption($arg))
			{
				$arg = $this->completizeOption($arg, $argv);
				$arg = substr($arg, 1);
				$tmp = explode('=', $arg, 2);
				while (strlen($tmp[0]) > 1)
				{
					$args[$tmp[0][0]] = true;
					$tmp[0] = substr($tmp[0], 1);
				}
				$args = $this->addArg($args, $tmp);
			}
			elseif ($this->isLongOption($arg))
			{
				$arg = $this->completizeOption($arg, $argv);
				$arg = substr($arg, 2);
				$tmp = explode('=', $arg, 2);
				$args = $this->addArg($args, $tmp);
			}
			else
			{
				$args[] = $arg;
			}
		}
		return $args;
	}

	/**
	 * Completize an option
	 *
	 * If the next parameter is or starts with an equal sign, we just got the key,
	 * and the next parameter belongs to the current option.
	 * If the current option ends with an equal sign, the next parameter is the
	 * value for this option.
	 *
	 * @param string $arg
	 * @param array  $argv
	 *
	 * @return array
	 */
	private function completizeOption($arg, &$argv)
	{
		if (!empty($argv))
		{
			if ($argv[0] > '' && $argv[0][0] == '=')
			{
				$arg .= array_shift($argv);
			}
			if (preg_match('~=$~', $arg))
			{
				$arg .= array_shift($argv);
			}
		}

		return $arg;
	}

	/**
	 * @param string $arg
	 *
	 * @return bool
	 */
	private function isOption($arg)
	{
		return $this->isShortOption($arg) || $this->isLongOption($arg);
	}

	/**
	 * @param string $arg
	 *
	 * @return bool
	 */
	private function isShortOption($arg)
	{
		return preg_match('~^-\w+~', $arg);
	}

	/**
	 * @param string $arg
	 *
	 * @return bool
	 */
	private function isLongOption($arg)
	{
		return preg_match('~^--\w+~', $arg);
	}

	private function addArg($args, $tmp)
	{
		$key = $tmp[0];

		if (count($tmp) == 2)
		{
			$value = $tmp[1];

			if (isset($args[$key]))
			{
				$args[$key] = (array)$args[$key];
				$args[$key][] = $value;
			}
			else
			{
				$args[$key] = $value;
			}
		}
		else
		{
			$args[$key] = true;
		}

		return $args;
	}
}
