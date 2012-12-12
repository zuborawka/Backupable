<?php
App::uses('BackupableAppModel', 'Backupable.Model');
/**
 * Backup Model
 *
 */
class Backup extends BackupableAppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'created';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'table_name' => array(
			'table_name' => array(
				'rule' => '/^[_a-zA-Z][_a-zA-Z0-9]+$/',
			),
		),
		'src_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'data' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);
}
