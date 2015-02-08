<?php
//$this->EE->cp->load_package_css('bootstrap');
if($this->session->flashdata('success')){
	echo "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert'>&times;</button><h4>Success!</h4>".$this->session->flashdata('success')."</div>";
}
elseif($this->session->flashdata('error')){
	echo "<div class='alert alert-error'><button type='button' class='close' data-dismiss='alert'>&times;</button><h4>Oops!</h4>".$this->session->flashdata('error')."</div>";
}
?>