<?php

/**
 * This is an element code to display a history for Backupable plugin.
 * It requires $history as the history data of BackupModel.
 *
 * @var $history array
 * @var $alias string [optional]
 * @var $id string [optional]
 */

$_default = array(
	'alias' => 'Backup',
	'id' => empty($this->request->params['pass'][0]) ? null : $this->request->params['pass'][0],
);
extract($_default, EXTR_SKIP);
?>
		<ul class="backupHistory">
<?php
foreach ($history as $data):
?>
			<li><?php echo $this->Html->link($data[$alias]['created'], array(
				'action' => 'remember',
				$id,
				$data[$alias]['id']
			), array(
				'class' => 'rememberBackup',
				'data-id' => $id,
				'data-backup-id' => $data[$alias]['id'])); ?></li>
<?php
endforeach;
?>
		</ul>
