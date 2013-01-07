<?php
App::uses('BackupEngine', 'Backupable.Model/Interface');

/**
 * Backup utility behavior based on table name and fields.
 */
class BackupableBehavior extends ModelBehavior
{

	public $settings = array();
	public $mapMethods = array();

/**
 * The model could hold these properties for the behavior.
 * The key is property name, the value is default value.
 * @var array
 */
	protected $_defaultOptionProperties = array(
		'autoBackup' => true,
		'dependent' => false,
		'skipSame' => true,
		'backupEngineClass' => 'Backupable.BasicBackup',
		'backupEngineAlias' => 'Backup',
		'backupEngineConfig' => null,
	);

	protected function _getBackupEngine(Model $model) {
		if (empty($this->settings[$model->alias]['backupEngine'])) {
			$class = $this->settings[$model->alias]['backupEngineClass'];
			$alias = $this->settings[$model->alias]['backupEngineAlias'];
			$backupEngine = ClassRegistry::init(array('class' => $class, 'alias' => $alias));

			if (! $backupEngine instanceof BackupEngine) {
				throw new CakeException(get_class($backupEngine) . ' must be instance of BackupEngine. But it isn\'t it.');
			}

			if (!empty($this->settings[$model->alias]['backupEngineConfig']) &&
				is_array($this->settings[$model->alias]['backupEngineConfig'])) {
				foreach ($this->settings[$model->alias]['backupEngineConfig'] as $configName => $config) {
					$backupEngine->$configName = $config;
				}
			}

			$this->settings[$model->alias]['backupEngine'] = $backupEngine;
		}
		return $this->settings[$model->alias]['backupEngine'];
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
 * Callback after delete
 * Remove the associated backup records, if "dependent" option is valid.
 *
 * @param Model
 * @return void
 */
	public function afterDelete(Model $model) {
		if ($this->settings[$model->alias]['dependent']) {
			$this->removeBackups($model);
		}
	}

/**
 * wrapper of BackupEngine::backup()
 *
 * @param Model
 * @param array
 * @return array|false Created data. If it could not get ID, it returns false.
 */
	public function backup(Model $model, $options = array()) {
		if (empty($options['settings'])) {
			$options['settings'] = $this->settings[$model->alias];
		}
		return $this->_getBackupEngine($model)->backup($model, $options);
	}

/**
 * wrapper of BackupEngine::removeBackups()
 *
 * @param Model
 * @param array
 * @return boolean
 */
	public function removeBackups(Model $model, $options = array()) {
		if (empty($options['settings'])) {
			$options['settings'] = $this->settings[$model->alias];
		}
		return $this->_getBackupEngine($model)->removeBackups($model, $options);
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
		if ($options && ! is_array($options)) {
			if (is_numeric($options)) {
				$options = array('id' => $options);
			} else {
				$options = (array)$options;
			}
		}
		if (empty($options['settings'])) {
			$options['settings'] = $this->settings[$model->alias];
		}
		return $this->_getBackupEngine($model)->history($model, $options);
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
		if (empty($options['settings'])) {
			$options['settings'] = $this->settings[$model->alias];
		}
		return $this->_getBackupEngine($model)->remember($model, $options);
	}

/**
 * wrapper of BackupEngine::rememberLast
 *
 * @param Model
 * @param array
 * @return array
 */
	public function rememberLast(Model $model, $options = array()) {
		if (empty($options['settings'])) {
			$options['settings'] = $this->settings[$model->alias];
		}
		return $this->_getBackupEngine($model)->rememberLast($model, $options);
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
		if (empty($options['settings'])) {
			$options['settings'] = $this->settings[$model->alias];
		}
		return $this->_getBackupEngine($model)->restore($model, $options);
	}

/**
 * wrappe of BackupEngine::getProperty
 *
 * @param Model
 * @param string
 * @param boolean
 * @return mixed
 * @throws InvalidArgumentException
 */
	public function getBackupEngineProperty(Model $model, $propertyName, $throws = false) {
		return $this->_getBackupEngine($model)->getProperty($model, $propertyName, $throws);
	}

}
