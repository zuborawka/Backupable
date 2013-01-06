<?php
App::uses('BackupableAppModel', 'Backupable.Model');

abstract class BaseOfBackupEngine extends BackupableAppModel
{

	public function getProperty(Model $model, $name, $throws = false)
	{
		if (isset($this->$name)) {
			return $this->$name;
		}
		if ($throws) {
			throw new InvalidArgumentException($name . ' is not set.');
		}
	}

}
