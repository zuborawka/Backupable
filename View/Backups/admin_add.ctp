<div class="backups form">
<?php echo $this->Form->create('Backup'); ?>
	<fieldset>
		<legend><?php echo __('Admin Add Backup'); ?></legend>
	<?php
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

		<li><?php echo $this->Html->link(__('List Backups'), array('action' => 'index')); ?></li>
	</ul>
</div>
