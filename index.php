<?php
/**
 * @package    Joomla.Site
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Constant that is checked in included files to prevent direct access.
define('_JEXEC', 1);

require 'libraries/vendor/autoload.php';
require 'appropriate/directory/autoload.php';

$app      = (new \Joomla\Cms\Application\Factory)->create('site', $_SERVER);
$app->run();
