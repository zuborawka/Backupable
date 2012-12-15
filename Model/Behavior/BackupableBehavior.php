<?php
App::uses('BackupEngine', 'Backupable.Model/Interface');

/**
 * Backup utility behavior based on table name and fields.
 */
class BackupableBehavior extends ModelBehavior
{

	public $settings = array();
	public $mapMethods = array();
	public $backupEngineClass = 'Backupable.BasicBackup';
	public $backupEngineAlias = 'Backup';

/**
 * The model could hold these properties for the behavior.
 * The key is property name, the value is default value.
 * @var array
 */
	protected $_defaultOptionProperties = array(
		'autoBackup' => true,
		'skipSame' => true,
	);

	public function __get($name) {
		if ($name === 'Backup') {
			$this->Backup = $this->_getBackupEngine();
			return $this->Backup;
		}
	}

	protected function _getBackupEngine($class = null) {
		if (!$class) {
			if (! ($class = Configure::read('Backupable.BackupEngine'))) {
				$class = $this->backupEngineClass;
			}
		}
		if (is_string($class)) {
			$class = array('class' => $class);
		}
		if (empty($class['alias'])) {
			if (! ($alias = Configure::read('Backupable.BackupEngineAlias'))) {
				$alias = $this->backupEngineAlias;
			}
			$class['alias'] = $alias;
		}
		$backupEngine = ClassRegistry::init($class);
		if (! $backupEngine instanceof BackupEngine) {
			throw new CakeException(get_class($backupEngine) . ' must be instance of BackupEngine. But it isn\'t it.');
		}
		return $backupEngine;
	}

/**
 * Callback when attach itself to the Model
 * @param Model
 * @param array
 * @return void
 */
	public function setup(Model $model, $config = array()) {

		if (isset($model->backupConfig) && is_array($model->backupConfig)) {
			$_config = array_merge(
				$this->_defaultOptionProperties,
				$model->backupConfig
			);
		} else {
			$_config = $this->_defaultOptionProperties;
		}

		$config = array_merge($_config, $config);

		if (empty($config['backupFields'])) {
			$config['backupFields'] = array_keys($model->schema());
		}

		foreach ($this->_defaultOptionProperties as $prop => $default) {
			if (!isset($config[$prop]) && isset($model->backup[$prop])) {
				$config[$prop] = $model->backup[$prop];
			} elseif (!isset($config[$prop])) {
				$config[$prop] = $default;
			}
		}

		$this->settings[$model->alias] = $config;
	}

/**
 * Callback after save
 * Backup automatically by configuration value of "autoBackup"
 *
 * @param Model
 * @param boolean
 * @return boolean If it fault backup, it returns false.
 */
	public function afterSave(Model $model, $created) {
		if ($this->settings[$model->alias]['autoBackup']) {
			if (! $this->backup($model)) {
				return false;
			}
		}
		return true;
	}

/**
 * wrapper of BackupEngine::backup()
 *
 * @param Model
 * @param array
 * @return array|false Created data. If it could not get ID, it returns false.
 */
	public function backup(Model $model, $options = array()) {
		$options['settings'] = $this->settings[$model->alias];
		return $this->Backup->backup($model, $options);
	}

/**
 * wrapper of BackupEngine::history()
 *
 * Options:
 *    limit, page, order, fields
 * @param Model
 * @param array
 * @return array
 */
	public function history(Model $model, $options = array()) {
		return $this->Backup->history($model, $options);
	}

/**
 * wrapper of BackupEngine::remember()
 *
 * Options:
 *   'backupId' -- required
 *   'id' -------- option (if it is empty, the method searches $model->id)
 *
 * @param Model
 * @param array
 */
	public function remember(Model $model, $options = array()) {
		return $this->Backup->remember($model, $options);
	}

/**
 * wrapper of BackupEngine::rememberLast
 *
 * @param Model
 * @param array
 * @return array
 */
	public function rememberLast(Model $model, $options = array()) {
		return $this->Backup->rememberLast($model, $options);
	}

/**
 * wrapper of BackupEngine::restore()
 *
 * Options:
 *   'backupId' -- required
 *   'id' -------- option (if it is empty, the method searches $model->id)
 *
 * @param Model
 * @param array
 */
	public function restore(Model $model, $options = array()) {
		return $this->Backup->restore($model, $options);
	}

}
