<?php
include('header.php');
?>
<div class="rightNav">
	<span class="button">
		<?php echo '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=create_client" class="submit">' . lang('create_client') . '</a>'; ?>
	</span>
</div>
<div class="clear"></div>
<div id="filterMenu">
	<fieldset>
		<legend><?=lang('search_clients')?></legend>

	<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=clients', array('class' => 'form-search'))?>
	<?php
		$input = array(
		              'name'        => 'keyword',
		              'id'          => 'keywords',
		              'value'       => $keyword,
					  'class'		=> 'search-query',
					  'style'		=> 'box-sizing:content-box;width:206px',
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
	echo '<h2>' . lang('no_clients') . '</h2>';
}
else{
	$this->table->clear();
	//$cp_table_template['heading_cell_start'] = '<th style="width:25%">';
	$cp_table_template['table_open'] = '<table class="table table-striped table-bordered">';
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(array(lang('client_id'), lang('client_name'), lang('edit_user'), lang('client_email'), lang('join_date'), lang('client_address')));
	foreach($data as $row){
		$this->table->add_row(array(
							$row['id'],
							$row['name'],
							'<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=create_client'.AMP.'id='.$row['id'].'">'.$row['username'].'</a>',
							'<a href="mailto:"'.$row['email'].'">'.$row['email'].'</a>',
							date('d M Y', $row['date_created']),
							$row['address']
						));
	}
	echo $this->table->generate();
}
?>
</div>