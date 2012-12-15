<?php

App::uses('BackupEngine', 'Backupable.Model/Interface');

abstract class BackupableAppModel extends AppModel implements BackupEngine {

}

