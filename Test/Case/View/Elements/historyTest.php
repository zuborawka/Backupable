<?php

App::uses('CakeRequest', 'Network');
App::uses('HtmlHelper', 'View/Helper');
App::uses('Controller', 'Controller');

class historyTest extends CakeTestCase
{

	public $request;
	public $Html;
	public $element;
	public $dataProvider;

	public function setUp() {
		$this->request = new CakeRequest();
		$this->View = $this->getMock('View', array('append'), array(new TheHtmlTestController()));
		$this->Html = new HtmlHelper($this->View);
		$this->element = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'View' . DS . 'Elements' . DS . 'history.ctp';
		$this->dataProvider = new BackupHistoryProvider();
	}

	public function tearDown() {
		unset($this->request, $this->Html, $this->element, $this->dataProvider);
	}

	public function testView() {
		$this->request->params['pass'][0] = 123;
		$history = array();
		$history[] = $this->dataProvider->data();

		ob_start();

		include($this->element);

		$view = ob_get_clean();

		$this->assertRegExp('/ class=[\'"]backupHistory[ \'"]/', $view);
	}

}

class BackupHistoryProvider
{
	public function data($options = array())
	{
		$options = array_merge(
			array(
				'alias' => 'Backup',
				'id' => 1,
				'created' => '2012-12-12 12:12:12',
			),
			$options
		);

		extract($options);

		$data = array(
			$alias => compact('id', 'created')
		);

		return $data;
	}
}

/**
 * TheHtmlTestController class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class TheHtmlTestController extends Controller {

/**
 * name property
 *
 * @var string 'TheTest'
 */
	public $name = 'TheTest';

/**
 * uses property
 *
 * @var mixed null
 */
	public $uses = null;
}
