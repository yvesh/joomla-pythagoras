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
	/** @var Joomla\Cms\Application\Factory */
	private $applicationFactory = null;

	/**
	 * This method is called before the first test of this test class is run.
	 */
	public static function setUpBeforeClass()
	{
	}

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
	}

	/**
	 * Performs assertions shared by all tests of a test case.
	 *
	 * This method is called before the execution of a test starts
	 * and after setUp() is called.
	 */
	protected function assertPreConditions()
	{
	}

	/**
	 * Performs assertions shared by all tests of a test case.
	 *
	 * This method is called before the execution of a test ends
	 * and before tearDown() is called.
	 */
	protected function assertPostConditions()
	{
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * This method is called after the last test of this test class is run.
	 */
	public static function tearDownAfterClass()
	{
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
					'f'    => true,
					'l'    => true,
					'g'    => true,
					'a'    => 'shortOption',
				],
			],
			'spaces' => [
				'argv'           => [
					'joomla',
					'-a',
					'=',
					'shortOption',
					'argument with spaces'
				],
				'expectedParams' => [
					'script' => 'joomla',
					'a'    => 'shortOption',
					0      => 'argument with spaces'
				],
			],
			'long-options' => [
				'argv'           => [
					'joomla',
					'--ansi',
					'--an=otherOption',
				],
				'expectedParams' => [
					'script' => 'joomla',
					'ansi' => true,
					'an' => 'otherOption',
				],
			],
			'app-command' => [
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
					'task' => 'command',
					'an'   => 'option',
					0      => 'argument',
				],
			],
			'stop-parse' => [
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
			'array-param' => [
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

	public function casesFactory()
	{
		return [
			'cli-default'  => [
				'server'            => [
					'argv' => explode(' ', 'joomla -flg --flag -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\PlainRenderer',
				'expectedComponent' => 'default',
				'expectedTask'      => 'view',
				'expectedParams'    => [
					'f'    => true,
					'l'    => true,
					'g'    => true,
					'flag' => true,
					'a'    => 'shortOption',
					'an'   => 'otherOption',
					0      => 'argument1',
					1      => 'argument2'
				],
			],
			'cli-app'      => [
				'server'            => [
					'argv' => explode(' ', 'joomla application -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\PlainRenderer',
				'expectedComponent' => 'com_applications',
				'expectedTask'      => 'view',
				'expectedParams'    => [
					'a'  => 'shortOption',
					'an' => 'otherOption',
					0    => 'argument1',
					1    => 'argument2'
				],
			],
			'cli-cmd'      => [
				'server'            => [
					'argv' => explode(' ', 'joomla application command -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\PlainRenderer',
				'expectedComponent' => 'com_applications',
				'expectedTask'      => 'command',
				'expectedParams'    => [
					'a'  => 'shortOption',
					'an' => 'otherOption',
					0    => 'argument1',
					1    => 'argument2'
				],
			],
			'cli-ansi'     => [
				'server'            => [
					'argv' => explode(' ', 'joomla application command --ansi -a=shortOption --an=otherOption argument1 argument2'),
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\AnsiRenderer',
				'expectedComponent' => 'com_applications',
				'expectedTask'      => 'command',
				'expectedParams'    => [
					'a'  => 'shortOption',
					'an' => 'otherOption',
					0    => 'argument1',
					1    => 'argument2'
				],
			],
			'html-default' => [
				'server'            => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
				'expectedComponent' => 'default',
				'expectedTask'      => 'browse',
				'expectedParams'    => [],
			],
			'html-index'   => [
				'server'            => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/index.php',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
				'expectedComponent' => 'default',
				'expectedTask'      => 'browse',
				'expectedParams'    => [],
			],
			'json-default' => [
				'server'            => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'application/json',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\JsonRenderer',
				'expectedComponent' => 'default',
				'expectedTask'      => 'browse',
				'expectedParams'    => [],
			],
			'xml-default'  => [
				'server'            => [
					'REQUEST_METHOD'       => 'GET',
					'REQUEST_URI'          => '/',
					'SERVER_PROTOCOL'      => 'HTTP/1.1',
					'HTTP_ACCEPT'          => 'application/xml',
					'HTTP_ACCEPT_LANGUAGE' => 'de,en-US;q=0.7,en;q=0.3',
					'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\XmlRenderer',
				'expectedComponent' => 'default',
				'expectedTask'      => 'browse',
				'expectedParams'    => [],
			],
			/*
			 * User component
			 */

			'cli-user'     => [
				'server'            => [
					'argv' => explode(' ', 'joomla user --id=42'),
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\PlainRenderer',
				'expectedComponent' => 'com_users',
				'expectedTask'      => 'view',
				'expectedParams'    => [
					'id' => '42'
				],
			],
			'html-user'    => [
				'server'            => [
					'REQUEST_METHOD' => 'GET',
					'REQUEST_URI'    => '/index.php?option=com_users&task=view&id=42',
					'HTTP_ACCEPT'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\HtmlRenderer',
				'expectedComponent' => 'com_users',
				'expectedTask'      => 'view',
				'expectedParams'    => [
					'id' => '42'
				],
			],
			'xml-user'     => [
				'server'            => [
					'REQUEST_METHOD' => 'GET',
					'REQUEST_URI'    => '/users/42',
					'HTTP_ACCEPT'    => 'application/xml',
				],
				'expectedRenderer'  => 'Joomla\\Cms\\Renderer\\XmlRenderer',
				'expectedComponent' => 'com_users',
				'expectedTask'      => 'view',
				'expectedParams'    => [
					'id' => '42'
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
	public function testApplicationFactoryCreatesTheAppropriateApplication($server, $expectedRenderer, $expectedComponent, $expectedTask, $expectedParams)
	{
		$factory = new Joomla\Cms\Application\Factory;
		$app     = $factory->create('site', $server);

		$this->assertInstanceOf('Joomla\\Cms\\Application\\Application', $app, "App is not an application");

		$renderer = $app->getRenderer();
		$this->assertInstanceOf($expectedRenderer, $renderer, "Expected class $expectedRenderer, got " . get_class($renderer));

		$option = $app->getOption();
		$this->assertEquals($expectedComponent, $option, "Expected option $expectedComponent, got $option");

		$task = $app->getTask();
		$this->assertEquals($expectedTask, $task, "Expected task $expectedTask, got $task");

		$params = $app->getParams();
		$this->assertEquals($expectedParams, $params);
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
