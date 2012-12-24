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
		$id = 1234;
		$this->request->params['pass'][0] = $id;
		$src = array(
			array('id' => 100, 'created' => '2012-12-12 12:12:12'),
			array('id' =>  50, 'created' => '2011-11-11 11:11:11'),
			array('id' =>  10, 'created' => '2010-10-10 10:10:10'),
		);
		$history = array();
		foreach ($src as $_src) {
			$history[] = $this->dataProvider->data($_src);
		}

		ob_start();
		include($this->element);
		$view = ob_get_clean();

		$xml = simplexml_load_string($view);
		$attributes = $xml->attributes();
		$li = $xml->li;
		$classes = explode(' ', $attributes['class']);

		$result = in_array('backupHistory', $classes);
		$this->assertTrue($result);

		$expected = count($history);
		$result = count($li);
		$this->assertEquals($expected, $result);

		$i = 0;
		foreach ($li as $_li) {

			$_src = $src[$i];

			$expected = $_src['created'];
			$result = (string)$_li->a;
			$this->assertEquals($expected, $result);

			$aAttributes = $_li->a->attributes();
			$expected = $this->Html->url(array('action' => 'remember', $id, $_src['id']));
			$result = $aAttributes['href'];
			$this->assertEquals($expected, $result);

			$expected = (string)$id;
			$result = $aAttributes['data-id'];
			$this->assertEquals($expected, $result);

			$expected = (string)$_src['id'];
			$result = $aAttributes['data-backup-id'];
			$this->assertEquals($expected, $result);

			$expected = 'rememberBackup';
			$result = $aAttributes['class'];
			$this->assertEquals($expected, $result);

			$i++;
		}

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
