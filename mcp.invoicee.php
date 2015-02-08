<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoicee_mcp {
	function Invoicee_mcp()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->library('invoicee');
		$this->EE->load->helper('form');
		$this->EE->load->library('form_validation');
		$this->EE->load->helper(array('string', 'snippets'));
		$this->EE->cp->add_to_head("<link rel='stylesheet' href='".$this->EE->config->item('theme_folder_url') ."third_party/invoicee/css/bootstrap.css'>");
		$this->EE->cp->load_package_js('bootstrap');
		
		//Set the navigation
		$this->EE->cp->set_right_nav(array(
					lang('dashboard')  => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
						.AMP.'module=invoicee',
					lang('clients')  => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
						.AMP.'module=invoicee'.AMP.'method=clients',
					lang('projects')  => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
						.AMP.'module=invoicee'.AMP.'method=projects',
					lang('quotes')  => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
						.AMP.'module=invoicee'.AMP.'method=records'.AMP.'type=quotes',
					lang('invoices')  => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
						.AMP.'module=invoicee'.AMP.'method=records'.AMP.'type=invoices',
					lang('settings')  => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
						.AMP.'module=invoicee'.AMP.'method=settings',
					));
	}
	
	//Default control panel function
	function index(){
		$this->no_settings();
		//Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('title'));
		
		//Initilaise the $vars array
		$vars = array();
		$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
		
		//Get outstanding invoices
		$this->EE->db->select('invoicee_record.record_id, name, invoicee_record.created_at, invoicee_record.updated_at, invoicee_record_data.total as total, has_paid, type_name');
		$this->EE->db->join('invoicee_type', 'invoicee_type.type_id = invoicee_record.type_id');
		$this->EE->db->join('invoicee_record_data', 'invoicee_record_data.record_id = invoicee_record.record_id', 'left');
		$this->EE->db->group_by('name');
		$query = $this->EE->db->get('invoicee_record');
		$vars['prospective'] = 0;
		$vars['outstanding'] = 0;
		$vars['paid'] = 0;
		foreach($query->result_array() as $row){
			$sql = "SELECT SUM(total) as t_total FROM exp_invoicee_record_data WHERE record_id = " . $row['record_id'];
			$query = $this->EE->db->query($sql);
			$total = $query->row('t_total');
			if($row['type_name'] == 'quote'){
				$vars['prospective'] += $total;
			}
			elseif($row['type_name'] == 'invoice'){
				if($row['has_paid']){
					$vars['paid'] += $total;
				}
				else{
					$vars['outstanding'] += $total;
				}
			}
		}
		$invoices = array(
						lang('outstanding') => $vars['outstanding'],
						lang('paid') => $vars['paid'],
						lang('prospective') => $vars['prospective'],
		);
		$settings = $this->EE->invoicee->_get_settings('invoice');
		$vars['prospective']  = $settings['invoice_symbol'] . number_format($vars['prospective'], 2, '.', ',');
		$vars['outstanding']  = $settings['invoice_symbol'] . number_format($vars['outstanding'], 2, '.', ',');
		$vars['paid']  = $settings['invoice_symbol'] . number_format($vars['paid'], 2, '.', ',');
		$clients = $this->EE->invoicee->_get_settings('client');
		$vars['clients'] = $clients['member_groups'];
		
		$this->EE->javascript->output('drawChart('.$this->EE->javascript->generate_json($invoices, TRUE).', "'.lang('chart_title').'");');
		
		//Get (max 5) overdue invoices
		$this->EE->db->join('invoicee_type', 'invoicee_type.type_id = invoicee_record.type_id');
		$this->EE->db->where('DATE_SUB(CURDATE(), INTERVAL 30 DAY) > FROM_UNIXTIME(exp_invoicee_record.created_at)');
		$this->EE->db->where('type_name', 'invoice');
		$this->EE->db->where('has_paid', 0);
		$query = $this->EE->db->get('invoicee_record', 5);
		
		foreach($query->result_array() as $row){
			$query = $this->EE->db->get_where('invoicee_record_data', array('record_id' => $row['record_id']));
			$total = 0;
			foreach($query->result_array() as $row_two){
				$total += $row_two['total'];
			}
			$data = array(
						'invoice_id' => $row['record_id'],
						'name' => $row['name'],
						'total' => $settings['invoice_symbol'] . number_format($total, 2, '.', ',')
			);
			$vars['overdue'][] = $data;
		}
		
		//Return the $vars array and load the index view
		return $this->EE->load->view('index', $vars, TRUE);
	}
	
	//View and edit the settings of the module
	function settings(){
		if($this->EE->input->post('submit')){
			//Set the $client_settings array to store data for db entry
			$client_settings = array();
			
			//Set the $email_settings array to store the data for db entry
			$email_settings = array();
			
			//Set the $invoice_settings array to store the data for db entry
			$invoice_settings = array();
			
			//Set the $quote_settings array to store the data for db entry
			$quote_settings = array();
			
			//Populate the $client_settings array
			$client_settings['member_groups'] = $this->EE->input->post('member_groups');
			$client_settings['client_first_name'] = $this->EE->input->post('client_first_name');
			$client_settings['client_surname'] = $this->EE->input->post('client_surname');
			$client_settings['client_address_one'] = $this->EE->input->post('client_address_one');
			$client_settings['client_address_two'] = $this->EE->input->post('client_address_two');
			$client_settings['client_town'] = $this->EE->input->post('client_town');
			$client_settings['client_county'] = $this->EE->input->post('client_county');
			$client_settings['client_postcode'] = $this->EE->input->post('client_postcode');
			$client_settings['client_telephone'] = $this->EE->input->post('client_telephone');
			$client_settings['client_vat_number'] = $this->EE->input->post('client_vat_number');
			
			//Populate the $email_settings array
			$email_settings['email_send_admin'] = $this->EE->input->post('email_send_admin');
			$email_settings['email_from_address'] = $this->EE->input->post('email_from_address');
			$email_settings['email_from_name'] = $this->EE->input->post('email_from_name');
			$email_settings['email_subject'] = $this->EE->input->post('email_subject');
			
			//Populate the $invoice_settings array
			$invoice_settings['invoice_prefix'] = $this->EE->input->post('invoice_prefix');
			if($this->EE->input->post('invoice_length') != ''){
				$invoice_settings['invoice_length'] = $this->EE->input->post('invoice_length');
			}
			else{
				$invoice_settings['invoice_length'] = 10;
			}
			if($this->EE->input->post('invoice_symbol') != ''){
				$invoice_settings['invoice_symbol'] = $this->EE->input->post('invoice_symbol');
			}
			else{
				$invoice_settings['invoice_symbol'] = '£';
			}
			$invoice_settings['invoice_template'] = $this->EE->input->post('invoice_template');
			
			//Populate the $quote_settings array
			$quote_settings['quote_prefix'] = $this->EE->input->post('quote_prefix');
			if($this->EE->input->post('quote_length') != ''){
				$quote_settings['quote_length'] = $this->EE->input->post('quote_length');
			}
			else{
				$quote_settings['quote_length'] = 10;
			}
			if($this->EE->input->post('quote_symbol') != ''){
				$quote_settings['quote_symbol'] = $this->EE->input->post('quote_symbol');
			}
			else{
				$quote_settings['quote_symbol'] = '£';
			}
			$quote_settings['quote_template'] = $this->EE->input->post('quote_template');
			
			//Populate the db array with the json encoded arrays
			$db = array(
						'client_settings' => json_encode($client_settings),
						'email_settings' => json_encode($email_settings),
						'invoice_settings' => json_encode($invoice_settings),
						'quote_settings' => json_encode($quote_settings)
						);
			
			//If there are currently no settings in the db
			$query = $this->EE->db->get('invoicee_settings');
			if($query->num_rows() == 0){
				//Insert the new settings into the db
				$this->EE->db->insert('invoicee_settings', $db);
			}
			//If there are...
			else{
				//Update the new settings into the db
				$this->EE->db->update('invoicee_settings', $db);
			}
			$this->EE->session->set_flashdata('success', lang('settings_updated'));
	$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=settings');
		}
		
		//Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('settings'));
		
		//Initialise $vars array
		$vars = array();
		$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
		$settings = $this->EE->invoicee->_get_settings();
		$vars['client_settings'] = $settings['client_settings'];
		$vars['email_settings'] = $settings['email_settings'];
		$vars['invoice_settings'] = $settings['invoice_settings'];
		$vars['quote_settings'] = $settings['quote_settings'];
		
		//Get all the member group data for the settings
		$query = $this->EE->db->get('member_groups');
		$vars['member_groups'][''] = '---';
		foreach($query->result() as $row){
			$vars['member_groups'][$row->group_id] = $row->group_title;
		}
		
		//Get all the member field data
		$query = $this->EE->db->get('member_fields');
		$vars['member_fields'][''] = '---';
		foreach($query->result() as $row){
			$vars['member_fields'][$row->m_field_id] = $row->m_field_label;
		}
		
		//Get all the template data
		$this->EE->load->model('template_model');
		$templates = $this->EE->template_model->get_templates();
		foreach($templates->result() as $row){
			//[$row->template_id] = $row->template_name;
			$vars['templates'][$row->template_id] = $row->group_name.'/'.$row->template_name;
		}
		
		
		//Return the $vars array and load the settings view
		return $this->EE->load->view('settings', $vars, TRUE);
	}
	
	//This provides the data for viewing client data
	function clients(){
		$this->no_settings();
		//Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('clients'));
		
		//Initilaise the $vars array
		$vars = array();
		$vars['keyword'] = '';
		$settings = $this->EE->invoicee->_get_settings();

		//Get all the client data
		$this->EE->db->select('*');
		$this->EE->db->from('members');
		$this->EE->db->join('member_data', 'member_data.member_id = members.member_id');
		$this->EE->db->where('group_id', $settings['client_settings']['member_groups']);
		if($s = $this->EE->input->get_post('keyword')){
			$where = "group_id = " . $settings['client_settings']['member_groups'] . " AND (username LIKE '%$s%' OR email LIKE '%$s%')";
			$this->EE->db->where($where);
			$vars['keyword'] = $s;
		}
		$query = $this->EE->db->get();
		
		if($query->num_rows() == 0){
			$vars['results'] = 0;
		}
		else{
			$settings = $this->EE->invoicee->_get_settings('client');
			foreach($query->result_array() as $row){
				$address = $row['m_field_id_'.$settings['client_address_one']] . ', ';
				if($row['m_field_id_'.$settings['client_address_two']]){
					$address .= $row['m_field_id_'.$settings['client_address_two']] . ', ';
				}
				if($row['m_field_id_'.$settings['client_town']]){
					$address .= $row['m_field_id_'.$settings['client_town']] . ', ';
				}
				if($row['m_field_id_'.$settings['client_county']]){
					$address .= $row['m_field_id_'.$settings['client_county']] . ', ';
				}
				if($row['m_field_id_'.$settings['client_postcode']]){
					$address .= $row['m_field_id_'.$settings['client_postcode']];
				}
				$vars['data'][] = array(
										'id' 		=> 	$row['member_id'],
										'email'		=>	$row['email'],
										'name'	=>	$row['m_field_id_'.$settings['client_first_name']] . ' ' . $row['m_field_id_'.$settings['client_surname']],
										'address'	=>	$address,
										'username' => $row['username'],
										'date_created' => $row['join_date'],
										);
			}
		}
		$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
		
		//Return the $vars array and load the clients view
		return $this->EE->load->view('clients', $vars, TRUE);
	}
	
	function projects(){
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('projects'));
		//Initilaise the $vars array
		$vars = array();
		$vars['keyword'] = '';
		//$settings = $this->EE->invoicee->_get_settings();

		//Get all the client data
		$this->EE->db->select('*');
		$this->EE->db->from('invoicee_project');
		if($s = $this->EE->input->get_post('keyword')){
			$where = "project_name LIKE '%$s%' OR project_name LIKE '%$s%'";
			$this->EE->db->where($where);
			$vars['keyword'] = $s;
		}
		$query = $this->EE->db->get();
		
		if($query->num_rows() == 0){
			$vars['results'] = 0;
		}
		else{
			$settings = $this->EE->invoicee->_get_settings('client');
			foreach($query->result_array() as $row){
				$vars['data'][] = array(
										'id' 		=> 	$row['project_id'],
										'project_name'		=>	$row['project_name'],
										'client'	=>	$row['m_field_id_'.$settings['client_first_name']] . ' ' . $row['m_field_id_'.$settings['client_surname']],
										'address'	=>	$address,
										'username' => $row['username'],
										'date_created' => $row['join_date'],
										);
			}
		}
		$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
		
		//Return the $vars array and load the clients view
		return $this->EE->load->view('clients', $vars, TRUE);
	}
	
	//This provides the data for viewing invoices
	function records(){
		//if no type in the get request, throw an error
		if(!$type = $this->EE->input->get('type')){
			return show_error(lang('no_type'));
		}
		$this->no_settings();
		if($this->EE->input->post('submit')){
			foreach($_POST as $key => $val){
				if(is_numeric($key)){
					$data = array(
					               'has_paid' => $val,
					            );
					$this->EE->db->where('record_id', $key);
					$this->EE->db->update('invoicee_record', $data);
				}
			}
			$this->EE->session->set_flashdata('success', lang('status_updated'));
$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records'.AMP.'type='.$type);
		}
		//Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line($type));
		
		//Initilaise the $vars array
		$vars = array();
		$vars['keyword'] = '';
		
		//Get all the record data		
		$this->EE->db->select('invoicee_record.record_id, name, invoicee_record.created_at, invoicee_record.updated_at, invoicee_record_data.total as total, has_paid, unique_id');
		$this->EE->db->join('invoicee_record_data', 'invoicee_record_data.record_id = invoicee_record.record_id', 'left');
		$this->EE->db->join('invoicee_type', 'invoicee_type.type_id = invoicee_record.type_id');
		if($type == 'invoices'){
			$this->EE->db->where('type_name', 'invoice');
		}
		else{
			$this->EE->db->where('type_name', 'quote');
		}
		
		if($s = $this->EE->input->get_post('keyword')){
			$this->EE->db->like('name', $s);
			$vars['keyword'] = $s;
		}
		$this->EE->db->group_by('name');
		$this->EE->db->order_by('invoicee_record.record_id', 'DESC');
		$query = $this->EE->db->get('invoicee_record');
		if($query->num_rows() == 0){
			$vars['results'] = 0;
		}
		else{
			$settings = $this->EE->invoicee->_get_settings('invoice');
			foreach($query->result() as $row){
				$sql = "SELECT SUM(total) as t_total FROM exp_invoicee_record_data WHERE record_id = " . $row->record_id;
				$query = $this->EE->db->query($sql);
				$total = $query->row('t_total');
				$vars['data'][] = array(
										'invoice_number'	=>	$row->name,
										'record_id'	=>	$row->record_id,
										'created'	=>	date('d M Y', $row->created_at),
										'updated'	=>	date('d M Y', $row->updated_at),
										'total'		=> $settings['invoice_symbol'].number_format($total, 2, '.', ','),
										'status'	=> $row->has_paid,
										'unique_id'	=> $row->unique_id,
										);
			}
		}
		$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
		
		//Return the $vars array and load the invoices view
		return $this->EE->load->view($type, $vars, TRUE);
	}
	
	function create_client(){
		$this->no_settings();
		//Is the user allowed to create members?
		if ( ! $this->EE->cp->allowed_group('can_access_members') OR ! $this->EE->cp->allowed_group('can_admin_members'))
		{
			//If not, lets throw an error
			return show_error(lang('unauthorized_access'));
		}
		$vars = array();
		
		if($member_id = $this->EE->input->get('id')){
			$this->EE->db->from('members');
			$this->EE->db->join('member_data', 'members.member_id = member_data.member_id');
			$this->EE->db->where('members.member_id', $member_id);
			$query = $this->EE->db->get();
			$settings = $this->EE->invoicee->_get_settings('client');
			$vars['form_data'] = array(
				'client_email' 	=>	$query->row('email'),
				'client_username' 	=>	$query->row('username'),
				'client_first_name' 	=>	$query->row('m_field_id_'.$settings['client_first_name']),
				'client_surname' 	=>	$query->row('m_field_id_'.$settings['client_surname']),
				'client_address_one' 	=>	$query->row('m_field_id_'.$settings['client_address_one']),
				'client_address_two' 	=>	$query->row('m_field_id_'.$settings['client_address_two']),
				'client_town' 	=>	$query->row('m_field_id_'.$settings['client_town']),
				'client_county' 	=>	$query->row('m_field_id_'.$settings['client_county']),
				'client_postcode' 	=>	$query->row('m_field_id_'.$settings['client_postcode']),
				'client_telephone' 	=>	$query->row('m_field_id_'.$settings['client_telephone']),
				'client_vat_number' 	=>	$query->row('m_field_id_'.$settings['client_vat_number']),
			);
		}
		else{
			$vars['form_data'] = array(
				'client_email' 	=>	'',
				'client_username' 	=>	'',
				'client_first_name' 	=>	'',
				'client_surname' 	=>	'',
				'client_address_one' 	=>	'',
				'client_address_two' 	=>	'',
				'client_town' 	=>	'',
				'client_county' 	=>	'',
				'client_postcode' 	=>	'',
				'client_telephone' 	=>	'',
				'client_vat_number' 	=>	'',
			);
		}
		
		//Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('create_client'));
		$vars['settings'] = array_slice($this->EE->invoicee->_get_settings('client'), 1);
		//Has any data been submitted??
		if($this->EE->input->post('submit')){
			if($this->EE->input->get_post('id')){
				//$this->EE->form_validation->set_old_value('email', $this->EE->input->post('client_email'));
				//$this->EE->form_validation->set_old_value('username', $this->EE->input->post('client_username'));
				$username_rules = 'required|trim|valid_username';
				$email_rules = 'required|trim|valid_user_email';
			}
			else{
				$username_rules = 'required|trim|valid_username[new]';
				$email_rules = 'required|trim|valid_user_email[new]';
			}
			//Lets validate the data
			
			$config = array(
				array(
					'field'  => 'client_username', 
					'label'  => 'lang:client_username', 
					'rules'  => $username_rules
				),
				array(
					'field'  => 'client_email', 
					'label'  => 'lang:client_email', 
					'rules'  => $email_rules
				),
				array(
					'field'  => 'client_first_name', 
					'label'  => 'lang:client_first_name', 
					'rules'  => 'trim|required'
				),
				array(
					'field'  => 'client_surname', 
					'label'  => 'lang:client_surname', 
					'rules'  => 'trim|required'
				),
				array(
					'field'  => 'client_address_one', 
					'label'  => 'lang:client_address_one', 
					'rules'  => 'trim|required'
				),
				array(
					'field'  => 'client_postcode', 
					'label'  => 'lang:client_postcode', 
					'rules'  => 'trim|required'
				),
			);
			
			$this->EE->form_validation->set_rules($config);
			
			if ($this->EE->form_validation->run() === FALSE)
			{
				if(isset($this->EE->form_validation->_error_array) && !empty($this->EE->form_validation->_error_array)){
				 	foreach($this->EE->form_validation->_error_array as $k => $v){
						$vars['error'][$k.'_error'] = $v;
					}
 				}
				$vars['form_data']['client_email'] = $this->EE->input->post('client_email');
				$vars['form_data']['client_username'] = $this->EE->input->post('client_username');
				$vars['form_data']['client_first_name'] = $this->EE->input->post('client_first_name');
				$vars['form_data']['client_surname'] = $this->EE->input->post('client_surname');
				$vars['form_data']['client_address_one'] = $this->EE->input->post('client_address_one');
				$vars['form_data']['client_address_two'] = $this->EE->input->post('client_address_two');
				$vars['form_data']['client_town'] = $this->EE->input->post('client_town');
				$vars['form_data']['client_county'] = $this->EE->input->post('client_county');
				$vars['form_data']['client_postcode'] = $this->EE->input->post('client_postcode');
				$vars['form_data']['client_telephone'] = $this->EE->input->post('client_telephone');
				$vars['form_data']['client_vat_number'] = $this->EE->input->post('client_vat_number');
				return $this->EE->load->view('create_client', $vars, TRUE);
			}
			
			
			$settings = $this->EE->invoicee->_get_settings();
			$this->EE->load->model('member_model');
			$this->EE->load->library('validate');
			$email = $this->EE->input->post('client_email');
			$this->EE->validate->validate_email();
			$data = array(
				'email' => $this->EE->input->post('client_email'),
				'group_id' => $settings['client_settings']['member_groups'],
				'username' => $this->EE->input->post('client_username'),
				'screen_name' => $this->EE->input->post('client_username'),
				'password' => $this->EE->functions->random(),
				'salt' => $this->EE->functions->random(),
				'authcode' => $this->EE->functions->random('alpha', 10),
				'join_date' => time(),
			);
			
			$cdata = array();
			$client_settings = array_slice($settings['client_settings'], 1);
			foreach($client_settings as $key => $val){
				$cdata['m_field_id_' . $val] = $this->EE->input->post($key);
			}			
			
			if($member_id = $this->EE->input->post('id')){
				$this->EE->member_model->update_member($member_id, $data);
				$this->EE->member_model->update_member_data($member_id, $cdata);
				$this->EE->session->set_flashdata('success', lang('member_updated'));
	$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=clients'.AMP.'keyword='.$data['username']);
			}
			else{
				$member_id = $this->EE->member_model->create_member($data, $cdata);
				$query = $this->EE->db->get_where('members', array('member_id' => $member_id));
				$this->EE->session->set_flashdata('success', lang('member_created'));
	$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=clients'.AMP.'keyword='.$query->row('username'));	
			}
		}
		return $this->EE->load->view('create_client', $vars, TRUE);
	}
	
	function record(){
		$vars['theme_folder_url'] = $this->EE->config->slash_item('theme_folder_url');
		//get the type of record from the URL
		$type = $this->EE->input->get('type');
		$vars['type'] = $type;
		//determine if we are creating a new record or editing an existing based on whether or not the record id is in the URL
		if($record_id = $this->EE->input->get('record_id')){ 
			$vars['title'] = 'edit_'.$type;
			$vars['method'] = 'record'.AMP.'type='.$type.AMP.'record_id='.$record_id;
		}
		else{
			$vars['title'] = 'create_'.$type;
			$vars['method'] = 'record'.AMP.'type='.$type;
		}
		//check that the record type exists in the db
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
		
		
		//Grab the module settings
		$settings = $this->EE->invoicee->_get_settings();
		
		//Grab the correct currency symbol
		$vars['symbol'] = $settings[$type.'_settings'][$type.'_symbol'];
		
		//has the form been submitted?
		if($this->EE->input->post('submit')){
			//sanitize and insert/update
			$vars['record_name'] = $this->EE->input->post('invoice_no');
			$vars['date'] = $this->EE->input->post('date');
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
			
			$counter = 0;
			foreach($this->EE->input->post('record') as $row){
				$arr = array();
				$go_ahead = FALSE;
				foreach($row as $key => $val){
					if($val != ''){
						$go_ahead = TRUE;
					}
					$arr[$key] = $val;
				}
				
				if($go_ahead){
					//validate the data
					$config[] = array(
						'field'  => "record[$counter][record]", 
						'label'  => 'lang:record', 
						'rules'  => 'required|trim'
					);
					$config[] = array(
						'field'  => "record[$counter][record_units]", 
						'label'  => 'lang:units', 
						'rules'  => 'required|trim|numeric'
					);
					$config[] = array(
						'field'  => "record[$counter][record_rate]", 
						'label'  => 'lang:rate', 
						'rules'  => 'required|trim|numeric'
					);
					$vars['records'][$counter] = $arr;
				}
				$counter++;
			}
			
			$this->EE->form_validation->set_rules($config);
			//run the form validation
			if ($this->EE->form_validation->run() === FALSE){
				if(isset($this->EE->form_validation->_error_array) && !empty($this->EE->form_validation->_error_array)){
					foreach($this->EE->form_validation->_error_array as $k => $v){
						$vars['errors'][$k] = $v;
					}
				}
			}
			else{
				//if this is an update, delete all exisiting records on this invoice
				if(isset($record_id) && $record_id != ''){
					$this->EE->db->delete('invoicee_record_data', array('record_id' => $record_id));
				}
				else{
					$data = array(
					   'created_at' => strtotime($this->EE->input->post('date')),
					   'updated_at' => time(),
					   'has_paid' => 0,
					   'member_id' => $this->EE->input->post('member'),
					   'name' => $this->EE->input->post('invoice_no'),
					   'unique_id' => $this->EE->functions->random('encrypt', 5),
					   'type_id' => $type_id
					);
					//insert into the invoice table
					$this->EE->db->insert('invoicee_record', $data);
					$record_id = $this->EE->db->insert_id();
				}
				$data = array();
				foreach($this->EE->input->post('record') as $row){
					$data[] = array(
						'record_id' => $record_id,
						'title' => $row['record'],
						'description' => $row['record_description'],
						'units' => $row['record_units'],
						'rate' => $row['record_rate'],
						'tax' => $row['record_tax'],
						'total' => $row['record_units']*$row['record_rate']*(($row['record_tax']/100)+1),
						'created_at' => time(),
						'updated_at' => time(),
					);
				}
				$this->EE->db->insert_batch('invoicee_record_data', $data);
				$this->EE->session->set_flashdata('success', lang($type.'_updated'));
	$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=record'.AMP.'type='.$type.AMP.'record_id='.$record_id);
			}
			
		}
		else{
			//get the latest record based on the prefix in the settings and the type from the URL
			$this->EE->db->select('*');
			$this->EE->db->from('invoicee_record');
			$where = "name LIKE '" . $settings[$type.'_settings'][$type.'_prefix'] . "%'";
			$this->EE->db->where($where);
			$this->EE->db->order_by('name', 'DESC');
			$this->EE->db->limit('1');
			$query = $this->EE->db->get();

			//if there are no records, we create the first entry
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
			
			if($record_id){
				$query = $this->EE->db->get_where('invoicee_record', array('record_id' => $record_id));
				$vars['record_name'] = $query->row('name');
				$vars['date'] = date('Y-m-d', $query->row('created_at'));
				$query = $this->EE->db->get_where('invoicee_record_data', array('record_id' => $record_id));
				$counter = 0;
				if($query->num_rows() != 0){
					foreach($query->result_array() as $row){
						$vars['records'][$counter] = array(
											'record' => $row['title'], 
											'record_description' => $row['description'], 
											'record_units' => $row['units'], 
											'record_rate' => $row['rate'], 
											'record_tax' => $row['tax'], 
										);
						$counter++;
					}
				} 
			}
		}
		
		$this->EE->javascript->compile();
	
		//Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('create_'.$type));
		
		//Get a list of members for the dropdown
		//@TODO perhaps move this to a model?
		$this->EE->db->select('*');
		$this->EE->db->from('members');
		$this->EE->db->join('member_data', 'member_data.member_id = members.member_id');
		$this->EE->db->where('group_id', $settings['client_settings']['member_groups']);
		$query = $this->EE->db->get();
		
		foreach($query->result_array() as $row){
			$vars['members'][$row['member_id']] = $row['m_field_id_'.$settings['client_settings']['client_first_name']] . ' ' . $row['m_field_id_'.$settings['client_settings']['client_surname']] . ' - ' . $row['email'];
		}
		
		return $this->EE->load->view('record', $vars, TRUE);
	}	
	
	function view(){
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('view'));
		$record_id = $this->EE->input->get('record_id');
		$inv = $this->EE->db->get_where('invoicee_record', array('unique_id' => $record_id));
		if($inv->num_rows() > 0){
			$this->EE->load->model('template_model');
			//$this->EE->load->library('invoicee');
			$settings = $this->EE->invoicee->_get_settings('invoice');
			$query = $this->EE->template_model->get_template_info($settings['invoice_template']);
	
			if (!$row = $query->row())
			{
				die();
			}
			$template_data = $row->template_data;
			$template_data = str_replace('{record_id}', $record_id, $template_data);
			$text = $this->_parse($template_data);
			$this->EE->invoicee->pdf($text, $inv->row('name'));
		}
		else{
			return show_error(lang('invalid_invoice'));
		}
	}
	
	function _parse($text){
		require_once APPPATH.'libraries/Template'.EXT;

		$this->EE->TMPL = new EE_Template();
		$this->EE->TMPL->template_type = 'webpage';
		$this->EE->TMPL->parse($text, FALSE, $this->EE->config->item('site_id'));

		return $this->EE->TMPL->parse_globals($this->EE->TMPL->final_template);
	}
	
	function no_settings(){
		//Determine if any module settings have been stored
		$query = $this->EE->db->get('invoicee_settings');
		
		//If no result, we redirect to the settings page
		if($query->num_rows() == 0){
			$this->EE->session->set_flashdata('error', lang('no_settings'));
	$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=settings');
		}
	}
	
	function create_project(){
		$vars = array();
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('create_project'));
		return $this->EE->load->view('create_project', $vars, TRUE);
	}
	
	function delete(){
		$type = $this->EE->input->get('type');
		$result = $this->EE->db->get_where('invoicee_type', array('type_name' => $type));
		if($result->num_rows() == 0){
			return show_error(lang('no_matching_type'));
		}
		$record_id = $this->EE->input->get('record_id');
		$result = $this->EE->db->get_where('invoicee_record', array('record_id' => $record_id));
		if($result->num_rows() == 0){
			return show_error(lang('no_record'));
		}
		$this->EE->db->delete('invoicee_record', array('record_id' => $record_id));
		$this->EE->db->delete('invoicee_record_data', array('record_id' => $record_id));
		$this->EE->session->set_flashdata('success', lang($type.'_deleted'));
$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=invoicee'.AMP.'method=records'.AMP.'type='.$type.'s');
	}
	
	function send(){
		if(!$record_id = $this->EE->input->get_post('record_id'))
		{
			return show_error(lang('no_record_id'));
		}
		$type = $this->EE->input->get('type');
		$vars = array();
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('send_'.$type));
		//Set the variables for the view page
		$vars['title'] = lang('send_'.$type);
		$query = $this->EE->db->get_where('invoicee_record', array('record_id' => $record_id));
		$this->EE->db->join('members', 'members.member_id = member_data.member_id');
		$query = $this->EE->db->get_where('member_data', array('member_data.member_id' => $query->row('member_id')));
		
		foreach($this->EE->invoicee->_get_settings('client') as $key => $val){
			$vars[$key] = $query->row('m_field_id_'.$val);
		}
		$vars['email'] = $query->row('email');
		print_r($vars);
		//load the view
		return $this->EE->load->view('send', $vars, TRUE);
	}
	
			
}
// END CLASS

/* End of file mcp.invoicee.php */
/* Location: ./system/expressionengine/third_party/invoicee/mcp.invoicee.php */