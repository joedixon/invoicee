<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoicee {

	var $return_data	= '';
	
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->EE->load->library('invoicee');
	}
	function index()
	{	
		
	}
	
	function invoice(){
		$record_id = $this->EE->TMPL->fetch_param('record_id');
		$this->EE->db->from('invoicee_record');
		$this->EE->db->join('members', 'members.member_id = invoicee_record.member_id');
		$this->EE->db->join('member_data', 'member_data.member_id = members.member_id');
		$this->EE->db->join('invoicee_type', 'invoicee_type.type_id = invoicee_record.type_id');
		$this->EE->db->where('invoicee_record.unique_id', $record_id);
		$query = $this->EE->db->get();
		
		$client_settings = $this->EE->invoicee->_get_settings('client');
		$vars['client_name'] = $query->row('m_field_id_'.$client_settings['client_first_name']) . ' ' . $query->row('m_field_id_'.$client_settings['client_surname']);
		$vars['client_address_1'] = $query->row('m_field_id_'.$client_settings['client_address_one']);
		$vars['client_address_2'] = $query->row('m_field_id_'.$client_settings['client_address_two']);
		$vars['client_town'] = $query->row('m_field_id_'.$client_settings['client_town']);
		$vars['client_county'] = $query->row('m_field_id_'.$client_settings['client_county']);
		$vars['client_postcode'] = $query->row('m_field_id_'.$client_settings['client_postcode']);
		$vars['invoice_date'] = $query->row('created_at');
		$vars['invoice_number'] = $query->row('name');
		if($query->row('has_paid')){
			$vars['paid'] = 'true';
		}
		else{
			$vars['paid'] = '';
		}
		$type = $query->row('type_name');
		$type_settings = $this->EE->invoicee->_get_settings($type);
		$this->EE->db->from('invoicee_record_data');
		$this->EE->db->join('invoicee_record', 'invoicee_record.record_id = invoicee_record_data.record_id');
		$this->EE->db->where('invoicee_record.unique_id', $record_id);
		$query = $this->EE->db->get();
		$subtotal = 0;
		$total = 0;
		$tax = 0;
		if($query->num_rows() > 0){
			$counter = 0;
			foreach($query->result_array() as $row){
				$vars['entries'][$counter]['title'] = $row['title'];
				$vars['entries'][$counter]['description'] = $row['description'];
				$vars['entries'][$counter]['units'] = $row['units'];
				$vars['entries'][$counter]['rate'] = htmlentities($type_settings[$type.'_symbol'], ENT_IGNORE, "UTF-8").number_format($row['rate'], 2, '.', ',');
				$vars['entries'][$counter]['tax'] = number_format($row['tax'], 2, '.', ',').htmlentities('%');
				$vars['entries'][$counter]['total'] = htmlentities($type_settings[$type.'_symbol'], ENT_IGNORE, "UTF-8").number_format($row['total'], 2, '.', ',');
				$subtotal += $row['units']*$row['rate'];
				$total += $row['total'];
				$tax += $row['units']*$row['rate']*($row['tax']/100);
				$counter++;
			}
		}
		else{
			$vars['entries'] = array();
		}
		$vars['subtotal'] = htmlentities($type_settings[$type.'_symbol'], ENT_IGNORE, "UTF-8").number_format($subtotal, 2, '.', ',');
		$vars['total'] = htmlentities($type_settings[$type.'_symbol'], ENT_IGNORE, "UTF-8").number_format($total, 2, '.', ',');
		$vars['tax'] = htmlentities($type_settings[$type.'_symbol'], ENT_IGNORE, "UTF-8").number_format($tax, 2, '.', ',');
		$output = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
		return $output;
	}
	
	function generate_invoice(){
		$record_id = $this->EE->input->get('record_id');
		$this->EE->load->model('template_model');
		$this->EE->load->library('invoicee');
		$settings = $this->EE->invoicee->_get_settings('invoice');
		$query = $this->EE->template_model->get_template_info($settings['invoice_template']);
		if (!$row = $query->row())
		{
			die();
		}
		$template_data = $row->template_data;
		$template_data = str_replace('{record_id}', $record_id, $template_data);
		$text = $this->_parse($template_data);
		$this->EE->invoicee->pdf($text, 'Test2.pdf');
	}
	
	/*protected function _get_settings($type=''){
		$settings = array();
		
		//Get all the module settings data
		$query = $this->EE->db->get('invoicee_settings');
		
		if($query->num_rows() > 0){
			$settings['client_settings'] = json_decode($query->row('client_settings'), true);
			$settings['email_settings'] = json_decode($query->row('email_settings'), true);
			$settings['invoice_settings'] = json_decode($query->row('invoice_settings'), true);
			$settings['quote_settings'] = json_decode($query->row('quote_settings'), true);
		}
		else{
			$settings['client_settings']['member_groups'] = '';
			$settings['client_settings']['client_first_name'] = '';
			$settings['client_settings']['client_surname'] = '';
			$settings['client_settings']['client_address_one'] = '';
			$settings['client_settings']['client_address_two'] = '';
			$settings['client_settings']['client_town'] = '';
			$settings['client_settings']['client_county'] = '';
			$settings['client_settings']['client_postcode'] = '';
			$settings['client_settings']['client_telephone'] = '';
			$settings['client_settings']['client_vat_number'] = '';
			$settings['email_settings']['email_send_admin'] = '';
			$settings['email_settings']['email_from_address'] = '';
			$settings['email_settings']['email_from_name'] = '';
			$settings['email_settings']['email_subject'] = '';
			$settings['invoice_settings']['invoice_prefix'] = '';
			$settings['invoice_settings']['invoice_length'] = '';
			$settings['invoice_settings']['invoice_template'] = '';
			$settings['invoice_settings']['invoice_symbol'] = '';
		}
		if($type == ''){
			return $settings;
		}
		elseif($type == 'client'){
			return $settings['client_settings'];
		}
		elseif($type == 'email'){
			return $settings['email_settings'];
		}
		elseif($type == 'invoice'){
			return $settings['invoice_settings'];
		}
	}*/
	
	function _parse($text){
		require_once APPPATH.'libraries/Template'.EXT;

		$this->EE->TMPL = new EE_Template();
		$this->EE->TMPL->template_type = 'webpage';
		$this->EE->TMPL->parse($text, FALSE, $this->EE->config->item('site_id'));

		return $this->EE->TMPL->parse_globals($this->EE->TMPL->final_template);
	}	
}

// END CLASS

/* End of file mod.invoicee.php */
/* Location: ./system/expressionengine/third_party/invoicee/mod.invoicee.php */