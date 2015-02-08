<?php
include('header.php');
$this->table->clear();
$cp_table_template['heading_cell_start'] = '<th style="width:25%">';
$cp_table_template['table_open'] = '<table class="table table-bordered">';
$this->table->set_template($cp_table_template);
$cell = array('data' => lang($title), 'colspan' => 2);
$this->table->set_heading($cell);
echo form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method='.$method);
if(isset($record_id)){
	echo form_hidden('record_id', $record_id);
}
if(isset($error['invoice_no_error'])){
$this->table->add_row(array(
						lang('invoice_no'),
						form_input('invoice_no') . '<span class="notice">' . $error['invoice_no_error'] . '</span>',
					));
}
else{
	$data = array(
		  'name'        => 'invoice_no',
		  'class'          => 'field',
		  'value'       => '',
		  'style'       => 'width:25%;',
		  'value'		=> $record_name,
		);
	$this->table->add_row(array(
							lang('invoice_no'),
							form_input($data),
						));
}
if(isset($error['date_error'])){
	$data = array(
		  'name'        => 'date',
		  'class'       => 'field',
		  'maxlength'   => '100',
		  'size'        => '50',
		  'style'       => 'width:25%;',
		  'id'			=> 'invoice_date',
		);
$this->table->add_row(array(
						lang('date'),
						form_input($data) . '<span class="notice">' . $error['date_error'] . '</span>',
					));
}
else{
	$data = array(
		  'name'        => 'date',
		  'class'          => 'field',
		  'value'       => $date,
		  'maxlength'   => '100',
		  'size'        => '50',
		  'style'       => 'width:25%;',
		  'id'			=> 'invoice_date',
		);
		$this->table->add_row(array(
								lang('date'),
								form_input($data),
							));
}
if(!isset($customer)){
	$customer = '';
}
$this->table->add_row(array(
						lang('client'),
						form_dropdown('member', $members, $customer, 'class="span6"'),
					));
echo $this->table->generate();

$this->table->clear();

$cp_table_template['table_open'] = '<table class="table table-bordered" id="record_table">';
$this->table->set_template($cp_table_template);
$this->table->set_heading(
						array('data' => lang('record'), 'style' => 'width:30%'), 
						array('data' => lang('description'), 'style' => 'width:35%'),  
						array('data' => lang('units'), 'style' => 'width:10%'),  
						array('data' => lang('rate'), 'style' => 'width:10%'), 
						array('data' => lang('tax'), 'style' => 'width:10%'),
						array('data' => lang('remove'), 'style' => 'width:5%')
					);
//print_r($records);
if(isset($records)){
	$counter = 0;										
	foreach($records as $k => $record){
		$form = array();
		foreach($record as $key => $val){
			//echo $key . ' ' . $val . '<br />';
			if($key == 'record_rate'){
				if(isset($errors["record[$k][$key]"])){
					$form[] = '<div class="input-prepend"><span class="add-on">'.$symbol.'</span>'.form_input("record[$k][$key]", $val, 'id="prependedInput" class="span2"').'</div><span class="notice">' . $errors["record[$k][$key]"] . '</span>';
				}
				else{
					$form[] = '<div class="input-prepend"><span class="add-on">'.$symbol.'</span>'.form_input("record[$k][$key]", $val, 'id="prependedInput" class="span2"').'</div>';
				}
			}
			else{
				if(isset($errors["record[$k][$key]"])){
					$form[] = form_input("record[$k][$key]", $val) . '<span class="notice">' . $errors["record[$k][$key]"] . '</span>';
				}
				else{
					$form[] = form_input("record[$k][$key]", $val);
				}
			}				
		}
		$counter++;
		$form[] = "<a href='#' class='icon-trash' id='$k'></a>";
		$this->table->add_row($form);
	}
}
else{
	$this->table->add_row(array(
			form_input("record[0][record]"),
			form_input("record[0][record_description]"),
			form_input("record[0][record_units]"),
			'<div class="input-prepend"><span class="add-on">'.$symbol.'</span>'.form_input(array('name' => 'record[0][record_rate]', 'id' => 'prependedInput', 'class' => 'span2')).'</div>',
			form_input("record[0][record_tax]"),
			"<a href='#' class='icon-trash'></a>"
	));
}
echo $this->table->generate();
echo '<a class="btn btn-small" href="#" id="record"><i class="icon-plus-sign"></i>'.lang('add_record').'</a><br /><br />';
$this->table->clear();

echo '<input type="submit" value="'.lang('submit').'" class="btn btn-primary" name="submit" />';
echo form_close();

echo "<script>";
//echo "counter = 1;";
echo '$(document).ready(function(){
	var counter = $("#record_table tr:last").index() + 1;
	$(document).on("click", "#record", function(evt){		
		var c = $("#record_table tr:last").attr("class");
		if(c=="odd"){c="even";}else{c="odd";}
		$("#record_table tr:last").after("<tr class="+c+"><td><input type=\'text\' name=\'record["+counter+"][record]\'></td><td><input type=\'text\' name=\'record["+counter+"][record_description]\'></td><td><input type=\'text\' name=\'record["+counter+"][record_units]\'></td><td><div class=\'input-prepend\'><span class=\'add-on\'>'.$symbol.'</span><input type=\'text\' name=\'record["+counter+"][record_rate]\' id=\'prependedInput\' class=\'span2\'></div></td><td><input type=\'text\' name=\'record["+counter+"][record_tax]\'></td><td><a href=\'#\' class=\'icon-trash\'></a></td></tr>");evt.preventDefault();counter++;
	});
	$(document).on("click", ".icon-trash", function(evt){
		console.log(this);$(this).parent().parent().remove();});
});
</script>';


