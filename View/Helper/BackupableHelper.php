<?php

App::uses('AppHelper', 'View/Helper');

class BackupableHelper extends AppHelper
{

	public function __construct(View $view, $settings = array()) {
		parent::__construct($view, $settings);

	}

	public function history($options = array())
	{
	}
}
