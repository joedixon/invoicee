<?php
include('header.php');
?>
<div class="rightNav">
	<span class="button">
		<?php echo '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=record'.AMP.'type=invoice" class="submit">' . lang('create_invoice') . '</a>'; ?>
	</span>
</div>
<div class="clear"></div>
<div id="filterMenu">
	<fieldset>
		<legend><?=lang('search_invoices')?></legend>

	<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records', array('class' => 'form-search'))?>
	<?php
		$input = array(
		              'name'        => 'keyword',
		              'id'          => 'keywords',
		              'value'       => $keyword,
					  'class'		=> 'input-medium search-query',
					  'style'		=> 'width:206px'
		            );
	?>
		<div>
			<label for="keywords" class="js_hide"><?=lang('keywords')?></label>
			<?=form_input($input, NULL,  'placeholder="'.lang('keywords').'"')?>
			<?= form_submit('submit', lang('search'), 'class="btn" id="search_button"').NBS.NBS?>			
		</div>

	<?=form_close()?>
	
	</fieldset>
	
</div> <!-- filterMenu -->
<div>	
<?php
if(isset($results) && $results == 0){
	echo '<h2>' . lang('no_invoices') . '</h2>';
}
else{
	$this->table->clear();
	$cp_table_template['heading_cell_start'] = '<th style="width:20%">';
	$cp_table_template['table_open'] = '<table class="table table-striped table-bordered">';
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(array(lang('invoice_number'), lang('created'), lang('updated'), lang('total'), lang('status'), lang('view'), lang('send'), lang('delete')));
	echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records'.AMP.'type=invoices');
	
	foreach($data as $row){
		$options = array(
		                  '0'  => lang('outstanding'),
		                  '1'    => lang('paid'),
		                );
		$status = form_dropdown($row['record_id'], $options, $row['status']);
	
		$this->table->add_row(array(
							'<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=record'.AMP.'record_id='.$row['record_id'].AMP.'type=invoice">'.$row['invoice_number'].'</a>',
							$row['created'],
							$row['updated'],
							$row['total'],
							$status,
							'<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=view'.AMP.'record_id='.$row['unique_id'].'">'.lang('view').'</a>',
							'<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=send'.AMP.'type=invoice'.AMP.'record_id='.$row['record_id'].'">'.lang('send').'</a>',
							'<a class="icon-trash" href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=delete'.AMP.'type=invoice'.AMP.'record_id='.$row['record_id'].'"></a>',
						));
	}
	echo $this->table->generate();
	echo '<input type="submit" value="'.lang('submit').'" class="submit" name="submit" />';
	echo form_close();
}
?>
</div>