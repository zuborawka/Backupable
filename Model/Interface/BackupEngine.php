<?php

interface BackupEngine {

/**
 * It must backup the record.
 *
 * @param Model
 * @param array
 * @return array|false Created data. If it could not get ID, it returns false.
 */
	public function backup(Model $model, $options = array());

/**
 * It must return the history of record.
 * It must return a list of backup's id and created time like below sample from latest to earliest by default.
 * The result can also includes other data.
 *
 * array(
 *    array(
 *        {BackupEngineAlias} => array(
 *            'id' =>      {id},
 *            'created' => {yyyy-mm-dd hh:ii:ss}
 *        )
 *    ),
 *    array(
 *        {BackupEngineAlias} => array(
 *            'id' =>      {id},
 *            'created' => {yyyy-mm-dd hh:ii:ss}
 *        )
 *    ),
 *    array(
 *        {BackupEngineAlias} => array(
 *            'id' =>      {id},
 *            'created' => {yyyy-mm-dd hh:ii:ss}
 *        )
 *    ),
 * )
 *
 * Options:
 *    limit, page, order, fields
 * @param Model
 * @param array
 * @return array
 */
	public function history(Model $model, $options = array());

/**
 * It must return backup data appointed by options and model's id.
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
 * It must return the last backup data
 *
 * @param Model
 * @param array
 * @return array
 */
	public function rememberLast(Model $model, $options = array());

/**
 * It must restore the record by backup data.
 *
 * Options:
 *   'backupId' -- required
 *   'id' -------- option (if it is empty, the method searches $model->id)
 *
 * @param Model
 * @param array
 */
	public function restore(Model $model, $options = array());

}
