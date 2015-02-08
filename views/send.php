<?php
include('header.php');
$this->table->clear();
$cp_table_template['heading_cell_start'] = '<th style="width:25%">';
$cp_table_template['table_open'] = '<table class="table table-bordered">';
$this->table->set_template($cp_table_template);
$cell = array('data' => lang($title), 'colspan' => 2);
$this->table->set_heading($cell);
echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=send');
if(isset($record_id)){
	echo form_hidden('record_id', $record_id);
}
	$data = array(
		  'name'        => 'to',
		  'class'          => 'field',
		  'value'       => '',
		  'style'       => 'width:25%;',
		  'value'		=> $client_first_name . ' ' . $client_surname,
		);
	$this->table->add_row(array(
							lang('to'),
							form_input($data),
						));
						
	$data = array(
		  'name'        => 'to',
		  'class'          => 'field',
		  'value'       => '',
		  'style'       => 'width:25%;',
		  'value'		=> $email,
		);
	$this->table->add_row(array(
							lang('email'),
							form_input($data),
						));

echo $this->table->generate();
$this->table->clear();

echo '<input type="submit" value="'.lang('submit').'" class="btn btn-primary" name="submit" />';
echo form_close();