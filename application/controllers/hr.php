<?php
class Hr extends CI_Controller {

	// num of records per page
	private $limit = 5000;
	
	function hr(){
		parent::__construct();

		// load library
		$this->load->library(array('table','validation','authlib'));
		
		// load helper
		$this->load->helper('url');

    	// load model
		$this->load->model('hrModel','',TRUE);
	
}

	function index($offset = 0){

		// offset
		$uri_segment = 3;
		$offset = $this->uri->segment($uri_segment);
		
		// load data
		$hrs = $this->hrModel->get_paged_list($this->limit, $offset)->result();
		
		// generate pagination
		$this->load->library('pagination');
		$config['base_url'] = site_url('hr/index/');
 		$config['total_rows'] = $this->hrModel->count_all();
 		$config['per_page'] = $this->limit;
		$config['uri_segment'] = $uri_segment;
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		// generate table data
		$this->load->library('table');
		$this->table->set_empty("&nbsp;");
		$this->table->set_heading('Emp_No', 'First Name', 'Last Name','Gender', 'Date of Birth', 'Hire Date','Actions');
		$i = 0 + $offset;
		foreach ($hrs as $hr){
			$this->table->add_row($hr->emp_no, $hr->first_name,$hr->last_name, strtoupper($hr->gender)=='M'? 'Male':'Female', date('d-m-Y',strtotime($hr->birth_date)), date('d-m-Y',strtotime($hr->hire_date)), 
				anchor('hr/view/'.$hr->emp_no,'view',array('class'=>'view')).' '.
				anchor('hr/update/'.$hr->emp_no,'update',array('class'=>'update')).' '.
				anchor('hr/delete/'.$hr->emp_no,'delete',array('class'=>'delete','onclick'=>"return confirm('Are you sure want to delete this hr?')"))
			);
		}
		$data['table'] = $this->table->generate();
		
		// load view
		$this->load->view('hrList', $data);
	}
	
	function add(){
		// set validation properties
		$this->_set_fields();
		
		// set common properties
		$data['title'] = 'Add new hr';
		$data['message'] = '';
		$data['action'] = site_url('hr/addhr');
		$data['link_back'] = anchor('hr/index/','Back to list of hrs',array('class'=>'back'));
	
		// load view
		$this->load->view('hrEdit', $data);
	}
	
	function addhr(){
		// set common properties
		$data['title'] = 'Add new hr';
		$data['action'] = site_url('hr/addhr');
		$data['link_back'] = anchor('hr/index/','Back to list of hrs',array('class'=>'back'));
		
		// set validation properties
		$this->_set_fields();
		$this->_set_rules();
		
		// run validation
		if ($this->validation->run() == FALSE){
			$data['message'] = '';
		}else{
			// save data
			var_dump($_POST);
			$hr = array('first_name' => $this->input->post('first_name'),
							'last_name' => $this->input->post('last_name'),
							'gender' => $this->input->post('gender'),
							'birth_date' => date('Y-m-d', strtotime($this->input->post('birth_date'))),
							'hire_date' => date('Y-m-d', strtotime($this->input->post('hire_date'))));
			$id = $this->hrModel->save($hr);
			
			// set form input name="id"
			$this->validation->id = $id;
			
			// set user message
			$data['message'] = '<div class="success">add new hr success</div>';
		}
		
		// load view
		$this->load->view('hrEdit', $data);
	}
	
	function view($id){
		// set common properties
		$data['title'] = 'hr Details';
		$data['link_back'] = anchor('hr/index/','Back to list of hrs',array('class'=>'back'));
		
		// get hr details
		$data['hr'] = $this->hrModel->get_by_id($id)->row();
		
		// load view
		$this->load->view('hrView', $data);
	}
	
	function update($id){
		// set validation properties
		$this->_set_fields();
		
		// prefill form values
		$hr = $this->hrModel->get_by_id($id)->row();
		$this->validation->id = $id;
		$this->validation->first_name = $hr->first_name;
		$this->validation->last_name = $hr->last_name;
		$_POST['gender'] = strtoupper($hr->gender);
		$this->validation->birth_date = date('d-m-Y',strtotime($hr->birth_date));
		$this->validation->hire_date = date('d-m-Y',strtotime($hr->hire_date));
		
		// set common properties
		$data['title'] = 'Update hr';
		$data['message'] = '';
		$data['action'] = site_url('hr/updatehr');
		$data['link_back'] = anchor('hr/index/','Back to list of hrs',array('class'=>'back'));
	
		// load view
		$this->load->view('hrEdit', $data);
	}
	
	function updatehr(){
		// set common properties
		$data['title'] = 'Update hr';
		$data['action'] = site_url('hr/updatehr');
		$data['link_back'] = anchor('hr/index/','Back to list of hrs',array('class'=>'back'));
		
		// set validation properties
		$this->_set_fields();
		$this->_set_rules();
		
		// run validation
		if ($this->validation->run() == FALSE){
			$data['message'] = '';
		}else{
			// save data
			$id = $this->input->post('id');
			$hr = array('first_name' => $this->input->post('first_name'),
							'last_name' => $this->input->post('last_name'),
							'gender' => $this->input->post('gender'),
							'birth_date' => date('Y-m-d', strtotime($this->input->post('birth_date'))),
							'hire_date' => date('Y-m-d', strtotime($this->input->post('hire_date'))));
			$this->hrModel->update($id,$hr);
			
			// set user message
			$data['message'] = '<div class="success">update hr success</div>';
		}
		
		// load view
		$this->load->view('hrEdit', $data);
	}
	
	function delete($id){
		// delete hr
		$this->hrModel->delete($id);
		
		// redirect to hr list page
		redirect('hr/index/','refresh');
	}
	
	// validation fields
	function _set_fields(){
		$fields['id'] = 'id';
		$fields['emp_no'] = 'emp_no';
		$fields['first_name'] = 'first_name';
		$fields['last_name'] = 'last_name';
		$fields['gender'] = 'gender';
		$fields['birth_date'] = 'birth_date';
		$fields['hire_date'] = 'hire_date';
		
		$this->validation->set_fields($fields);
	}
	
	// validation rules
	function _set_rules(){
		
		$rules['first_name'] = 'trim|required';
		$rules['last_name'] = 'trim|required';
		$rules['gender'] = 'trim|required';
		$rules['birth_date'] = 'trim|required|callback_valid_date';
		$rules['hire_date'] = 'trim|required|callback_valid_date';
		
		$this->validation->set_rules($rules);
		
		$this->validation->set_message('required', '* required');
		$this->validation->set_message('isset', '* required');
		$this->validation->set_error_delimiters('<p class="error">', '</p>');


		
	}

	function updep()
	{
		$this->load->view('deptEdit');
	}
	
 function updtitle() 
{   
    $data = array(
        'titles' => 'titles', // pass the real table name
        'id' => $this->input->post('id'),
        'title' => $this->input->post('title')
        
    );

    $this->load->model('updateModel'); // load the model first
    if($this->updateModel->upddata($data)) // call the method from the model
    {
        	redirect('hr/index/');
    }
}

function updept()
{
	$this->load->view('moveEdit');
}

 function movedept() 
{   
    $emp_no = $this->input->get('emp_no');
    $dept_no = $this->input->get('dept_no');

    $this->load->model('updateDept'); // load the model first
    if($this->updateDept->upedit($emp_no, $dept_no)) // call the method from the model
    {
        	redirect('hr/index/');
    }
}
   
     function upsalary()
{
    $this->load->view('salaryEdit');

}

public function editsal() 
{   
    $emp_no = $this->input->get('emp_no');
    $salary = $this->input->get('salary');

    $this->load->model('salary_model'); // load the model first
    if($this->salary_model->upsalary($emp_no, $salary)) // call the method from the model
    {
        	redirect('hr/index/');
    }
 

}

function promoteMan(){

	$this->load->view('promoteMan');
}

function manPromote(){
	$emp_no = $this->input->post('emp_no');
	$dept_no = $this->input->post('dept_no');
	$this->load->model('hrModel'); 
	$this->hrModel->promoMan($emp_no, $dept_no);
	$this->load->view('success');
}

function removeMan()
{
    $this->load->view('salaryEdit');
}
	function deleteMan($id){
		// delete hr
		$this->hrModel->deleteMan($id);

		
		// redirect to hr list page
		redirect('hr/index/','refresh');
	}

}