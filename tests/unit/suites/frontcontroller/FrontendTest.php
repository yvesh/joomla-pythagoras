<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Frontcontroller
 * @group       Frontcontroller
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_BASE . '/appropriate/directory/autoload.php';

/**
 * Class FrontendTest
 *
 * @since 4.0
 */
class FrontendTest extends PHPUnit_Framework_TestCase
{
	/** @var array */
	private $server = null;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->server = $_SERVER;
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = '';
		}
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$_SERVER = $this->server;
	}

	public function casesCliParameters()
	{
		return [
			'short-options' => [
				'argv'           => [
					'joomla',
					'-flg',
					'-a=shortOption',
				],
				'expectedParams' => [
					'script' => 'joomla',
					'f'      => true,
					'l'      => true,
					'g'      => true,
					'a'      => 'shortOption',
				],
			],
			'spaces'        => [
				'argv'           => [
					'joomla',
					'-a',
					'=',
					'shortOption',
					'argument with spaces'
				],
				'expectedParams' => [
					'script' => 'joomla',
					'a'      => 'shortOption',
					0        => 'argument with spaces'
				],
			],
			'long-options'  => [
				'argv'           => [
					'joomla',
					'--ansi',
					'--an=otherOption',
				],
				'expectedParams' => [
					'script' => 'joomla',
					'ansi'   => true,
					'an'     => 'otherOption',
				],
			],
			'app-command'   => [
				'argv'           => [
					'joomla',
					'application',
					'command',
					'--an=option',
					'argument',
				],
				'expectedParams' => [
					'script' => 'joomla',
					'option' => 'application',
					'task'   => 'command',
					'an'     => 'option',
					0        => 'argument',
				],
			],
			'stop-parse'    => [
				'argv'           => [
					'joomla',
					'--an=option',
					'--',
					'--argument',
				],
				'expectedParams' => [
					'script' => 'joomla',
					'an'     => 'option',
					0        => '--argument',
				],
			],
			'array-param'   => [
				'argv'           => [
					'joomla',
					'--an=option',
					'--an=otherOption',
					'--an=extraOption',
				],
				'expectedParams' => [
					'script' => 'joomla',
					'an'     => ['option', 'otherOption', 'extraOption']
				],
			],
		];
	}

	/**
	 * @dataProvider casesCliParameters
	 *
	 * @param array $argv
	 * @param array $expectedParams
	 */
	public function testCliParametersAreParsedCorrectly($argv, $expectedParams)
	{
		$parser = new Joomla\Cms\Cli\ArgvParser;
		$this->assertEquals($expectedParams, $parser->parse($argv));
	}

	public function casesApplications()
	{
		return [
			'site' => [
				'client' => 'site',
				'class' => 'JApplicationSite'
			],
			'admin' => [
				'client' => 'administrator',
				'class'  => 'JApplicationAdministrator'
			],
			/*
			 * JApplicationCli constructor violates the LSP
			 * 'cli' => [
			 * 	'client' => 'cli',
			 * 	'class'  => 'JApplicationCli'
			 * ],
			 */
			/*
			 * InstallationApplicationWeb does not follow naming convention
			 * 'install' => [
			 * 	'client' => 'installer',
			 * 	'class' => 'InstallationApplicationWeb'
			 * ],
			 */
		];
	}

	/**
	 * @dataProvider casesApplications
	 *
	 * @param array  $client
	 * @param string $expectedRenderer
	 */
	public function testApplicationFactoryCreatesTheAppropriateApplication($client, $expected)
	{
		$config = new \Joomla\Registry\Registry(['session' => false]);

		$factory = new Joomla\Cms\Application\Factory;

		/** @var \Joomla\Application\AbstractApplication $app */
		$app = $factory->create($client, ['argv' => array()], $config);

		$this->assertInstanceOf($expected, $app);
	}

	public function casesFactory()
	{
		return [
			'cli-default'  => [
				'server'   => [
					'argv' => explode(' ', 'joomla -flg --flag -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\PlainRenderer',
					'params'   => [
						'f'    => true,
						'l'    => true,
						'g'    => true,
						'flag' => true,
						'a'    => 'shortOption',
						'an'   => 'otherOption',
						0      => 'argument1',
						1      => 'argument2'
					]
				],
			],
			'cli-app'      => [
				'server'   => [
					'argv' => explode(' ', 'joomla application -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\PlainRenderer',
					'params'   => [
						'option' => 'com_application',
						'a'      => 'shortOption',
						'an'     => 'otherOption',
						0        => 'argument1',
						1        => 'argument2'
					]
				],
			],
			'cli-cmd'      => [
				'server'   => [
					'argv' => explode(' ', 'joomla application command -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\PlainRenderer',
					'params'   => [
						'option' => 'com_application',
						'task'   => 'command',
						'a'      => 'shortOption',
						'an'     => 'otherOption',
						0        => 'argument1',
						1        => 'argument2'
					]
				],
			],
			'cli-ansi'     => [
				'server'   => [
					'argv' => explode(' ', 'joomla application command --ansi -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\AnsiRenderer',
					'params'   => [
						'option' => 'com_application',
						'task'   => 'command',
						'a'      => 'shortOption',
						'an'     => 'otherOption',
						0        => 'argument1',
						1        => 'argument2'
					]
				],
			],
			'html-default' => [
				'server'   => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
					'params'   => []
				],
			],
			'html-index'   => [
				'server'   => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/index.php',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
					'params'   => []
				],
			],
			'json-default' => [
				'server'   => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'application/json',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\JsonRenderer',
					'params'   => []
				],
			],
			'xml-default'  => [
				'server'   => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'application/xml',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\XmlRenderer',
					'params'   => []
				],
			],

			/*
			 * User component
			 */
			'cli-user'     => [
				'server'   => [
					'argv' => explode(' ', 'joomla users --id=42'),
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\PlainRenderer',
					'params'   => [
						'option' => 'com_users',
						'id'     => '42'
					]
				],
			],
			'html-user'    => [
				'server'   => [
					'REQUEST_METHOD' => 'GET',
					'REQUEST_URI'    => '/index.php?option=com_users&task=view&id=42',
					'HTTP_ACCEPT'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
					'params'   => [
						'option' => 'com_users',
						'task'   => 'view',
						'id'     => '42'
					]
				],
			],
			'xml-user'     => [
				'server'   => [
					'REQUEST_METHOD' => 'GET',
					'REQUEST_URI'    => '/users/42',
					'HTTP_ACCEPT'    => 'application/xml',
				],
				'expected' => [
					'renderer' => 'Joomla\\Cms\\Renderer\\XmlRenderer',
					'params'   => [
						'option' => 'com_users',
						'path'   => '/users/42'
					]
				],
			],
		];
	}

	/**
	 * @dataProvider casesFactory
	 *
	 * @param array  $server
	 * @param string $expectedRenderer
	 */
	public function testApplicationFactoryInitializesTheApplicationProperly($server, $expected)
	{
		$config = new \Joomla\Registry\Registry(['session' => false]);

		$factory = new Joomla\Cms\Application\Factory;

		/** @var JApplicationSite $app */
		$app = $factory->create('site', $server, $config);

		$this->assertInstanceOf('JApplicationSite', $app);

		$renderer = $app->getRenderer();
		$this->assertInstanceOf($expected['renderer'], $renderer, "Expected class {$expected['renderer']}, got " . get_class($renderer));

		$params = $app->input->getArray();
		$this->assertEquals($expected['params'], $params);
	}

	public function casesAcceptHeaders()
	{
		return [
			[
				'header'   => 'Accept: text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5',
				'expected' => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
			],
			[
				'header'   => 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c',
				'expected' => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
			],
			[
				'header'   => 'application/xml',
				'expected' => 'Joomla\\Cms\\Renderer\\XmlRenderer',
			],
			[
				'header'   => 'application/xml;q=0.8, application/json; q=0.9',
				'expected' => 'Joomla\\Cms\\Renderer\\JsonRenderer',
			],
			[
				'header'   => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'expected' => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
			],
		];
	}

	/**
	 * @dataProvider casesAcceptHeaders
	 *
	 * @param string $header
	 * @param string $expectedRenderer
	 */
	public function testRendererFactoryCreatesTheAppropriateRenderer($header, $expectedRenderer)
	{
		$factory = new Joomla\Cms\Renderer\Factory;

		$renderer = $factory->create($header);
		$this->assertInstanceOf($expectedRenderer, $renderer);
	}

	private function ignore($arg)
	{
	}
}
