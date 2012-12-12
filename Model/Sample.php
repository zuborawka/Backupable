<?php

App::uses('AppModel', 'Model');
CakePlugin::load('Backupable');

class Sample extends AppModel
{

	public $actsAs = array(
		'Backupable.Backupable' => array(
			'backupFields' => array(
				'title', 'message', 'user_id',
			),
		),
	);

}
