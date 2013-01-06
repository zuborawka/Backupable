<?php
App::uses('BaseOfBackupEngine', 'Backupable.Model');
/**
 * BasicBackup Model
 *
 * It's a very simple model that implemented BackupEngine interface.'
 */
class BasicBackup extends BaseOfBackupEngine {

	public $useTable = 'backups';

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

/**
 * backup the record
 *
 * @param Model
 * @param array
 * @return array|false Created data. If it could not get ID, it returns false.
 */
	public function backup(Model $model, $options = array()) {
		list($id, $table) = $this->_getSrcIdAndTableName($model, $options);
		if (! $model->exists($id)) {
			return array();
		}

		$settings = $options['settings'];

		$fields = $settings['backupFields'];
		$conditions = array($model->alias . '.' . $model->primaryKey => $id);
		$model->recursive = -1;
		$data = $model->find('first', compact('fields', 'conditions'));

		$serialized = serialize($data[$model->alias]);

		$backupData = array(
			$this->alias => array(
				'id' => null,
				'table_name' => $table,
				'src_id' => $id,
				'data' => $serialized,
			),
		);

		if ($settings['skipSame']) {
			$last = $this->rememberLast($model, $options);
			if (isset($last[$this->alias]['data'])) {
				$last = serialize($last[$this->alias]['data']);
			}
			if ($last === $serialized) {
				return false;
			}
		}

		$this->create();
		$res = $this->save($backupData);
		return $res;
	}

/**
 * Get the history of record.
 * It returns a list of Backup's id and created time from latest to earliest by default.
 *
 * Options:
 *    limit, page, order, fields
 * @param Model
 * @param array
 * @return array
 */
	public function history(Model $model, $options = array()) {
		// default options are
		$limit = 20;
		$page = 1;
		$order = $this->alias . '.id DESC';
		$fields = array('id', 'created');

		if (is_array($options)) {
			extract($options);
		}

		list($id, $table) = $this->_getSrcIdAndTableName($model, $options);
		$conditions = array(
			$this->alias . '.table_name' => $table,
			$this->alias . '.src_id' => $id,
		);
		$recursive = -1;
		$history = $this->find('all', compact('conditions', 'fields', 'order', 'limit', 'page', 'recursive'));
		return $history;
	}

/**
 * Get backup data.
 * If the table name, source id and backup id are not matched, it returns false.
 *
 * Options:
 *   'backupId' -- required
 *   'id' -------- option (if it is empty, the method searches $model->id)
 *
 * @param Model
 * @param array
 */
	public function remember(Model $model, $options = array()) {
		if (isset($options['backupId'])) {
			$backupId = $options['backupId'];
		} else {
			return false;
		}
		list($id, $table) = $this->_getSrcIdAndTableName($model, $options);
		$conditions = array(
			$this->alias . '.id' => $backupId,
			$this->alias . '.table_name' => $table,
			$this->alias . '.src_id' => $id,
		);
		$fields = array('data', 'created');
		$data = $this->find('first', compact('conditions', 'fields'));
		if (empty($data)) {
			return array();
		}
		$data[$this->alias]['data'] = unserialize($data[$this->alias]['data']);
		$data[$this->alias]['data'][$model->primaryKey] = $id;
		return $data;
	}

/**
 * Get the last backup data
 *
 * @param Model
 * @param array
 * @return array
 */
	public function rememberLast(Model $model, $options = array()) {
		list($id, $table) = $this->_getSrcIdAndTableName($model, $options);
		$conditions = array(
			$this->alias . '.table_name' => $table,
			$this->alias . '.src_id' => $id,
		);
		$order = $this->alias . '.id DESC';
		$lastBackup = $this->find('first', compact('conditions', 'order'));
		if (empty($lastBackup[$this->alias]['data'])) {
			return array();
		}
		$lastBackup[$this->alias]['data'] = unserialize($lastBackup[$this->alias]['data']);
		return $lastBackup;
	}

/**
 * Restore the record by backup data.
 * If the table name, source id and backup id are not matched, it returns false.
 * Options:
 *   'backupId' -- required
 *   'id' -------- option (if it is empty, the method searches $model->id)
 *
 * @param Model
 * @param array
 */
	public function restore(Model $model, $options = array()) {
		$restore = $this->remember($model, $options);
		if (empty($restore)) {
			return false;
		}
		$model->id = $restore[$this->alias]['data'][$model->primaryKey];
		return $model->save(array($model->alias => $restore[$this->alias]['data']));
	}

/**
 *
 */
	public function removeBackups(Model $model, $options = array()) {
		list($id, $table) = $this->_getSrcIdAndTableName($model, $options);
		$conditions = array(
			$this->alias . '.table_name' => $table,
			$this->alias . '.src_id' => $id,
		);
		return $this->deleteAll($conditions);
	}

	protected function _getSrcId(Model $model, $options = array()) {
		if (is_int($options) || is_string($options)) {
			$id = $options;
		} elseif (isset($options['id'])) {
			$id = $options['id'];
		} elseif (isset($model->id)) {
			$id = $model->id;
		} else {
			$id = null;
		}
		return $id;
	}

	protected function _getTableName($model) {
		if ($model->tablePrefix) {
			$table = $model->tablePrefix . $model->useTable;
		} else {
			$table = ConnectionManager::$config->{$model->useDbConfig}['prefix'] . $model->useTable;
		}
		return $table;
	}

	protected function _getSrcIdAndTableName(Model $model, $options = array()) {
		return array($this->_getSrcId($model, $options), $this->_getTableName($model));
	}

}
