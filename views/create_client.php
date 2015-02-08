<?php
include('header.php');
$this->table->clear();
$cp_table_template['heading_cell_start'] = '<th style="width:25%">';

$this->table->set_template($cp_table_template);
$cell = array('data' => lang('new_client_info'), 'colspan' => 2);
$this->table->set_heading($cell);
echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=create_client');
if($member_id = $this->input->get('id')){
	echo form_hidden('id', $member_id);
}
if(isset($error['client_email_error'])){
	$this->table->add_row(array(
							lang('client_email'),
							form_input('client_email', $form_data['client_email']) . '<span class="notice">' . $error['client_email_error'] . '</span>',
							));
}
else{
	$this->table->add_row(array(
							lang('client_email'),
							form_input('client_email', $form_data['client_email']),
							));
}
if(isset($error['client_username_error'])){
	$this->table->add_row(array(
							lang('client_username'),
							form_input('client_username', $form_data['client_username']) . '<span class="notice">' . $error['client_username_error'] . '</span>',
							));
}
else{
	$this->table->add_row(array(
							lang('client_username'),
							form_input('client_username', $form_data['client_username']),
							));
}
foreach($settings as $key => $val){
	if(isset($error[$key . '_error'])){
		$this->table->add_row(array(
								lang($key),
								form_input($key, $form_data[$key]) . '<span class="notice">' . $error[$key . '_error'] . '</span>',
								));
	}
	else{
		$this->table->add_row(array(
								lang($key),
								form_input($key, $form_data[$key]),
								));
	}
}
echo $this->table->generate();
echo '<input type="submit" value="' . lang('submit') . '" class="submit" name="submit" />';
echo form_close();