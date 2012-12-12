<?php

App::uses('Sample', 'Backuppable.Model');

class BackuppableBehaviorTest extends CakeTestCase
{

	public $Sample = null;
	public $SampleRecord = null;
	public $SampleSkipSame = null;

	public $fixtures = array(
		'plugin.Backuppable.backup',
		'plugin.Backuppable.sample',
	);

	public function setUp()
	{
		parent::setUp();
		$this->Sample = ClassRegistry::init('Backuppable.Sample');
		$this->SampleRecord = new SampleRecord();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	public function testNumOfHistory()
	{
		for ($i = 0; $i < 3; $i++) {
			$Sample = $this->SampleRecord->create();
			$this->Sample->save(compact('Sample'));
		}

		$rec = $this->Sample->find('all');
		foreach ($rec as $r) {
			$this->Sample->id = $r['Sample']['id'];
			$history = $this->Sample->history();
			$expected = 1;
			$result = count($history);
			$this->assertEquals($expected, $result);
		}

		$first = $this->Sample->find();
		$id = $first['Sample']['id'];

		$add = 5;

		for ($i = 0; $i < $add; $i++) {
			sleep(1);
			$Sample = $this->SampleRecord->create(array('id' => $id));
			$this->Sample->save(compact('Sample'));
		}

		$this->Sample->id = $id;
		$history = $this->Sample->history();
		$expected = $add + 1;
		$result = count($history);
		$this->assertEquals($expected, $result);

	}

	public function testLastBackup()
	{
		$this->Sample->create();
		$this->Sample->save(array('Sample' => $this->SampleRecord->create(array('id' => null))));
		$first = $this->Sample->find();
		$id = $first['Sample']['id'];
		$messages = array(
			'message1',
			'message2',
			'message3',
		);
		foreach ($messages as $message) {
			$this->Sample->id = $id;
			$this->Sample->save(array('Sample' => $this->SampleRecord->create(compact('id', 'message'))));
		}

		$this->Sample->id = $id;
		$lastBackup = $this->Sample->rememberLast();

		$expected = array_pop($messages);
		$result = $lastBackup['message'];

		$this->assertEquals($expected, $result);
	}

	public function testRestore()
	{
		$firstMessage = 'My first message';
		$this->Sample->create();
		$this->Sample->save(array('Sample' => $this->SampleRecord->create(array('id' => null, 'message' => $firstMessage))));
		$first = $this->Sample->find();
		$id = $first['Sample']['id'];
		$messages = array(
			'message1',
			'message2',
			'message3',
		);
		foreach ($messages as $message) {
			$this->Sample->id = $id;
			$this->Sample->save(array('Sample' => $this->SampleRecord->create(compact('id', 'message'))));
		}
		$history = $this->Sample->history($id);
		$firstPosition = array_pop($history);
		$res = $this->Sample->restore(array('backupId' => $firstPosition['Backup']['id']));
		$data = $this->Sample->read();
		$expected = $firstMessage;
		$result = $data['Sample']['message'];
		$this->assertEquals($expected, $result);

		$secondPosition = array_pop($history);
		$res = $this->Sample->restore(array('backupId' => $secondPosition['Backup']['id']));
		$data = $this->Sample->read();
		$expected = array_shift($messages);
		$result = $data['Sample']['message'];
		$this->assertEquals($expected, $result);
	}

	public function testSkipSame()
	{
		// If set option "skipSame" true, backup won't be added after saving same data.
		$this->SampleSkipSame = ClassRegistry::init('SampleSkipSame');
		$this->SampleSkipSame->create();
		$SampleSkipSame = $this->SampleRecord->create(array('id' => 1));
		$this->SampleSkipSame->save(compact('SampleSkipSame'));
		$this->SampleSkipSame->save(compact('SampleSkipSame'));
		$history = $this->SampleSkipSame->history();
		$expected = 1;
		$result = count($history);
		$this->assertEquals($expected, $result);

		// If record is changed, backup will be added.
		$SampleSkipSame = $this->SampleRecord->create(array('id' => 1));
		$this->SampleSkipSame->save(compact('SampleSkipSame'));
		$this->SampleSkipSame->save(compact('SampleSkipSame'));
		$history = $this->SampleSkipSame->history();
		$expected = 2;
		$result = count($history);
		$this->assertEquals($expected, $result);

		// NOT skip same
		$this->Sample->create();
		$Sample = $this->SampleRecord->create(array('id' => 2));
		$this->Sample->save(compact('Sample'));
		$this->Sample->save(compact('Sample'));
		$history = $this->Sample->history();
		$expected = 2;
		$result = count($history);
		$this->assertEquals($expected, $result);
	}
}

class SampleRecord
{

	public $template = array(
		'id' => '',
		'title' => '',
		'message' => '',
		'user_id' => '',
		'created' => '',
	);

	public static $serial = 1;

	public static $baseTime = null;

	public function __construct() {
		self::$baseTime = time();
	}

	public function create($options = array()) {
		$serial = self::$serial++;

		$record = array(
			'id' => $serial,
			'title' => md5('Title' . $serial),
			'message' => md5('Message' . $serial),
			'user_id' => $serial * 10,
			'created' => date('Y-m-d H:i:s', time() + $serial),
		);

		$record = array_merge(
			$record,
			$options
		);

		return $record;
	}
}

class SampleSkipSame extends Sample
{

	public $useTable = 'samples';

	public $backup = array(
		'skipSame' => true,
	);
}
