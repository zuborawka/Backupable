<?php

App::uses('AppModel', 'Model');
CakePlugin::load('Backuppable');

class Sample extends AppModel
{

	public $actsAs = array(
		'Backuppable.Backuppable' => array(
			'backupFields' => array(
				'title', 'message', 'user_id',
			),
		),
	);

}
