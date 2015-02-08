<?php
function create_record(){
	$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
	$type = $this->EE->input->get('type');
	$vars['type'] = $type;
	$vars['title'] = 'create_'.$type;
	$result = $this->EE->db->get_where('invoicee_type', array('type_name' => $type));
	if($result->num_rows() == 0){
		return show_error(lang('no_matching_type'));
	}
	else{
		$type_id = $result->row('type_id');
	}
	$this->no_settings();
	//load some js libraries
	$this->EE->cp->add_js_script(array(
		'ui'	 => array('datepicker')
	));
	
	//out the data for the date picker
	$this->EE->javascript->output('$(function(){$("#invoice_date").datepicker({dateFormat: "yy-mm-dd"});});');
	$vars['method'] = 'create_record'.AMP.'type='.$type;
	//if the form is submitted...
	if($this->EE->input->post('submit')){
		//check to see if the invoice name exists
		//print_r($this->EE->input->get('record_id'));
		if($this->EE->input->get('record_id') == ""){
			$query = $this->EE->db->get_where('invoicee_record', array('name' => $this->EE->input->post('invoice_no')));
			if($query->num_rows() > 0){
				//if it does, throw an error
				return show_error(lang($type.'_exists'));
			}
		}
		
		//generate the config for the form validatio of the invoice name, member or date
		$config = array( 
					array(
						'field'  => 'invoice_no', 
						'label'  => 'lang:invoice_no', 
						'rules'  => 'required|trim'
						),
					array(
						'field'  => 'member', 
						'label'  => 'lang:member', 
						'rules'  => 'required|trim'
						),
					array(
						'field'  => 'date', 
						'label'  => 'lang:date', 
						'rules'  => 'required|trim'
						),									
		);
		
		//this loops over all the task entries created and sets the form validation config
		//it also outputs the data back to the form
		$counter = 0;
		foreach($_POST as $key => $val){
			if(preg_match('/^record/', $key) && !preg_match('/^record_/', $key)){
				$counter = str_replace('record', '', $key);
				
				$config[] = array(
					'field'  => 'record'.$counter, 
					'label'  => 'lang:record', 
					'rules'  => 'required|trim'
				);
				$config[] = array(
					'field'  => 'record_units'.$counter, 
					'label'  => 'lang:units', 
					'rules'  => 'required|trim|numeric'
				);
				$config[] = array(
					'field'  => 'record_rate'.$counter, 
					'label'  => 'lang:rate', 
					'rules'  => 'required|trim|numeric'
				);
				$vars['records'][$counter] = array(
									'name' => $this->EE->input->post('record'.$counter), 
									'description' => $this->EE->input->post('record_description'.$counter), 
									'units' => $this->EE->input->post('record_units'.$counter), 
									'rate' => $this->EE->input->post('record_rate'.$counter), 
									'tax' => $this->EE->input->post('record_tax'.$counter), 
								);
			}
		}
		//sets the record counter for the js
		$vars['record_counter'] = $counter+1;
		
		$this->EE->form_validation->set_rules($config);
		//run the form validation
		if ($this->EE->form_validation->run() === FALSE){
			if(isset($this->EE->form_validation->_error_array) && !empty($this->EE->form_validation->_error_array)){
			 	foreach($this->EE->form_validation->_error_array as $k => $v){
					$vars['error'][$k.'_error'] = $v;
				}
			}
			//Grab the module settings
			$settings = $this->EE->invoicee->_get_settings();
			
			//Grab the correct currency symbol
			$vars['symbol'] = $settings[$type.'_settings'][$type.'_symbol'];
			
			//Get a list of members for the dropdown
			$this->EE->db->select('*');
			$this->EE->db->from('members');
			$this->EE->db->join('member_data', 'member_data.member_id = members.member_id');
			$this->EE->db->where('group_id', $settings['client_settings']['member_groups']);
			$query = $this->EE->db->get();
			
			foreach($query->result_array() as $row){
				$vars['members'][$row['member_id']] = $row['m_field_id_'.$settings['client_settings']['client_first_name']] . ' ' . $row['m_field_id_'.$settings['client_settings']['client_surname']] . ' - ' . $row['email'];
			}
			
			//prep the rest of the data for the form
			$vars['record_name'] = $this->EE->input->post('invoice_no');
			$vars['date'] = $this->EE->input->post('date');
			
			//load the view
			return $this->EE->load->view('create_record', $vars, TRUE);
		}
		//if there are no errors, we prep the data for entering into the db
		$data = array(
		   'created_at' => strtotime($this->EE->input->post('date')),
		   'updated_at' => strtotime($this->EE->input->post('date')),
		   'has_paid' => 0,
		   'member_id' => $this->EE->input->post('member'),
		   'name' => $this->EE->input->post('invoice_no'),
		   'unique_id' => $this->EE->functions->random('encrypt', 5),
		   'type_id' => $type_id
		);
		
		//insert into the invoice table
		$this->EE->db->insert('invoicee_record', $data);
		$record_id = $this->EE->db->insert_id();
		//prep the data
		foreach($_POST as $key => $val){
			if(preg_match('/^record/', $key) && !preg_match('/^record_/', $key)){
				$counter = str_split($key, 6);
				$counter = $counter[1];
				//echo $counter.'<br />';
				$records[] = array(
									'record_id' => $record_id,
									'title' => $this->EE->input->post('record'.$counter),
									'description' => $this->EE->input->post('record_description'.$counter), 
									'units' => $this->EE->input->post('record_units'.$counter), 
									'rate' => $this->EE->input->post('record_rate'.$counter), 
									'tax' => $this->EE->input->post('record_tax'.$counter),
									'total' => $this->EE->input->post('record_units'.$counter)*$this->EE->input->post('record_rate'.$counter)*(($this->EE->input->post('record_tax'.$counter)/100)+1)
								);
			}
		}
		//print_r($records);
		if(isset($records)){
			$this->EE->db->insert_batch('invoicee_record_data', $records);
		}	
	}
	
	$this->EE->javascript->compile();
	//$vars = array();
	//Set the page title
	$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('create_'.$type));
	
	//Grab the module settings
	$settings = $this->EE->invoicee->_get_settings();
	
	//Grab the correct currency symbol
	$vars['symbol'] = $settings[$type.'_settings'][$type.'_symbol'];
	
	//Get a list of members for the dropdown
	$this->EE->db->select('*');
	$this->EE->db->from('members');
	$this->EE->db->join('member_data', 'member_data.member_id = members.member_id');
	$this->EE->db->where('group_id', $settings['client_settings']['member_groups']);
	$query = $this->EE->db->get();
	
	foreach($query->result_array() as $row){
		$vars['members'][$row['member_id']] = $row['m_field_id_'.$settings['client_settings']['client_first_name']] . ' ' . $row['m_field_id_'.$settings['client_settings']['client_surname']] . ' - ' . $row['email'];
	}
	
	//get the latest invoice based on the prefix in the settings
	$this->EE->db->select('*');
	$this->EE->db->from('invoicee_record');
	$where = "name LIKE '" . $settings[$type.'_settings'][$type.'_prefix'] . "%'";
	$this->EE->db->where($where);
	$this->EE->db->order_by('name', 'DESC');
	$this->EE->db->limit('1');
	$query = $this->EE->db->get();
	
	//if there are no invoices, we craete the first entry
	if($query->num_rows() == 0){
		$inv = str_pad(1, $settings[$type.'_settings'][$type.'_length'] - strlen($settings[$type.'_settings'][$type.'_prefix']), '0', STR_PAD_LEFT);
		$vars['record_name'] = $settings[$type.'_settings'][$type.'_prefix'] . $inv;
	}
	//if there are some entries, we calculate the next
	else{
		$inv = str_replace($settings[$type.'_settings'][$type.'_prefix'], '', $query->row('name')) + 1;
		$inv = str_pad($inv, $settings[$type.'_settings'][$type.'_length'] - strlen($settings[$type.'_settings'][$type.'_prefix']), '0', STR_PAD_LEFT);
		$vars['record_name'] = $settings[$type.'_settings'][$type.'_prefix'] . $inv;
	}
	//set the date
	$vars['date'] = date('Y-m-d', time());
	
	//these counters are used in the js for creating additional rows				
	$vars['record_counter'] = 0;
	
	//load the view
	if(isset($record_id)){
		$this->EE->session->set_flashdata('success', lang($type.'_updated'));
		$query = $this->EE->db->get_where('invoicee_record', array('record_id' => $record_id));
$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records'.AMP.'keyword='.$query->row('name').AMP.'type='.$type.'s');
	}
	else{
		return $this->EE->load->view('create_record', $vars, TRUE);
	}
}

function edit_record(){
	$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
	$type = $this->EE->input->get('type');
	$vars['type'] = $type;
	$vars['title'] = 'edit_'.$type;
	$result = $this->EE->db->get_where('invoicee_type', array('type_name' => $type));
	if($result->num_rows() == 0){
		return show_error(lang('no_matching_type'));
	}
	else{
		$type_id = $result->row('type_id');
	}
	$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('edit_'.$type));
	$this->no_settings();
	if($record_id = $this->EE->input->get_post('record_id')){
		$vars['record_id'] = $record_id;
		//load some js libraries
		$this->EE->cp->add_js_script(array(
			'ui'	 => array('datepicker')
		));
		$vars['method'] = 'edit_record'.AMP.'record_id='.$record_id.AMP.'type='.$type;
		//out the data for the date picker
		$this->EE->javascript->output('$(function(){$("#invoice_date").datepicker({dateFormat: "yy-mm-dd"});});');
		
		//if the form has been submitted
		if($this->EE->input->post('submit')){
			//check that record name doesn't already exist
			
			//generate the config for the form validatio of the invoice name, member or date
			$config = array( 
						array(
							'field'  => 'invoice_no', 
							'label'  => 'lang:invoice_no', 
							'rules'  => 'required|trim'
							),
						array(
							'field'  => 'member', 
							'label'  => 'lang:member', 
							'rules'  => 'required|trim'
							),
						array(
							'field'  => 'date', 
							'label'  => 'lang:date', 
							'rules'  => 'required|trim'
							),									
			);

			//this loops over all the task entries created and sets the form validation config
			//it also outputs the data back to the form
			$counter = 0;
			foreach($_POST as $key => $val){
				if(preg_match('/^record/', $key) && !preg_match('/^record_/', $key)){
					$counter = str_replace('record', '', $key);
					
					$config[] = array(
						'field'  => 'record'.$counter, 
						'label'  => 'lang:record', 
						'rules'  => 'required|trim'
					);
					$config[] = array(
						'field'  => 'record_units'.$counter, 
						'label'  => 'lang:units', 
						'rules'  => 'required|trim|numeric'
					);
					$config[] = array(
						'field'  => 'record_rate'.$counter, 
						'label'  => 'lang:rate', 
						'rules'  => 'required|trim|numeric'
					);
					$vars['records'][$counter] = array(
										'name' => $this->EE->input->post('record'.$counter), 
										'description' => $this->EE->input->post('record_description'.$counter), 
										'units' => $this->EE->input->post('record_units'.$counter), 
										'rate' => $this->EE->input->post('record_rate'.$counter), 
										'tax' => $this->EE->input->post('record_tax'.$counter), 
									);
					}
					//$counter++;
				}
				//sets the record counter for the js
				$vars['record_counter'] = $counter+1;

				$this->EE->form_validation->set_rules($config);
				//run the form validation
				if ($this->EE->form_validation->run() === FALSE){
					if(isset($this->EE->form_validation->_error_array) && !empty($this->EE->form_validation->_error_array)){
					 	foreach($this->EE->form_validation->_error_array as $k => $v){
							$vars['error'][$k.'_error'] = $v;
						}
	 				}
					//Grab the module settings
					$settings = $this->EE->invoicee->_get_settings();
					
					//Grab the correct currency symbol
					$vars['symbol'] = $settings[$type.'_settings'][$type.'_symbol'];

					//Get a list of members for the dropdown
					$this->EE->db->select('*');
					$this->EE->db->from('members');
					$this->EE->db->join('member_data', 'member_data.member_id = members.member_id');
					$this->EE->db->where('group_id', $settings['client_settings']['member_groups']);
					$query = $this->EE->db->get();

					foreach($query->result_array() as $row){
						$vars['members'][$row['member_id']] = $row['m_field_id_'.$settings['client_settings']['client_first_name']] . ' ' . $row['m_field_id_'.$settings['client_settings']['client_surname']] . ' - ' . $row['email'];
					}

					//prep the rest of the data for the form
					$vars['record_name'] = $this->EE->input->post('invoice_no');
					$vars['date'] = $this->EE->input->post('date');

					//load the view
					return $this->EE->load->view('create_record', $vars, TRUE);
			}
			//get the task data
			$query = $this->EE->db->get_where('invoicee_record_data', array('record_id' => $record_id));
			$counter = 0;
			
			$data = array(
			   'updated_at' => time(),
			   'has_paid' => 0,
			   'member_id' => $this->EE->input->post('member'),
			   'name' => $this->EE->input->post('invoice_no'),
			   'type_id' => $type_id
			);

			//insert into the invoice table
			$this->EE->db->where('record_id', $record_id);
			$this->EE->db->update('invoicee_record', $data);
			
			foreach($query->result_array() as $row){
				if($this->EE->input->post('record'.$row['record_data_id'])){
					$data = array(
								'title' => $this->EE->input->post('record'.$row['record_data_id']),
								'description' => $this->EE->input->post('record_description'.$row['record_data_id']),
								'units' => $this->EE->input->post('record_units'.$row['record_data_id']),
								'rate' => $this->EE->input->post('record_rate'.$row['record_data_id']),
								'tax' => $this->EE->input->post('record_tax'.$row['record_data_id']),
								'total' => $this->EE->input->post('record_units'.$row['record_data_id'])*$this->EE->input->post('record_rate'.$row['record_data_id'])*(($this->EE->input->post('record_tax'.$row['record_data_id'])/100)+1),
								'updated_at' => time(),
					);
					$this->EE->db->where('record_data_id', $row['record_data_id']);
					$this->EE->db->update('invoicee_record_data', $data);
					$counter = $row['record_data_id']+1;
				}
				else{
					$this->EE->db->delete('invoicee_record_data', array('record_data_id' => $row['record_data_id']));
				}
				unset($_POST['record'.$row['record_data_id']]);
			}
			
			//prep the task data
			foreach($_POST as $key => $val){
				if(preg_match('/^record/', $key) && !preg_match('/^record_/', $key)){
					$counter = str_split($key, 6);
					$counter = $counter[1];
					$record_data[] = array(
										'record_id' => $record_id,
										'title' => $this->EE->input->post('record'.$counter),
										'description' => $this->EE->input->post('record_description'.$counter), 
										'units' => $this->EE->input->post('record_units'.$counter), 
										'rate' => $this->EE->input->post('record_rate'.$counter), 
										'tax' => $this->EE->input->post('record_tax'.$counter),
										'total' => $this->EE->input->post('record_units'.$counter)*$this->EE->input->post('task_rate'.$counter)*(($this->EE->input->post('task_tax'.$counter)/100)+1)
									);
				}
				
			}
	
			if(isset($record_data)){
				$this->EE->db->insert_batch('invoicee_record_data', $record_data);
			}
		
			$this->EE->session->set_flashdata('success', lang($type.'_updated'));
			$query = $this->EE->db->get_where('invoicee_record', array('record_id' => $record_id));
$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records'.AMP.'keyword='.$query->row('name').AMP.'type='.$type.'s');							
		}
		//get the invoice data
		$query = $this->EE->db->get_where('invoicee_record', array('record_id' => $record_id));
		if($query->num_rows() == 0){
			return show_error(lang('no_invoice'));
		}
		else{
			$vars['record_name'] = $query->row('name');
			$vars['customer'] = $query->row('member_id');
			$vars['date'] = date('Y-m-d', $query->row('created_at'));
			$settings = $this->EE->invoicee->_get_settings();
			
			//Grab the correct currency symbol
			$vars['symbol'] = $settings[$type.'_settings'][$type.'_symbol'];
			
			//Get a list of members for the dropdown
			$this->EE->db->select('*');
			$this->EE->db->from('members');
			$this->EE->db->join('member_data', 'member_data.member_id = members.member_id');
			$this->EE->db->where('group_id', $settings['client_settings']['member_groups']);
			$query = $this->EE->db->get();

			foreach($query->result_array() as $row){
				$vars['members'][$row['member_id']] = $row['m_field_id_'.$settings['client_settings']['client_first_name']] . ' ' . $row['m_field_id_'.$settings['client_settings']['client_surname']] . ' - ' . $row['email'];
			}
			
			
			//get tasks
			$query = $this->EE->db->get_where('invoicee_record_data', array('record_id' => $record_id));
			$counter = 0;
			if($query->num_rows() == 0){
				$vars['record_counter'] = 0;
			}
			else{
				foreach($query->result_array() as $row){
					$vars['records'][$row['record_data_id']] = array(
										'name' => $row['title'], 
										'description' => $row['description'], 
										'units' => $row['units'], 
										'rate' => $row['rate'], 
										'tax' => $row['tax'], 
									);
					$counter++;
				}
				$vars['record_counter'] = $row['record_data_id']+1;
			}
		}
		return $this->EE->load->view('create_record', $vars, TRUE);
	}
	else{
		return show_error(lang('no_invoice'));
	}
}
?>