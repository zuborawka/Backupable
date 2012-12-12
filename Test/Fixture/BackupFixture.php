<?php
/**
 * BackupFixture
 *
 */
class BackupFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
		'table_name' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'ascii_general_ci', 'charset' => 'ascii'),
		'src_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10),
		'data' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'timestamp', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'table_and_src_id' => array('column' => array('table_name', 'src_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

}
