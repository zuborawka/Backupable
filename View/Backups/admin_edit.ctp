<div class="backups form">
<?php echo $this->Form->create('Backup'); ?>
	<fieldset>
		<legend><?php echo __('Admin Edit Backup'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('table_name');
		echo $this->Form->input('src_id');
		echo $this->Form->input('data');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Backup.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Backup.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Backups'), array('action' => 'index')); ?></li>
	</ul>
</div>
