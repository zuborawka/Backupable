<?php
require_once('PHPUnit/Framework/IncompleteTestError.php');

App::uses('View', 'View');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('AppModel', 'Model');
App::uses('AppController', 'Controller');
App::uses('BackupableHelper', 'Backupable.View/Helper');
/**
 * BackupableHelperTest class
 *
 * @package	Backupable.Test.Case.View.Helper
 */
class BackupableHelperTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();
		$this->Controller = new SamplesController(new CakeRequest(), new CakeResponse());
		$this->View = new View($this->Controller);
		$this->Helper = new BackupableHelper($this->View);
	}

	public function tearDown() {
		unset($this->Controller, $this->View, $this->Helper);
	}

	public function testHistory() {
		throw new PHPUnit_Framework_IncompleteTestError('This method is not completed.');
	}

	public function testRemember() {
		throw new PHPUnit_Framework_IncompleteTestError('This method is not completed.');
	}



}

class Sample extends AppModel {

	public $actsAs = array('Backupable.Backupable');

}

class SamplesController extends AppController {

	public $helpers = array('Backupable.Backupable');

}