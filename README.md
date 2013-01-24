Backupable Plugin for CakePHP2.x
==========

# What is this?
A plugin to backup and restore records for cakephp2.x, row by row.

## What is it like?
It's like the WordPress revisions for automatic backup of an article.
But this plugin is more simple and fewer function.

## How to use?
1. [Installation](#section1)
2. [Create a table for backup records.](#section2)
3. [Set your bootstrap.php to load plugin.](#section3)
4. [Set your Model file to act as "Backupable".](#section4)
5. [API and usage](#section5)
6. [Some options](#section6)
7. [To do](#section-)

##<a name="section1">1.Installation
Clone it to your plugin directory.
```console
cd path/to/app/Plugin or /plugins
git clone git@github.com:Zuborawka/Backupable.git Backupable
```
Or set files into your plugin directory.
- app/Plugin/Backupable/Model/BackupableAppModel.php
- app/Plugin/Backupable/Model/BasicBackup.php
- app/Plugin/Backupable/Model/Interface/BackupEngine.php
- app/Plugin/Backupable/Model/Behavior/BackupableBehavior.php
- app/Plugin/Backupable/View/Elements/history.ctp


##<a name="section2">2.Create a table for backup records.

````sql
CREATE TABLE `backups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) CHARACTER SET ascii NOT NULL,
  `src_id` int(10) unsigned NOT NULL,
  `data` longblob NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table_and_src_id` (`table_name`,`src_id`,`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
````
This table is associated with BasicBackup class.
So, you can change the table name by setting in Backupable/Model/BasicBackup.php.
And you can also use your own BackupEngine, if it implements BacupEngine interface.

##<a name="section3">3.Set your bootstrap.php to load plugin.

````php:app/Config/bootstrap.php
CakePlugin::load('Backupable');
````

##<a name="section4">4.Set your Model file to act as "Backupable".

````php:app/Model/Post.php
  class Post extends AppModel {
      $actsAs = array('Backupable.Backupable');
  }
````

##<a name="section5">5.API and usage.

###API

####BackupableBehavior::backup()
* Backup current record.
* By default, this method is called automaticaly after save data.

####BackupableBehavior::history()
* It returns the history of backups.
* The result includes backups' IDs and created times from latest to earliest by default.

####BackupableBehavior::remember()
* It returns backup data.
* If the table name, source id and backup id are not matched, it returns false.

####BackupableBehavior::restore()
* It restores the record by backup data.
* If the table name, source id and backup id are not matched, it returns false.

---
###Usage

````php:app/Controller/PostsController.php
/**
 * app/Controller/PostsController.php
 * PostsController
 */
class PostsController extends AppController {

/**
 * Usage of BackupableBehavior::history()
 */
    function history($id) {
        $history = $this->Post->history($id);
        $this->set(compact('history'));
    }

/**
 * Usage of BackupableBehavior::remember()
 */
    function remember($id, $backupId) {
        $remember = $this->Post->remember(compact('id', 'backupId'));
        $this->set(compact('remember'));
    }

/**
 * Usage of BackupableBehavior::restore()
 */
    function restore($id, $backupId) {
        $result = $this->Post->restore(compact('id', 'backupId'));
        $message = $result ? 'Restored' : 'Fault';
        $this->Session->setFlash($message);
        $this->redirect(array('action' => 'view', $id));
    }
}
````

````php:app/View/Posts/history.ctp
<?php
/**
 * app/View/Posts/history.ctp
 */
<h2>History</h2>
<ul>
<?php foreach ($history as $data): ?>
    <li><?php echo $this->Html->link(
        $data['Backup']['created'],
        array(
            'action' => 'remember',
            $this->request->params['pass'][0],
            $data['Backup']['id']
        )); ?></li>
<?php endforeach; ?>
</ul>
````
It helps you write a list of history in view to use Backupable.history element.
````
echo $this->element('Backupable.history');
````

````php:app/View/Posts/remember.ctp
<?php
/**
 * app/View/Posts/remember.ctp
 */
<h2>Do you restore the record?</h2>
<dl>
    <dt>Title</dt>
    <dd><?php echo h($remember['Backup']['data']['title']); ?></dd>
    <dt>Content</dt>
    <dt><?php echo h($remember['Backup']['data']['content']); ?></dt>
</dl>
<p>
<?php
echo $this->Html->link(
    'Yes, I restore it now!',
    array(
        'action' => 'restore',
        $this->request->params['pass'][0],
        $this->request->params['pass'][1]
    )
);
?>
</p>
````

##<a name="section6">6.Some options.
You can set 7 type options.

* "backupFields" array default null. Null means all fields.
* "autoSave" boolean default true
* "skipSame" boolean default true
* "dependent" boolean default false
* "backupEngineClass" string default "Backupable.BasicBackup".
* "backupEngineAlias" string default "Backup"
* "backupConfig" string

e.g.)

````app/Model/Post.php
/**
 * app/Model/Post.php
 * Post.php
 */
public $actsAs = array(
    'Backupable.Backupable' => array(
        'backupFields' => array('title', 'content'),
        'autoSave' => false,
        'skipSame' => false,
        'backupEngineClass' => 'MyCoolBackup',
        'backupEngineAlias' => 'SoCoolBackup',
        'backupConfig' => array(
            'useDbConfig' => 'backup_db',
        ),
    ),
);
````
### "backupFields" option
It specifies the list of fields to backup.
Null means all fields.
Default is null.

### "autoSave" option
If it is set true, the backup engine will save record automatically after the original was saved.
Default is true

### "skipSame" option
If it is set true, the backup engine will skip saving backup record when the last record is same the current.
Default is true

### "dependent" option
If it is set true, the backup engine will remove all records associated with the original.
Default is false

### "backupEngineClass" option
It specifies the class for backup logic. The class must implement "Backupable.BackupEngine" interface.
Default is "Backupable.BasicBackup"

### "backupEngineAlias" option
It specifies the alias of backup engine class.
Default is "Backup"

### "backupConfig" option
It specifies the properties of BackupEngine instance.

##<a name="section-">-.To do

* View helper class
* Another backup engine class
