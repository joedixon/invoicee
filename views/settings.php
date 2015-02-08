<?php
include('header.php');
$this->table->clear();
$cp_table_template['heading_cell_start'] = '<th style="width:50%">';
$cp_table_template['table_open'] = '<table class="table table-bordered table-striped">';
$cp_table_template['heading_row_start'] = '<tr class="info">';
$this->table->set_template($cp_table_template);
$cell = array('data' => lang('client_settings'), 'colspan' => 2);
$this->table->set_heading($cell);
echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=settings');
$this->table->add_row(array(
						lang('client_member_group'),
						form_dropdown('member_groups', $member_groups, $client_settings['member_groups']),
					));
$this->table->add_row(array(
						lang('client_first_name'),
						form_dropdown('client_first_name', $member_fields, $client_settings['client_first_name']),
					));
$this->table->add_row(array(
						lang('client_surname'),
						form_dropdown('client_surname', $member_fields, $client_settings['client_surname']),
));
$this->table->add_row(array(
						lang('client_address_one'),
						form_dropdown('client_address_one', $member_fields, $client_settings['client_address_one']),
					));
$this->table->add_row(array(
						lang('client_address_two'),
						form_dropdown('client_address_two', $member_fields, $client_settings['client_address_two']),
					));
$this->table->add_row(array(
						lang('client_town'),
						form_dropdown('client_town', $member_fields, $client_settings['client_town']),
					));
$this->table->add_row(array(
						lang('client_county'),
						form_dropdown('client_county', $member_fields, $client_settings['client_county']),
					));
$this->table->add_row(array(
						lang('client_postcode'),
						form_dropdown('client_postcode', $member_fields, $client_settings['client_postcode']),
					));
$this->table->add_row(array(
						lang('client_telephone'),
						form_dropdown('client_telephone', $member_fields, $client_settings['client_telephone']),
					));
$this->table->add_row(array(
						lang('client_vat_number'),
						form_dropdown('client_vat_number', $member_fields, $client_settings['client_vat_number']),
					));
echo $this->table->generate();

$cell = array('data' => lang('email_settings'), 'colspan' => 2);
$this->table->set_heading($cell);
if($email_settings['email_send_admin']){
	$radio = '<label class="radio inline"><input class="radio" id="inlineCheckbox1" type="radio" name="email_send_admin" value="1" checked="checked" /> ' . lang('yes') . '</label><br />
	<label class="radio inline"><input class="radio" id="inlineCheckbox2" type="radio" name="email_send_admin" value="0" /> ' . lang('no') . '</label>';
}
else{
	$radio = '<label class="radio inline"><input class="radio" id="inlineCheckbox1" type="radio" name="email_send_admin" value="1" /> ' . lang('yes') . '</label><br />
	<label class="radio inline"><input class="radio" id="inlineCheckbox2" type="radio" name="email_send_admin" value="0" checked="checked" /> ' . lang('no') . '</label>';
}
$this->table->add_row(array(
						lang('email_send_admin'),
						$radio,
					));
$this->table->add_row(array(
						lang('email_from_address'),
						form_input('email_from_address', $email_settings['email_from_address']),
					));
$this->table->add_row(array(
						lang('email_from_name'),
						form_input('email_from_name', $email_settings['email_from_name']),
					));
$this->table->add_row(array(
						lang('email_subject'),
						form_input('email_subject', $email_settings['email_subject']),
					));

echo $this->table->generate();

$cell = array('data' => lang('invoice_settings'), 'colspan' => 2);
$this->table->set_heading($cell);
$this->table->add_row(array(
						lang('invoice_prefix'),
						form_input('invoice_prefix', $invoice_settings['invoice_prefix']),
					));
$this->table->add_row(array(
						lang('invoice_length'),
						form_input('invoice_length', $invoice_settings['invoice_length']),
					));
$this->table->add_row(array(
						lang('invoice_symbol'),
						form_input('invoice_symbol', $invoice_settings['invoice_symbol']),
					));
$this->table->add_row(array(
						lang('invoice_template'),
						form_dropdown('invoice_template', $templates, $invoice_settings['invoice_template']),
					));
echo $this->table->generate();

$cell = array('data' => lang('quote_settings'), 'colspan' => 2);
$this->table->set_heading($cell);
$this->table->add_row(array(
						lang('quote_prefix'),
						form_input('quote_prefix', $quote_settings['quote_prefix']),
					));
$this->table->add_row(array(
						lang('quote_length'),
						form_input('quote_length', $quote_settings['quote_length']),
					));
$this->table->add_row(array(
							lang('quote_symbol'),
							form_input('quote_symbol', $quote_settings['quote_symbol']),
						));
$this->table->add_row(array(
						lang('quote_template'),
						form_dropdown('quote_template', $templates, $quote_settings['quote_template']),
					));
echo $this->table->generate();

echo '<input type="submit" value="submit" class="btn btn-primary" name="submit" />';
echo form_close();