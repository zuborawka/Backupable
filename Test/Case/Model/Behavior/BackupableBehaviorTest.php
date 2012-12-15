<?php

App::uses('Sample', 'Backupable.Model');

class BackupableBehaviorTest extends CakeTestCase
{

	public $Sample = null;
	public $SampleRecord = null;
	public $SampleNotSkipSame = null;
	public $AnotherSample = null;

	public $fixtures = array(
		'plugin.Backupable.backup',
		'plugin.Backupable.sample',
		'plugin.Backupable.another_sample',
	);

	public function setUp()
	{
		parent::setUp();
		$this->Sample = ClassRegistry::init('Backupable.Sample');
		$this->AnotherSample = ClassRegistry::init('Backupable.AnotherSample');
		$this->SampleRecord = new SampleRecord();
	}

	public function tearDown()
	{
		unset($this->Sample, $this->SampleRecord, $this->SampleNotSkipSame);
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
			$this->assertEquals($expected, $result, 'Expected = ' . $expected . ', Result = ' . $result);
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
		$result = $lastBackup['Backup']['data']['message'];

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
		// set option "skipSame" false
		$this->SampleNotSkipSame = ClassRegistry::init('SampleNotSkipSame');
		$this->SampleNotSkipSame->create();
		$SampleNotSkipSame = $this->SampleRecord->create(array('id' => 1));
		$this->SampleNotSkipSame->save(compact('SampleNotSkipSame'));
		$this->SampleNotSkipSame->save(compact('SampleNotSkipSame'));
		$history = $this->SampleNotSkipSame->history();
		$expected = 2;
		$result = count($history);
		$this->assertEquals($expected, $result);

		// skip same by default
		$this->Sample->create();
		$Sample = $this->SampleRecord->create(array('id' => 2));
		$this->Sample->save(compact('Sample'));
		$this->Sample->save(compact('Sample'));
		$history = $this->Sample->history();
		$expected = 1;
		$result = count($history);
		$this->assertEquals($expected, $result, 'Expected = ' . $expected . ', Result = ' . $result);
	}

	public function testMulitipleModelsAccess()
	{
		$messages = array(
			'message1',
			'message2',
			'message3',
		);

		$anotherMessages = array(
			'anotherMessage1',
			'anotherMessage2',
			'anotherMessage3',
		);

		$this->Sample->create();
		$this->AnotherSample->create();

		for ($i = 0; $i < 3; $i++) {
			$Sample = $this->SampleRecord->create(array('id' => 1, 'message' => $messages[$i]));
			$this->Sample->save(compact('Sample'));

			$AnotherSample = $this->SampleRecord->create(array('id' => 1, 'message' => $anotherMessages[$i]));
			$this->AnotherSample->save(compact('AnotherSample'));
		}

		$history = $this->Sample->history();
		$oldest = array_pop($history);
		$this->Sample->restore(array('backupId' => $oldest['Backup']['id']));
		$rec = $this->Sample->read();
		$expected = $messages[0];
		$result = $rec['Sample']['message'];
		$this->assertEquals($expected, $result);

		$history = $this->AnotherSample->history();
		$oldest = array_pop($history);
		$this->AnotherSample->restore(array('backupId' => $oldest['Backup']['id']));
		$rec = $this->AnotherSample->read();
		$expected = $anotherMessages[0];
		$result = $rec['AnotherSample']['message'];
		$this->assertEquals($expected, $result);
	}

	public function testUseInvalidEngine()
	{
		ClassRegistry::removeObject('Sample');
		Configure::write('Backupable.BackupEngine', 'InvalidBackupEngine');
		$sample = ClassRegistry::init('Sample');
		$exception = null;
		try{
			$sample -> save($this->SampleRecord->create());
		} catch (Exception $e) {
			$exception = $e;
		}

		$expected = true;
		$result = $exception instanceof CakeException;

		$this->assertEquals($expected, $result);
	}
}

/**
 * Sample record generator class
 */
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

/**
 * Sample model class for not using skipSame
 */
class SampleNotSkipSame extends Sample
{

	public $useTable = 'samples';

	public $backupConfig = array(
		'skipSame' => false,
	);
}

/**
 * AnotherSample
 */
class AnotherSample extends Sample
{
	public $useTable = 'another_samples';
}

/**
 * InvalidEngine
 * This is not implemented BackupEngine interface.
 */
class InvalidBackupEngine
{
}
