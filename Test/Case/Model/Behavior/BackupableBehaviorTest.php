<?php

App::uses('Sample', 'Backupable.Model');

class BackupableBehaviorTest extends CakeTestCase
{

	public $Sample = null;
	public $SampleRecord = null;
	public $Sample_skipSame_is_false = null;
	public $Sample_use_another_table = null;

	public $fixtures = array(
		'plugin.Backupable.backup',
		'plugin.Backupable.sample',
		'plugin.Backupable.another_sample',
	);

	public function setUp()
	{
		parent::setUp();
		$this->Sample = ClassRegistry::init('Backupable.Sample');
		$this->Sample_use_another_table = ClassRegistry::init('Backupable.Sample_use_another_table');
		$this->SampleRecord = new SampleRecord();
	}

	public function tearDown()
	{
		unset($this->Sample, $this->SampleRecord, $this->Sample_skipSame_is_false);
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
		$this->Sample_skipSame_is_false = ClassRegistry::init('Backupable.Sample_skipSame_is_false');
		$this->Sample_skipSame_is_false->create();
		$Sample_skipSame_is_false = $this->SampleRecord->create(array('id' => 1));
		$this->Sample_skipSame_is_false->save(compact('Sample_skipSame_is_false'));
		$this->Sample_skipSame_is_false->save(compact('Sample_skipSame_is_false'));
		$history = $this->Sample_skipSame_is_false->history();
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
		$this->Sample_use_another_table->create();

		for ($i = 0; $i < 3; $i++) {
			$Sample = $this->SampleRecord->create(array('id' => 1, 'message' => $messages[$i]));
			$this->Sample->save(compact('Sample'));

			$Sample_use_another_table = $this->SampleRecord->create(array('id' => 1, 'message' => $anotherMessages[$i]));
			$this->Sample_use_another_table->save(compact('Sample_use_another_table'));
		}

		$history = $this->Sample->history();
		$oldest = array_pop($history);
		$this->Sample->restore(array('backupId' => $oldest['Backup']['id']));
		$rec = $this->Sample->read();
		$expected = $messages[0];
		$result = $rec['Sample']['message'];
		$this->assertEquals($expected, $result);

		$history = $this->Sample_use_another_table->history();
		$oldest = array_pop($history);
		$this->Sample_use_another_table->restore(array('backupId' => $oldest['Backup']['id']));
		$rec = $this->Sample_use_another_table->read();
		$expected = $anotherMessages[0];
		$result = $rec['Sample_use_another_table']['message'];
		$this->assertEquals($expected, $result);
	}

	public function testUseInvalidEngine()
	{
		$sample = ClassRegistry::init('Backupable.Sample_has_invalid_engine');
		$append = $this->SampleRecord->create();
		$exception = null;
		try{
			$sample -> save($append);
		} catch (Exception $e) {
			$exception = $e;
		}

		$expected = true;
		$result = $exception instanceof CakeException;

		$this->assertEquals($expected, $result, 'Expected = ' . $expected . ', Result = ' . $result);
	}

	public function testRemove()
	{
		// "dependent" option is false by default
		$this->Sample->save($this->SampleRecord->create());
		$id1 = $this->Sample->getInsertId();

		// "dependent" option is true
		$sample = ClassRegistry::init('Backupable.Sample_dependent_is_true');
		$sample->save($this->SampleRecord->create());
		$id2 = $sample->getInsertId();

		$num = 5;

		for ($i = 0; $i < $num - 1; $i++) {
			$this->Sample->save($this->SampleRecord->create(array('id' => $id1)));
			$sample		 ->save($this->SampleRecord->create(array('id' => $id2)));
		}

		$history1 = $this->Sample->history($id1);
		$history2 = $sample		 ->history($id2);

		$expected = $num;
		$result1 = count($history1);
		$result2 = count($history2);
		$this->assertEquals($expected, $result1);
		$this->assertEquals($expected, $result2);


		$this->Sample->delete($id1);
		$sample      ->delete($id2);

		$history1 = $this->Sample->history($id1);
		$history2 = $sample      ->history($id2);

		$expected1 = $num;
		$result1 = count($history1);

		$expected2 = 0;
		$result2 = count($history2);

		$this->assertEquals($expected1, $result1);
		$this->assertEquals($expected2, $result2);
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
			'created' => date('Y-m-d H:i:s', self::$baseTime + $serial),
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
class Sample_skipSame_is_false extends Sample
{

	public $useTable = 'samples';

	public $backupConfig = array(
		'skipSame' => false,
	);
}

/**
 * Sample_use_another_table
 */
class Sample_use_another_table extends Sample
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

/**
 * Using InvalidEngine
 */
class Sample_has_invalid_engine extends Sample
{
	public $useTable = 'samples';

	public $actsAs = array(
		'Backupuble.Backupable' => array(
			'backupEngineClass' => 'InvalidBackupEngine',
		),
	);
}

/**
 * Sample model with 'dependent' option
 */
class Sample_dependent_is_true extends Sample
{
	public $useTable = 'samples';

	public $backupConfig = array(
		'dependent' => true,
	);
}
