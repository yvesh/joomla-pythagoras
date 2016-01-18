<?php

namespace Joomla\Tests\System;

use AcceptanceTester;
use Codeception\Util\Shared\Asserts;
use Facebook\WebDriver\WebDriver;
use Joomla\Tests\Page\DumpTrait;
use Joomla\Tests\Page\Page;
use Joomla\Tests\Page\PageFactory;

class CPanelCest
{
    use Asserts;
    use DumpTrait;

    /** @var  WebDriver */
    private $driver;

    /** @var  Page */
    private $page;

    public function _before(AcceptanceTester $I)
    {
        $this->driver = $I->getWebDriver();
        $this->page = (new PageFactory($I, 'Hathor'))->create('CPanelPage');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage((string) $this->page);

        $this->assertTrue($this->page->isCurrent(), 'Expected to be on ' . (string)$this->page . ', but actually on ' . $this->driver->getCurrentURL());

        $this->dumpPage(__METHOD__);
    }
}
