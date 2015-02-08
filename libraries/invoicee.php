<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoicee
{
	public function __construct()
	{
		$this->EE =& get_instance();

		// disable dompdf log file
		defined('DOMPDF_LOG_OUTPUT_FILE') OR define('DOMPDF_LOG_OUTPUT_FILE', FALSE);

		require_once(PATH_THIRD.'invoicee/third_party/dompdf/dompdf_config.inc.php');
	}

	public function pdf($text, $name)
	{
		$dompdf = new DOMPDF();
		$dompdf->set_paper('a4', 'portrait');
		$dompdf->load_html($text);
		$dompdf->render();
		$dompdf->stream($name, array("Attachment" => 0));
	}
	
	public function _get_settings($type=''){
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
			$settings['quote_settings']['quote_prefix'] = '';
			$settings['quote_settings']['quote_length'] = '';
			$settings['quote_settings']['quote_template'] = '';
			$settings['quote_settings']['quote_symbol'] = '';
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
		elseif($type == 'quote'){
			return $settings['quote_settings'];
		}
	}
}

/* End of file ./libraries/invoicee_pdf.php */