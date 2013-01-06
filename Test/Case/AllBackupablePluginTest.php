<?php
/**
 * AllBackupablePluginTest file
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * AllBackupableTest class
 *
 * This test group will run cache engine tests.
 *
 * @package       Cake.Test.Case
 */
class AllBackupablePluginTest extends PHPUnit_Framework_TestSuite
{

/**
 * suite method, defines tests for this suite.
 *
 * @return void
 */
	public static function suite()
	{
		$suite = new CakeTestSuite('All Backupable related class Tests');
		$dir = dirname(__FILE__);
		$suite->addTestDirectory($dir . DS . 'Model' . DS . 'Behavior');
		$suite->addTestDirectory($dir . DS . 'View' . DS . 'Elements');
		$suite->addTestDirectory($dir . DS . 'View' . DS . 'Helper');
		return $suite;
	}
}
