<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once FCPATH.'sparks/php-activerecord/0.0.2/vendor/php-activerecord/ActiveRecord.php'; 

class Sangar_scaffolds
{
	public $dbdriver;

	public $controller_name;
	public $model_name;
	public $model_name_for_calls;
	public $scaffold_code;

	public $arrayjson;
	public $errors;

	public $actual_language;
	public $languages;

	public $scaffold_delete_bd;
	public $scaffold_bd;
	public $scaffold_routes;
	public $scaffold_menu;

	public $create_controller;
	public $create_model;	
	public $create_view_create;
	public $create_view_list;

	public $tab;
	public $tabx2;
	public $tabx3;
	public $tabx4;
	public $tabx5;
	public $tabx6;
	public $tabx7;
	public $sl;


	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->database();
		$this->dbdriver = $this->ci->db->dbdriver;

		$this->actual_language 	= $this->ci->config->item('prefix_language');
		$this->languages 		= $this->ci->config->item('languages');

		$this->errors 	= FALSE;

		$this->tab 		= chr(9);
		$this->tabx2 	= chr(9).chr(9);
		$this->tabx3 	= chr(9).chr(9).chr(9);
		$this->tabx4 	= chr(9).chr(9).chr(9).chr(9);
		$this->tabx5 	= chr(9).chr(9).chr(9).chr(9).chr(9);
		$this->tabx6 	= chr(9).chr(9).chr(9).chr(9).chr(9).chr(9);
		$this->tabx7 	= chr(9).chr(9).chr(9).chr(9).chr(9).chr(9).chr(9);
		$this->sl  		= chr(13).chr(10);
	}


	public function create($data)
	{
		//Extraemos las variables
		$this->init($data);

		//Preparamos el JSON a partir de los datos enviados
		$result = $this->prepare_json();

		if ($result === FALSE)
		{
			return $this->errors;
		}


		//borramos la tabla en la base de datos
		if ($this->scaffold_delete_bd)
		{
			$result = $this->delete_table_bd();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}


		//creamos la tabla en la base de datos
		if ($this->scaffold_bd)
		{
			$result = $this->create_table_db();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}


		//creamos el controlador
		if ($this->create_controller)
		{
			$result = $this->create_controller();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}


		//creamos el modelo
		if ($this->create_model)
		{	
			$result = $this->create_model();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}


		//creamos la vista de create
		if ($this->create_view_create)
		{		
			$result = $this->create_view_create();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}


		//creamos la vista de lista
		if ($this->create_view_list)
		{
			$result = $this->create_view_list();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}


		//modify routes.php
		if ($this->scaffold_routes)
		{
			$result = $this->modify_routes();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}


		if ($this->scaffold_menu)
		{
			$result = $this->modify_menu();

			if ($result === FALSE)
			{
				return $this->errors;
			}
		}

		return TRUE;
	}


	private function init($data)
	{
		$this->controller_name			=	$data['controller_name'];
		$this->model_name				=	$data['model_name'];
		$this->model_name_for_calls		=	ucfirst($data['model_name']);
		$this->scaffold_code			=	$data['scaffold_code'];
		$this->scaffold_delete_bd		=	$data['scaffold_delete_bd'];
		$this->scaffold_bd 				=	$data['scaffold_bd'];
		$this->scaffold_routes 			=	$data['scaffold_routes'];
		$this->scaffold_menu 			=	$data['scaffold_menu'];
		$this->create_controller		=	$data['create_controller'];;
		$this->create_model				=	$data['create_model'];;	
		$this->create_view_create		=	$data['create_view_create'];;
		$this->create_view_list			=	$data['create_view_list'];;
	}


	private function prepare_json()
	{
		$arrayjson = json_decode("{".$this->scaffold_code."}", TRUE);

		//evitamos que se puedan crear los nombres de los campos con mayúsculas
		foreach ($arrayjson as $index => $value)
		{
			if (strtolower($index) !== $index)
			{
				$arrayjson[strtolower($index)] = $arrayjson[$index];
				unset($arrayjson[$index]);
			}
		}


		if ($arrayjson)
		{
			$this->arrayjson	=	$arrayjson;
		}
		else
		{
			$this->errors = 'Error creando el JSON';
			return FALSE;
		}

	}

	private function delete_table_bd()
	{
		$sql = "DROP TABLE IF EXISTS ".$this->controller_name.";";
		$conn = ActiveRecord\ConnectionManager::get_connection("development");
		$result = (object)$conn->query($sql);
		//$result = TRUE;

		if ($result)
			return TRUE;
		else
		{
			$this->errors = "Error creando la tabla en la base de datos:<br>$sql_table";
			return FALSE;
		}
	}


	private function create_table_db()
	{
		switch($this->dbdriver)
		{
			case 'mysql':

	  			$sql_table = "CREATE TABLE ".$this->controller_name." ("; 
	  			$sql_table .= "id INT(9) NOT NULL AUTO_INCREMENT PRIMARY KEY ,";

	  			
	  			foreach ($this->arrayjson as $index => $value )
		    	{
		    		$sql_table_aux = "";

		      		switch ($value['type'])
		      		{
		        		case 'text':

		        			if ( $value['multilanguage'] == "TRUE")
		        			{
		        				foreach ($this->languages as $prefix=>$language)
		        				{
									$sql_table_aux .= $index."_".$prefix ." varchar(256) DEFAULT '' "; 

						            if ($value['required'])
						             $sql_table_aux .= "NOT NULL, ";
						            else 
						              $sql_table_aux .= ", ";
		        				}

		        				$sql_table .= $sql_table_aux;
		        			}
		        			else
		        			{
					        	$sql_table .= $index."  varchar(256)  DEFAULT '' ";
					        			            
								if ($value['required'])
									$sql_table .= "NOT NULL, ";
								else 
									$sql_table .= ", ";
		        			}

		        		break;

		        		case 'textarea':

		        			if ( $value['multilanguage'] == "TRUE")
		        			{
		        				foreach ($this->languages as $prefix=>$language)
		        				{
									$sql_table_aux .= $index."_".$prefix ." text DEFAULT '' "; 

						            if ($value['required'])
						             $sql_table_aux .= "NOT NULL, ";
						            else 
						              $sql_table_aux .= ", ";
		        				}

		        				$sql_table .= $sql_table_aux;
		        			}
		        			else
		        			{
					        	$sql_table .= $index."  text  DEFAULT '' ";
					        			            
								if ($value['required'])
									$sql_table .= "NOT NULL, ";
								else 
									$sql_table .= ", ";
		        			}

		        		break;

		        		case 'checkbox':

		        			$sql_table .= $index." INT(1) ";

					        if ($value['required'])
					         $sql_table .= "NOT NULL, ";
					        else 
					          $sql_table .= ", ";

		        		break;

		        		case 'select':
		        		case 'radio':

				        	$sql_table .= $index."  varchar(32)  DEFAULT '' ";
				        			            
							if ($value['required'])
								$sql_table .= "NOT NULL, ";
							else 
								$sql_table .= ", ";
		        		break;

		        		case 'selectbd':

				        	$sql_table .= $index."  INT(9) ";
				        			            
							if ($value['required'])
								$sql_table .= "NOT NULL, ";
							else 
								$sql_table .= ", ";
		        		break;

		        		case 'datepicker':

		        			$sql_table .= $index."  date, ";

		        		break;

		        	}
		        }

				$sql_table .= "created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
				$sql_table .= "updated_at TIMESTAMP NOT NULL";
				$sql_table .= ") ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

				break;
		}

		$conn = ActiveRecord\ConnectionManager::get_connection("development");
		$result = (object)$conn->query($sql_table);
		//$result = TRUE;

		if ($result)
			return TRUE;
		else
		{
			$this->errors = "Error creando la tabla en la base de datos:<br>$sql_table";
			return FALSE;
		}
	}


	private function create_controller()
	{
		$data = "";

		$data .= "

<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ".ucfirst($this->controller_name)." extends MY_Controller
{
	protected \$before_filter = array(
		'action' => 'is_logged_in'
		//'except' => array(),
		//'only' => array()
	);

	function __construct()
	{
		parent::__construct();
	}


	public function index()
	{	
		//set the title of the page 
		\$layout['title'] = 'Listado de ".$this->controller_name."';

		//set the pagination configuration array and initialize the pagination
		\$config = \$this->set_paginate_options('index');

		//Initialize the pagination class
		\$this->pagination->initialize(\$config);

		//control of number page
		\$page = (\$this->uri->segment(2)) ? \$this->uri->segment(2) : 1;

		//find all the categories with paginate and save it in array to past to the view
		\$data['".$this->controller_name."'] = ".$this->model_name_for_calls."::paginate_all(\$config['per_page'], \$page);

		//create paginate´s links
		\$data['links'] = \$this->pagination->create_links();

		//control variables
		\$data['page'] = \$page;

		//Guardamos en la variable \$layout['body'] la vista renderizada users/list. Le pasamos tb la lista de todos los usuarios
		\$layout['body'] = \$this->load->view('".$this->controller_name."/list', \$data, TRUE);

		//Cargamos el layout y le pasamos el contenido que esta en la variable \$layout
		\$this->load->view('layouts/backend', \$layout);
	}


	function create(\$page = NULL) 
	{
		//create control variables
		\$data['title']		= 	'Crear ".$this->controller_name."';
		\$data['updType']	= 	'create';
		\$data['".$this->model_name."']		= 	getTableColumns('".$this->controller_name."', true);
		\$data['page']		=	( \$this->uri->segment(3) )  ? \$this->uri->segment(3) : \$this->input->post('page', TRUE);

		";

	  	foreach ($this->arrayjson as $index => $value )
    	{
      		switch ($value['type'])
      		{
        		case 'selectbd':
        			$data .= "\$data['array_".strtolower($value['options']['model'])."']	= 	".$value['options']['model']."::find('all', array('order' => '".$value['options']['order']."' ));";
        		break;
        	}
        }
		
        $data .= "

		//Rules for validation
		\$this->set_rules();

		//validate the fields of form
		if (\$this->form_validation->run() == FALSE) 
		{
			//load the view and the layout
			\$layout['body'] = \$this->load->view('".$this->controller_name."/create', \$data, TRUE);
			\$this->load->view('layouts/backend', \$layout);	
		}
		else
		{
			//Validation OK!

			// build array for the model
			\$form_data = array(";

				foreach ($this->arrayjson as $index => $value )
		    	{
		      		switch ($value['type'])
		      		{
		        		case 'text':
		        		case 'textarea':

		        			if ( $value['multilanguage'] == "TRUE")
		        			{
		        				foreach ($this->languages as $prefix=>$language)
		        				{
		        					$data .= $this->sl.$this->tabx4."'".$index."_".$prefix."' => set_value('".$index."_".$prefix."'), ";
		        				}

		        			}
		        			else
		        			{
		        				$data .= $this->sl.$this->tabx4."'".$index."' => set_value('".$index."'), ";
		        			}

		        		break;

		        		case 'checkbox':
		        		case 'select':
		        		case 'selectbd':
		        		case 'radio':
		        		case 'datepicker':


		        			$data .= $this->sl.$this->tabx4."'".$index."' => set_value('".$index."'), ";

		        		break;
		        	}
		        }

		        $data = substr( $data, 0, -2 );

			$data .=$this->sl.$this->tabx3.");

			// run insert model to write data to db
			\$".$this->model_name." = ".$this->model_name_for_calls."::create(\$form_data);

			if ( \$".$this->model_name."->is_valid() ) // the information has therefore been successfully saved in the db
			{
				\$this->session->set_flashdata('message', array( 'type' => 'success', 'text' => lang('web_create_success') ));
			}
			
			if ( $".$this->model_name."->is_invalid() )
			{
				\$this->session->set_flashdata('message', array( 'type' => 'error', 'text' => \$".$this->model_name."->errors->full_messages() ));
			}

			redirect('".$this->controller_name."/');
		
	  	} 
	}


	function edit(\$id = FALSE, \$page = 1) 
	{
		//get the \$id and sanitize
		\$id = ( \$this->uri->segment(3) )  ? \$this->uri->segment(3) : \$this->input->post('id', TRUE);
		\$id = ( \$id != 0 ) ? filter_var(\$id, FILTER_VALIDATE_INT) : NULL;

		//get the \$page and sanitize
		\$page = ( \$this->uri->segment(4) )  ? \$this->uri->segment(4) : \$this->input->post('page', TRUE);
		\$page = ( \$page != 0 ) ? filter_var(\$page, FILTER_VALIDATE_INT) : NULL;

		//redirect if it´s no correct
		if (!\$id){
			\$this->session->set_flashdata('message', array( 'type' => 'warning', 'text' => lang('web_object_not_exit') ) );
			redirect('".$this->controller_name."/');
		}

		";

	  	foreach ($this->arrayjson as $index => $value )
    	{
      		switch ($value['type'])
      		{
        		case 'selectbd':
        			$data .= "\$data['array_".strtolower($value['options']['model'])."']	= 	".$value['options']['model']."::find('all', array('order' => '".$value['options']['order']."' ));";
        		break;
        	}
        }
		
        $data .= "

		//Rules for validation
		\$this->set_rules(\$id);

		//create control variables
		\$data['title'] = lang('web_edit');
		\$data['updType'] = 'edit';
		\$data['page'] = \$page;


		if (\$this->form_validation->run() == FALSE) // validation hasn't been passed
		{

			//search the item to show in edit form
			\$data['".$this->model_name."'] = ".$this->model_name_for_calls."::find_by_id(\$id);
			
			//load the view and the layout
			\$layout['body'] = \$this->load->view('".$this->controller_name."/create', \$data, TRUE);
			\$this->load->view('layouts/backend', \$layout);
		}
		else
		{
			// build array for the model
			\$form_data = array(
					       	'id'	=> \$this->input->post('id', TRUE),";

							foreach ($this->arrayjson as $index => $value )
					    	{
					      		switch ($value['type'])
					      		{
					        		case 'text':
					        		case 'textarea':

					        			if ( $value['multilanguage'] == "TRUE")
					        			{
					        				foreach ($this->languages as $prefix=>$language)
					        				{
					        					$data .= $this->sl.$this->tabx7."'".$index."_".$prefix."' => set_value('".$index."_".$prefix."'), ";
					        				}

					        			}
					        			else
					        			{
					        				$data .= $this->sl.$this->tabx7."'".$index."' => set_value('".$index."'), ";
					        			}

					        		break;


					        		case 'checkbox':
					        		case 'select':
					        		case 'selectbd':
					        		case 'radio':
					        		case 'datepicker':

					        			$data .= $this->sl.$this->tabx7."'".$index."' => set_value('".$index."'), ";

					        		break;
					        	}
					        }

					        $data = substr( $data, 0, -2 );

					        $data .= "
						);
		
			//find the item to update
			\$".$this->model_name." = ".$this->model_name_for_calls."::find(\$this->input->post('id', TRUE));
			\$".$this->model_name."->update_attributes(\$form_data);

			// run insert model to write data to db
			if ( \$".$this->model_name."->is_valid()) // the information has therefore been successfully saved in the db
			{
				\$this->session->set_flashdata('message', array( 'type' => 'success', 'text' => lang('web_edit_success') ));
				redirect(\"".$this->controller_name."/\$page/\");
			}

			if (\$".$this->model_name."->is_invalid())
			{
				\$this->session->set_flashdata('message', array( 'type' => 'error', 'text' => \$".$this->model_name."->errors->full_messages() ) );
				redirect(\"".$this->controller_name."/\$page/\");
			}	
	  	} 
	}


	function delete(\$id = NULL, \$page = 1)
	{
		//filter & Sanitize \$id
		\$id = (\$id != 0) ? filter_var(\$id, FILTER_VALIDATE_INT) : NULL;

		//redirect if it´s no correct
		if (!\$id){
			\$this->session->set_flashdata('message', array( 'type' => 'warning', 'text' => lang('web_object_not_exit') ) );
			
			redirect('".$this->controller_name."');
		}
		
		//search the item to delete
		if ( ".$this->model_name_for_calls."::exists(\$id) )
		{
			\$".$this->model_name." = ".$this->model_name_for_calls."::find(\$id);
		}
		else
		{
			\$this->session->set_flashdata('message', array( 'type' => 'warning', 'text' => lang('web_object_not_exit') ) );
			
			redirect('".$this->controller_name."');		
		}

		//delete the item
		if ( \$".$this->model_name."->delete() == TRUE) 
		{
			\$this->session->set_flashdata('message', array( 'type' => 'success', 'text' => lang('web_delete_success') ));	
		}
		else
		{
			\$this->session->set_flashdata('message', array( 'type' => 'error', 'text' => lang('web_delete_failed') ) );
		}	

		redirect('".$this->controller_name."');
	}


	private function set_rules(\$id = NULL)
	{
		//Creamos los parametros de la funcion del constructor.
		// More validations: http://codeigniter.com/user_guide/libraries/form_validation.html";
    	foreach ($this->arrayjson as $index => $value )
    	{
      		switch ($value['type'])
      		{
        		case 'text':
   
        			if ( $value['multilanguage'] == "TRUE")
        			{

        				foreach ($this->languages as $prefix=>$language)
        				{

	        				if ($value['is_unique'] == "TRUE")
	        				{
        						$aux 		= "|is_unique[".$this->controller_name.".".$index."_".$prefix."]";
        						$auxwithid 	= "|is_unique[".$this->controller_name.".".$index."_".$prefix.".id.\$id]";

	$data .= "
		if (\$id)
		{
			\$this->form_validation->set_rules('".$index."_".$prefix."', '".ucfirst($index)." ($prefix)', \"".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]$auxwithid\");
		}
		else
		{
			\$this->form_validation->set_rules('".$index."_".$prefix."', '".ucfirst($index)." ($prefix)', \"".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]$aux\");
		}
	";

	        				}
	        				else
	        				{
	        					$data .= $this->tabx2."\$this->form_validation->set_rules('".$index."_".$prefix."', '".ucfirst($index)." ($prefix)', '".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]');".$this->sl;
	        				}
        				}
        			}
        			else
        			{
        				if ($value['is_unique'] == "TRUE")
        				{
        					$aux 		= "|is_unique[".$this->controller_name.".".$index."]";
        					$auxwithid 	= "|is_unique[".$this->controller_name.".".$index.".id.\$id]";

	$data .= "
		if (\$id)
		{
			\$this->form_validation->set_rules('$index', '$index', \"".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]$auxwithid\");
		}
		else
		{
			\$this->form_validation->set_rules('$index', '$index', \"".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]$aux\");
		}

";  
        				}
        				else
        				{
        					$data .= $this->sl.$this->tabx2."\$this->form_validation->set_rules('$index', '$index', '".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]');".$this->sl;
        				}        				
        			}

        		break;

        		case 'textarea':

        			if ( $value['multilanguage'] == "TRUE")
        			{
        				foreach ($this->languages as $prefix=>$language)
        				{
							$data .= $this->tabx2."\$this->form_validation->set_rules('".$index."_".$prefix."', '".ucfirst($index)." ($prefix)', '".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]');".$this->sl;
        				}
        			}
        			else
        			{
        				$data .= $this->tabx2."\$this->form_validation->set_rules('$index', '$index', '".(($value['required'] == 'TRUE') ? 'required|' : '')."trim|xss_clean|min_length[".$value['minlength']."]|max_length[".$value['maxlength']."]');".$this->sl;
        				$data .= $this->tabx5;
        			}

        		break;

        		case 'checkbox':
        		case 'select':
        		case 'selectbd':
        		case 'radio':
        		case 'datepicker':

        	    	$data .= $this->tabx2."\$this->form_validation->set_rules('$index', '$index', '".(($value['required'] == 'TRUE') ? 'required|' : '')."xss_clean');".$this->sl;
        			$data .= $this->tabx5;	

        		break;
        	}
        }

        $data .= $this->tabx2."\$this->form_validation->set_error_delimiters(\"<br /><span class='error'>\", '</span>');";
		$data .= $this->sl.$this->tab."}		

	
	private function set_paginate_options()
	{
		\$config = array();

		\$config['base_url'] = site_url() . '".$this->controller_name."';

	    \$config['total_rows'] = ".ucfirst($this->model_name)."::count();

		\$config['use_page_numbers'] = TRUE;

	    \$config['per_page'] = 10;

	    \$config['uri_segment'] = 2;

	    \$config['first_link'] = \"<< \".lang('web_first');
	    \$config['first_tag_open'] = \"<span class='pag'>\";
		\$config['first_tag_close'] = '</span>';

		\$config['last_link'] = lang('web_last') .\" >>\";
		\$config['last_tag_open'] = \"<span class='pag'>\";
		\$config['last_tag_close'] = '</span>';

		\$config['next_link'] = FALSE;
		\$config['next_tag_open'] = \"<span class='pag'>\";
		\$config['next_tag_close'] = '</span>';

		\$config['prev_link'] = FALSE;
		\$config['prev_tag_open'] = \"<span class='pag'>\";
		\$config['prev_tag_close'] = '</span>';

	    \$config['cur_tag_open'] = \"<span class='pag pag_active'>\";
	    \$config['cur_tag_close'] = '</span>';

	    \$config['num_tag_open'] = \"<span class='pag'>\";
	    \$config['num_tag_close'] = '</span>';

	    \$config['full_tag_open'] = \"<div class='navigation'>\";
	    \$config['full_tag_close'] = '</div>';

	    \$choice = \$config[\"total_rows\"] / \$config[\"per_page\"];
	    //\$config[\"num_links\"] = round(\$choice);

	    return \$config;
	}

}";


		if ( $this->save_file($this->controller_name, "controllers/", trim( $data ) ) === TRUE )
			return TRUE;
		else
			return FALSE;
	}


	private function create_model()
	{
		$data = "
<?php
class ".$this->model_name_for_calls." extends ActiveRecord\Model {

	
	static \$validates_presence_of = array(";

		$there_are_requireds = FALSE;

		foreach ($this->arrayjson as $index => $value )
    	{
      		switch ($value['type'])
      		{
        		case 'text':
        		case 'textarea':

        			if ($value['required'] == "TRUE")
        			{
	        			if ( $value['multilanguage'] == "TRUE")
	        			{
	        				foreach ($this->languages as $prefix=>$language)
	        				{
	        					$data .= $this->sl.$this->tabx2."array('".$index."_".$prefix."'), ";
	        				}
	        			}
	        			else
	        			{
	        				$data .= $this->sl.$this->tabx2."array('".$index."'), ";
	        			}

	        			$there_are_requireds = TRUE;
        			}

        		break;

        		case 'checkbox':
        		case 'select':
        		case 'selectbd':
        		case 'radio':
        		case 'datepicker':

        			if ($value['required'] == "TRUE")
        			{
	        			$data .= $this->sl.$this->tabx2."array('".$index."'), ";	        			
	        			$there_are_requireds = TRUE;
        			}        		

        		break;
        	}
        }

        if ($there_are_requireds)
        	$data = substr( $data, 0, -2 );

        $data .="	
    );


	static function paginate_all(\$limit, \$page)
	{
		\$offset = \$limit * ( \$page - 1) ;

		\$result = ".$this->model_name_for_calls."::find('all', array('limit' => \$limit, 'offset' => \$offset, 'order' => 'id DESC' ) );

		if (\$result)
		{
			return \$result;
		}
		else
		{
			return FALSE;
		}
	}


}
		";


		if ( $this->save_file($this->model_name, "models/", trim( $data ) ) === TRUE )
			return TRUE;
		else
			return FALSE;
	}


	private function create_view_create()
	{

		$data = "";
		
		foreach ($this->arrayjson as $index => $value )
    	{
      		switch ($value['type'])
      		{
        		case 'datepicker':

$data .= "
<script src=\"js/datepicker/jquery.ui.datepicker-<?=\$this->config->item('prefix_language')?>.js\" type=\"text/javascript\"></script>
<script>
	$(function() {
		$.datepicker.setDefaults($.datepicker.regional['<?=\$this->config->item('prefix_language')?>']);
		$('.datepicker').datepicker({dateFormat: 'dd-mm-yy'});
	});
</script>
";
        		break;
        	}
        }

		$data .= "
<div id='content-top'>
    <h2><?=lang((\$updType == 'create') ? \"web_add\" : \"web_edit\")?></h2>
    <a href='/".$this->controller_name."/<?=\$page?>' class='bforward'><?=lang('web_back_to_list')?></a>
    <span class='clearFix'>&nbsp;</span>
</div>

<?php 
\$attributes = array('class' => 'tform', 'id' => '');
echo (\$updType == 'create') ? form_open_multipart('".$this->controller_name."/create', \$attributes) : form_open_multipart('".$this->controller_name."/edit', \$attributes); 
?>
";

foreach ($this->arrayjson as $index => $value )
{
	switch ($value['type'])
	{
		case 'text':
		
			if ( $value['multilanguage'] == "TRUE")
			{
				foreach ($this->languages as $prefix=>$language)
				{
$data .="
<p>
	<label class='labelform' for='".$index."_".$prefix."'>".ucfirst($index)." ($prefix) ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>
	<input id='".$index."_".$prefix."' type='text' name='".$index."_".$prefix."' maxlength='".$value['maxlength']."' value=\"<?php echo set_value('".$index."_".$prefix."', (isset(\$".$this->model_name."->".$index."_".$prefix.")) ? \$".$this->model_name."->".$index."_".$prefix." : ''); ?>\"  />
	<?php echo form_error('".$index."_".$prefix."'); ?>
</p>
";
				}
			}
			else
			{
$data .="
<p>
	<label class='labelform' for='".$index."'>".ucfirst($index)." ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>
	<input id='".$index."' type='text' name='".$index."' maxlength='".$value['maxlength']."' value=\"<?php echo set_value('".$index."', (isset(\$".$this->model_name."->".$index.")) ? \$".$this->model_name."->".$index." : ''); ?>\"  />
	<?php echo form_error('".$index."'); ?>
</p>
";
			}

		break;

		case 'textarea':
		
			if ( $value['multilanguage'] == "TRUE")
			{
				foreach ($this->languages as $prefix=>$language)
				{
$data .="
<p>
	<label class='labelform' for='".$index."_".$prefix."'>".ucfirst($index)." ($prefix) ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>
	<textarea id=\"".$index."_".$prefix."\"  name=\"".$index."_".$prefix."\"  /><?php echo set_value('".$index."_".$prefix."', (isset(\$".$this->model_name."->".$index."_".$prefix.")) ? \$".$this->model_name."->".$index."_".$prefix." : ''); ?></textarea>
	<?php echo form_error('".$index."_".$prefix."'); ?>
</p>
";
				}
			}
			else
			{
$data .="
<p>
	<label class='labelform' for='".$index."'>".ucfirst($index)." ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>
	<textarea id=\"".$index."\"  name=\"".$index."\"  /><?php echo set_value('".$index."', (isset(\$".$this->model_name."->".$index.")) ? \$".$this->model_name."->".$index." : ''); ?></textarea>
	<?php echo form_error('".$index."'); ?>
</p>
";
			}

		break;

		case 'checkbox':
$data .= "
<p>
	<input id='".$index."' ".(($value['checked'] == "TRUE") ?  ' checked '  :  '')."type='checkbox' name='".$index."' value='1' <?=preset_checkbox('".$index."', '1', (isset(\$".$this->model_name."->".$index.")) ? \$".$this->model_name."->".$index." : ''  )?> />&nbsp;<label class='labelforminline' for='".$index."'>".$value['label']." ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>
	<?php echo form_error('".$index."'); ?>
</p>
";

		break;

		case 'select':
$data .="
<p>
	<label class='labelform' for='".$index."'>".ucfirst($index)." ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>

	<select name='".$index."' id='".$index."'>
		<option value=''><?=lang('web_choose_option')?></option>";

		foreach($value['options'] as $index2=>$value2)
		{
				$data .= $this->sl.$this->tab."<option value='".$value2['value']."' <?= preset_select('".$index."', '".$value2['value']."', (isset(\$".$this->model_name."->".$index.")) ? \$".$this->model_name."->".$index." : ''  ) ?>>".$value2['text']."</option>";
		}
		

$data .="		
	</select>
	<?php echo form_error('".$index."'); ?>
</p>
";
		break;


		case 'selectbd':
$data .="
<p>
	<label class='labelform' for='".$index."'>".ucfirst($index)." ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>
	<select name='".$index."' id='".$index."' size='".$value['size']."'>
		<option value=''><?=lang('web_choose_option')?></option>
		<?php foreach (\$array_".strtolower($value['options']['model'])." as \$item): ?>
			<option value=\"<?=\$item->".$value['options']['field_value'].";?>\" <?= preset_select('".$index."', \$item->".$value['options']['field_value'].", (isset(\$".$this->model_name."->".$index.")) ? \$".$this->model_name."->".$index." : ''  ) ?>><?=\$item->".$value['options']['field_text'].";?></option>
		<?php endforeach ?>
		";		

$data .="		
	</select>
	<?php echo form_error('".$index."'); ?>
</p>
";
		break;

		case 'radio':
$data .= "
<p>
	<label class='labelform'>".ucfirst($index)." ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>";

	$c = 0;
	foreach($value['options'] as $index2 => $value2)
	{
		$data .= $this->sl.$this->tab."<input type='radio' name='".$index."' id='".$index."_$c' value='".$value2['value']."' <?=preset_radio('".$index."', '".$value2['value']."', (\$".$this->model_name."->".$index." != '') ? \$".$this->model_name."->".$index." : '".$value['checked']."'  );?> > <label class='labelforminline' for='".$index."_$c'> ".$value2['label']." </label>";
		$c++;
	}

$data .= "
	<?php echo form_error('".$index."'); ?>
</p>
";

		break;

		case 'datepicker':
$data .="
<p>
	<label class='labelform' for='".$index."'>".ucfirst($index)." ".(($value['required'] == "TRUE") ? "<span class='required'>*</span>" : "") ."</label>
	<input id='".$index."' type='text' name='".$index."' maxlength='' class='datepicker' value=\"<?php echo set_value('".$index."', (\$".$this->model_name."->".$index." != '') ? \$".$this->model_name."->".$index."->format('d-m-Y') : ''); ?>\"  />
	<?php echo form_error('".$index."'); ?>
</p>
";
		break;
	}
}


$data .= "
<p>
    <?php echo form_submit( 'submit', (\$updType == 'edit') ? lang('web_edit') : lang('web_add'), ((\$updType == 'create') ? \"id='submit' class='bcreateform'\" : \"id='submit' class='beditform'\")); ?>
</p>

<?=form_hidden('page',set_value('page', \$page)) ?>

<?php if (\$updType == 'edit'): ?>
	<?=form_hidden('id',\$".$this->model_name."->id) ?>
<?php endif ?>

<?php echo form_close(); ?>
";

		$this->create_folder_if_no_exists(APPPATH.'views/'.$this->controller_name);

		if ( $this->save_file('create', "views/".$this->controller_name."/", trim( $data ) ) === TRUE )
			return TRUE;
		else
		{
			$this->errors = "Error creando el archivo view/".$this->controller_name."/create.php";
			return FALSE;
		}
	}


	private function create_view_list()
	{
		$data = "";

foreach ($this->arrayjson as $index => $value )
{
		switch ($value['type'])
		{
		case 'text':
		case 'textarea':

		if ( $value['multilanguage'] == "TRUE")
		{
			$data .= "<?php \$".$index."_with_actual_language = '".$index."_'.\$this->config->item('prefix_language'); ?>".$this->sl;
		}

		break;
	}
}


$data .= "
<div id='content-top'>
    <h2>Listado de ".$this->controller_name."</h2>
   
    <a href='/".$this->controller_name."/create/<?=\$page?>' class='bcreate'>Crear ".$this->model_name."</a>
  
    <span class='clearFix'>&nbsp;</span>
</div>

<?php if (\$".$this->controller_name."): ?>

<div class='clear'></div>

	<table class='ftable' cellpadding='5' cellspacing='5'>

		<thead>";

			foreach ($this->arrayjson as $index => $value )
			{
				switch ($value['type'])
				{
					case 'text':
					case 'textarea':

			$data .="
			<th>".ucfirst($index)."</th>";

					break;
				}
			}


			$data .="
			<th colspan='2'><?=lang('web_options')?></th>
		</thead>

		<tbody>
			<?php foreach (\$".$this->controller_name." as \$".$this->model_name."): ?>
				
				<tr>
				";
					foreach ($this->arrayjson as $index => $value )
			    	{
			      		switch ($value['type'])
			      		{
			        		case 'text':
			        		case 'textarea':

		        			if ( $value['multilanguage'] == "TRUE")
		        			{
		        				$data .= $this->tab."<td><?=\$".$this->model_name."->\$".$index."_with_actual_language;?></td>";
		        			}
		        			else
		        			{
		        				$data .= $this->tab."<td><?=\$".$this->model_name."->".$index.";?></td>";
		        			}

			        		break;
			        	}
			        }
				$data .= "
					<td width='60'><a class='ledit' href='/".$this->controller_name."/edit/<?=\$".$this->model_name."->id?>/<?=\$page?>'><?=lang('web_edit')?></a></td>
					<td width='60'><a class='ldelete' onClick=\"return confirm('<?=lang('web_confirm_delete')?>')\" href='/".$this->controller_name."/delete/<?=\$".$this->model_name."->id?>/<?=\$page?>'><?=lang('web_delete')?></a></td>
				</tr>
				
			<?php endforeach ?>
		</tbody>

	</table>

	<?php echo \$links; ?>

<?php else: ?>

	<p class='text'><?=lang('web_no_elements');?></p>

<?php endif ?>

		";

		$this->create_folder_if_no_exists(APPPATH.'views/'.$this->controller_name);

		if ( $this->save_file('list', "views/".$this->controller_name."/", trim( $data ) ) === TRUE )
			return TRUE;
		else
		{
			$this->errors = "Error creando el archivo view/".$this->controller_name."/list.php";
			return FALSE;		
		}
	}


	private function modify_routes()
	{
		$data = $this->sl.$this->sl;
		$data .="//routes para ".$this->controller_name.$this->sl;
		$data .= "\$route['".$this->controller_name."/(:num)'] = '".$this->controller_name."/index/$1';";

		if ( $this->save_file('routes', "config/", $data, 'a' ) === TRUE )
			return TRUE;
		else
		{
			$this->errors = "Error modificando  el archivo config/routes.php";
			return FALSE;
		}
	}

	private function modify_menu()
	{
		$data = $this->sl.$this->sl;
		$data .= "<?php  \$mactive = (\$this->uri->rsegment(1) == '".$this->controller_name."')  ? \"class='selected'\" : \"\" ?>".$this->sl;
		$data .= "<li <?=\$mactive?>><a href=\"/".$this->controller_name."/\" style=\"background-position: 0px 0px;\">".ucfirst($this->controller_name)."</a></li>";

		if ( $this->save_file('_menu', "views/partials/", $data, 'a' ) === TRUE )
			return TRUE;
		else
		{
			$this->errors = "Error modificando  el archivo config/routes.php";
			return FALSE;
		}
	}

	private function create_folder_if_no_exists($path)
	{
		if (@mkdir($path))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}


	private function save_file($filename, $path, $data, $mode = "w")
	{
		$file = fopen(APPPATH.$path.$filename.".php" , $mode);
      
		if ($file)
		{
			$result = fputs ($file, $data);
		}

		fclose ($file);

		if ($result)
			return TRUE;
		else
		{
			$this->errors = "Error creando el archivo ".APPPATH.$path.$filename.".php";
			return FALSE;
		}
	}

}