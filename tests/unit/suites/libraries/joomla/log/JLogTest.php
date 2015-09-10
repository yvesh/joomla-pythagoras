<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/log/inspector.php';

/**
 * Test class for JLog.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		// Clear out the log instance.
		$log = new JLogInspector;
		JLog::setInstance($log);

		parent::tearDown();
	}

	/**
	 * Test the JLog::addLogEntry method to verify that if called directly it will route the entry to the
	 * appropriate loggers.  We use the echo logger here for easy testing using the PHP output buffer.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testAddLogEntry()
	{
		// First let's test a set of priorities.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a loggers to the JLog object.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$this->expectOutputString("DEBUG: TESTING [deprecated]\n");
		$log->addLogEntry(new JLogEntry('TESTING', JLog::DEBUG, 'DePrEcAtEd'));
	}

	/**
	 * Test that if JLog::addLogger is called and no JLog instance has been instantiated yet, that one will
	 * be instantiated automatically and the logger will work accordingly.  We use the echo logger here for
	 * easy testing using the PHP output buffer.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testAddLoggerAutoInstantiation()
	{
		JLog::setInstance(null);

		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$this->expectOutputString("WARNING: TESTING [deprecated]\n");
		JLog::add(new JLogEntry('TESTING', JLog::WARNING, 'DePrEcAtEd'));
	}

	/**
	 * Test that if JLog::addLogger is called and no JLog instance has been instantiated yet, that one will
	 * be instantiated automatically and the logger will work accordingly.  We use the echo logger here for
	 * easy testing using the PHP output buffer.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testAddLoggerAutoInstantiationInvalidLogger()
	{
		// We are expecting a InvalidArgumentException to be thrown since we are trying to add a bogus logger.
		$this->setExpectedException('RuntimeException');

		JLog::setInstance(null);

		JLog::addLogger(array('logger' => 'foobar'), JLog::ALL);

		JLog::add(new JLogEntry('TESTING', JLog::WARNING, 'DePrEcAtEd'));
	}

	/**
	 * Test the JLog::findLoggers method to make sure given a category we are finding the correct loggers that
	 * have been added to JLog.  It is important to note that if a logger was added with no category, then it
	 * will be returned for all categories.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testFindLoggersByCategory()
	{
		$log = new JLogInspector;
		JLog::setInstance($log);

		$logger1 = JLog::addLogger(array('text_file' => 'deprecated.log'), JLog::ALL, 'deprecated');
		$logger2 = JLog::addLogger(array('text_file' => 'com_foo.log'), JLog::ALL, 'com_foo');
		$logger3 = JLog::addLogger(array('text_file' => 'none.log'), JLog::ALL);
		$logger4 = JLog::addLogger(array('text_file' => 'deprecated-com_foo.log'), JLog::ALL, array('deprecated', 'com_foo'));
		$logger5 = JLog::addLogger(array('text_file' => 'foobar-deprecated.log'), JLog::ALL, array('foobar', 'deprecated'));
		$logger6 = JLog::addLogger(array('text_file' => 'transactions-paypal.log'), JLog::ALL, array('transactions', 'paypal'));
		$logger7 = JLog::addLogger(array('text_file' => 'transactions.log'), JLog::ALL, array('transactions'));

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					$logger1,
					$logger3,
					$logger4,
					$logger5,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array(
					$logger3,
					$logger6,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					$logger2,
					$logger3,
					$logger4
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::WARNING, 'transactions'),
			$this->equalTo(
				array(
					$logger3,
					$logger6,
					$logger7,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

	}

	/**
	 * Test the JLog::findLoggers method to make sure given a category we are finding the correct loggers that
	 * have been added to JLog (using exclusion).  It is important to note that empty category can also be excluded.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testFindLoggersByNotCategory()
	{
		$log = new JLogInspector;
		JLog::setInstance($log);

		$logger1 = JLog::addLogger(array('text_file' => 'not_deprecated.log'), JLog::ALL, 'deprecated', true);
		$logger2 = JLog::addLogger(array('text_file' => 'not_com_foo.log'), JLog::ALL, 'com_foo', true);
		$logger3 = JLog::addLogger(array('text_file' => 'not_none.log'), JLog::ALL, '', true);
		$logger4 = JLog::addLogger(array('text_file' => 'not_deprecated-com_foo.log'), JLog::ALL, array('deprecated', 'com_foo'), true);
		$logger5 = JLog::addLogger(array('text_file' => 'not_foobar-deprecated.log'), JLog::ALL, array('foobar', 'deprecated'), true);
		$logger6 = JLog::addLogger(array('text_file' => 'not_transactions-paypal.log'), JLog::ALL, array('transactions', 'paypal'), true);
		$logger7 = JLog::addLogger(array('text_file' => 'not_transactions.log'), JLog::ALL, array('transactions'), true);

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					$logger2,
					$logger3,
					$logger6,
					$logger7,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array(
					$logger1,
					$logger2,
					$logger3,
					$logger4,
					$logger5,
					$logger7
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					$logger1,
					$logger3,
					$logger5,
					$logger6,
					$logger7
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::WARNING, 'transactions'),
			$this->equalTo(
				array(
					$logger1,
					$logger2,
					$logger3,
					$logger4,
					$logger5
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::INFO, ''),
			$this->equalTo(
				array(
					$logger1,
					$logger2,
					$logger4,
					$logger5,
					$logger6,
					$logger7
				)
			),
			'Line: ' . __LINE__ . '.'
		);

	}

	/**
	 * Test the JLog::findLoggers method to make sure given a priority we are finding the correct loggers that
	 * have been added to JLog.  It is important to test not only straight values but also bitwise combinations
	 * and the catch all JLog::ALL as registered loggers.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testFindLoggersByPriority()
	{
		$log = new JLogInspector;
		JLog::setInstance($log);

		$logger1 = JLog::addLogger(array('text_file' => 'error.log'), JLog::ERROR);
		$logger2 = JLog::addLogger(array('text_file' => 'notice.log'), JLog::NOTICE);
		$logger3 = JLog::addLogger(array('text_file' => 'warning.log'), JLog::WARNING);
		$logger4 = JLog::addLogger(array('text_file' => 'error_warning.log'), JLog::ERROR | JLog::WARNING);
		$logger5 = JLog::addLogger(array('text_file' => 'all.log'), JLog::ALL);
		$logger6 = JLog::addLogger(array('text_file' => 'all_except_debug.log'), JLog::ALL & ~JLog::DEBUG);

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, null),
			$this->equalTo(
				array(
					$logger5,
					$logger6,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, null),
			$this->equalTo(
				array(
					$logger2,
					$logger5,
					$logger6
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, null),
			$this->equalTo(
				array(
					$logger5
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::WARNING, null),
			$this->equalTo(
				array(
					$logger3,
					$logger4,
					$logger5,
					$logger6
				)
			),
			'Line: ' . __LINE__ . '.'
		);

	}

	/**
	 * Test the JLog::findLoggers method to make sure given a priority and category we are finding the correct
	 * loggers that have been added to JLog.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testFindLoggersByPriorityAndCategory()
	{
		$log = new JLogInspector;
		JLog::setInstance($log);

		$logger1 = JLog::addLogger(array('text_file' => 'deprecated.log'), JLog::ALL, 'deprecated');
		$logger2 = JLog::addLogger(array('text_file' => 'com_foo.log'), JLog::DEBUG, 'com_foo');
		$logger3 = JLog::addLogger(array('text_file' => 'none.log'), JLog::ERROR | JLog::CRITICAL | JLog::EMERGENCY);
		$logger4 = JLog::addLogger(array('text_file' => 'deprecated-com_foo.log'), JLog::NOTICE | JLog::WARNING, array('deprecated', 'com_foo'));
		$logger5 = JLog::addLogger(array('text_file' => 'transactions-paypal.log'), JLog::INFO, array('transactions', 'paypal'));
		$logger6 = JLog::addLogger(array('text_file' => 'transactions.log'), JLog::ERROR, array('transactions'));

		$this->assertThat(
			$log->findLoggers(JLog::EMERGENCY, 'deprecated'),
			$this->equalTo(
				array(
					$logger1,
					$logger3,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::NOTICE, 'paypal'),
			$this->equalTo(
				array()
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::DEBUG, 'com_foo'),
			$this->equalTo(
				array(
					$logger2,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::ERROR, 'transactions'),
			$this->equalTo(
				array(
					$logger3,
					$logger6,
				)
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$log->findLoggers(JLog::INFO, 'transactions'),
			$this->equalTo(
				array(
					$logger5,
				)
			),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JLog::setInstance method to make sure that if we set a logger instance JLog is actually going
	 * to use it.  We accomplish this by setting an instance of JLogInspector and then performing some
	 * operations using JLog::addLogger() to alter the state of the internal instance.  We then check that the
	 * JLogInspector instance we created (and set) has the same values we would expect for lookup and configuration
	 * so we can assert that the operations we performed using JLog::addLogger() were actually performed on our
	 * instance of JLogInspector that was set.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testSetInstance()
	{
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a logger to the JLog object.
		$logger1 = JLog::addLogger(array('logger' => 'w3c'));

		// Get the expected configurations array after adding the single logger.
		$expectedConfigurations = array(
			$logger1 => array('logger' => 'w3c')
		);

		// Get the expected lookup array after adding the single logger.
		$expectedLookup = array(
			$logger1 => (object) array('priorities' => JLog::ALL, 'categories' => array(), 'exclude' => false)
		);

		// Get the expected loggers array after adding the single logger (hasn't been instantiated yet so null).
		$expectedLoggers = null;

		$this->assertEquals(
			$expectedConfigurations,
			$log->configurations
		);

		$this->assertEquals(
			$expectedLookup,
			$log->lookup
		);

		$this->assertEquals(
			$expectedLoggers,
			$log->loggers
		);

		// Start over so we test that it actually sets the instance appropriately.
		$log = new JLogInspector;
		JLog::setInstance($log);

		// Add a logger to the JLog object.
		$logger2 = JLog::addLogger(array('logger' => 'database', 'db_type' => 'mysqli', 'db_table' => '#__test_table'), JLog::ERROR);

		// Get the expected configurations array after adding the single logger.
		$expectedConfigurations = array(
			$logger2 => array('logger' => 'database', 'db_type' => 'mysqli', 'db_table' => '#__test_table')
		);

		// Get the expected lookup array after adding the single logger.
		$expectedLookup = array(
			$logger2 => (object) array('priorities' => JLog::ERROR, 'categories' => array(), 'exclude' => false)
		);

		// Get the expected loggers array after adding the single logger (hasn't been instantiated yet so null).
		$expectedLoggers = null;

		$this->assertEquals(
			$expectedConfigurations,
			$log->configurations
		);

		$this->assertEquals(
			$expectedLookup,
			$log->lookup
		);

		$this->assertEquals(
			$expectedLoggers,
			$log->loggers
		);
	}
}
