<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Invoicee_upd { 

    var $version = '1.0'; 
     
    function __construct() 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
    }

	function install() 
	{
		$this->EE->load->dbforge();

		$data = array(
			'module_name' => 'Invoicee' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
		
		$this->EE->db->insert('modules', $data);
		
		//Create project table
		$fields = array(
		    'project_id'   => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'client_id' => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE),
			'project_name'    => array('type' => 'varchar', 'constraint'  => '250'),
			'project_desc' => array('type' => 'text')
		    );

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('project_id', TRUE);

		$this->EE->dbforge->create_table('invoicee_project');
		
		unset($fields);
		//End of project table
		
		//Create record table
		$fields = array(
		    'record_id'   => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'type_id' => array('type' => 'int', 'constraint' => '10'),
			'unique_id'    => array('type' => 'varchar', 'constraint'  => '50'),
			'name'    => array('type' => 'varchar', 'constraint'  => '250'),
			'project_id'	=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => TRUE),
			'member_id' => array('type' => 'int', 'constraint' => '10'),
		    'has_paid'    => array('type' => 'tinyint', 'constraint'  => '1'),
		    'created_at' => array('type' => 'int', 'constraint' => '10'),
		    'updated_at'    => array('type' => 'int', 'constraint' => '10'),
		    );
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('record_id', TRUE);
		$this->EE->dbforge->create_table('invoicee_record');
		
		unset($fields);
		//End of record table
		
		//Create type table
		$fields = array(
		    'type_id'   => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'type_name'    => array('type' => 'varchar', 'constraint'  => '50')
		);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('type_id', TRUE);
		$this->EE->dbforge->create_table('invoicee_type');
		
		unset($fields);
		//End of type table
		
		//Insert into the type table
		$data = array(
			array(
		   		'type_name' => 'invoice'
			),
			array(
				'type_name' => 'quote'
			)
		);
		$this->EE->db->insert_batch('invoicee_type', $data);
		//End of data entry
		
		//Create record_data table
		$fields = array(
		    'record_data_id'   => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'record_id' => array('type' => 'int', 'constraint' => '10'),
		    'title'    => array('type' => 'varchar', 'constraint'  => '250'),
		    'description' => array('type' => 'text'),
		    'units'    => array('type' => 'decimal', 'constraint' => '10,2'),
			'rate'    => array('type' => 'decimal', 'constraint' => '10, 2'),
			'tax'    => array('type' => 'decimal', 'constraint' => '5, 2'),
			'total'    => array('type' => 'decimal', 'constraint' => '10, 2'),
			'created_at' => array('type' => 'int', 'constraint' => '10'),
		    'updated_at'    => array('type' => 'int', 'constraint' => '10'),
		);
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('record_data_id', TRUE);
		$this->EE->dbforge->create_table('invoicee_record_data');
		
		unset($fields);
		//End of data table
		
		//Create type table
		$fields = array(
		    'data_id'   => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'data_name'    => array('type' => 'varchar', 'constraint'  => '50')
		);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('data_id', TRUE);
		$this->EE->dbforge->create_table('invoicee_data');
		
		unset($fields);
		//End of type table
		
		//Insert into the data table
		$data = array(
			array(
		   		'data_name' => 'task'
			),
			array(
				'data_name' => 'item'
			),
			array(
				'data_name' => 'expense'
			)
		);
		$this->EE->db->insert_batch('invoicee_data', $data);
		//End of data entry
		
		//Create comment table
		$fields = array(
		    'comment_id'   => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'invoice_id' => array('type' => 'int', 'constraint' => '10'),
		    'comment' => array('type' => 'text'),
			'created_at' => array('type' => 'int', 'constraint' => '10'),
		    );

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('comment_id', TRUE);

		$this->EE->dbforge->create_table('invoicee_comment');
		
		unset($fields);
		//End of comment table
		
		//Create settings table
		$fields = array(
		    'client_settings'   => array('type' => 'varchar', 'constraint' => '250'),
			'email_settings' => array('type' => 'varchar', 'constraint' => '250'),
			'invoice_settings' => array('type' => 'varchar', 'constraint' => '250'),
			'quote_settings' => array('type' => 'varchar', 'constraint' => '250'),
		    );

		$this->EE->dbforge->add_field($fields);

		$this->EE->dbforge->create_table('invoicee_settings');
		
		unset($fields);
		//End of comment table
		
		//Add a generate invoice action
		$data = array(
		    'class'     => 'Invoicee' ,
		    'method'    => 'generate_invoice'
		);

		$this->EE->db->insert('actions', $data);
				
		return TRUE;
	}
	
	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Invoicee'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Invoicee');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Invoicee');
		$this->EE->db->delete('actions');
		
		$this->EE->dbforge->drop_table('invoicee_project');
		$this->EE->dbforge->drop_table('invoicee_record');
		$this->EE->dbforge->drop_table('invoicee_record_data');
		$this->EE->dbforge->drop_table('invoicee_data');
		$this->EE->dbforge->drop_table('invoicee_type');
		$this->EE->dbforge->drop_table('invoicee_comment');
		$this->EE->dbforge->drop_table('invoicee_settings');	
		
		return TRUE;
	}
	
	function update($current = '')
	{
		return FALSE;
	}
}

/* End of file upd.invoicee.php */
/* Location: ./system/expressionengine/third_party/invoicee/upd.invoicee.php */