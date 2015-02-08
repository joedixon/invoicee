<?php
include('header.php');
?>
<div class="rightNav">
	<span class="button">
		<?php echo '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=record'.AMP.'type=quote" class="submit">' . lang('create_quote') . '</a>'; ?>
	</span>
</div>
<div class="clear"></div>
<div id="filterMenu">
	<fieldset>
		<legend><?=lang('search_quotes')?></legend>

	<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records', array('class' => 'form-search'))?>
	<?php
		$input = array(
		              'name'        => 'keyword',
		              'id'          => 'keywords',
		              'value'       => $keyword,
					  'class'		=> 'input-medium search-query',
					  'style'		=> 'box-sizing:content-box;width:206px'
		            );
	?>
		<div>
			<?=form_input($input, NULL,  'placeholder="'.lang('keywords').'"')?>
			<?= form_submit('submit', lang('search'), 'class="btn" id="search_button"').NBS.NBS?>			
		</div>

	<?=form_close()?>
	
	</fieldset>
	
</div> <!-- filterMenu -->
<div>	
<?php

if(isset($results) && $results == 0){
	echo '<h2>' . lang('no_quotes') . '</h2>';
}
else{
	$this->table->clear();
	$cp_table_template['heading_cell_start'] = '<th style="width:25%">';
	$cp_table_template['table_open'] = '<table class="table table-striped table-bordered">';
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(array(lang('id'), lang('created'), lang('updated'), lang('total'), lang('view'), lang('send'), lang('delete')));
	echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records'.AMP.'type=quotes');
	
	foreach($data as $row){	
		$this->table->add_row(array(
							'<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=record'.AMP.'record_id='.$row['record_id'].AMP.'type=quote">'.$row['invoice_number'].'</a>',
							$row['created'],
							$row['updated'],
							$row['total'],
							'<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=view'.AMP.'record_id='.$row['unique_id'].'">'.lang('view').'</a>',
							'<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=send'.AMP.'type=quote'.AMP.'record_id='.$row['record_id'].'">'.lang('send').'</a>',
							'<a class="icon-trash" href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=delete'.AMP.'type=quote'.AMP.'record_id='.$row['record_id'].'"></a>',
						));
	}
	echo $this->table->generate();
	echo '<input type="submit" value="'.lang('submit').'" class="btn" name="submit" />';
	echo form_close();
}
?>
</div>