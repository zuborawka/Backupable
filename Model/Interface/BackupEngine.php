<?php

interface BackupEngine {

/**
 * backup the record
 *
 * @param Model
 * @param array
 * @return array|false Created data. If it could not get ID, it returns false.
 */
	public function backup(Model $model, $options = array());

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
	public function history(Model $model, $options = array());

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
	public function remember(Model $model, $options = array());

/**
 * Get the last backup data
 *
 * @param Model
 * @param array
 * @return array
 */
	public function rememberLast(Model $model, $options = array());

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
	public function restore(Model $model, $options = array());

}
