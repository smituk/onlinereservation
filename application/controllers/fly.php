<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Fly extends CI_Controller {
    
    
    public function result() {
        
        $this->load->model("flight_search");
        
        
        $this->load->view("table_result", $this->flight_search->basic_flight($this->input->post("from"), $this->input->post("to"), $this->input->post("departure"), $this->input->post("return")));
        
    }
    
}
?>
