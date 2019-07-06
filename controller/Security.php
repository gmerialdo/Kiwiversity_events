<?php

class Security
{

    public $_post = [];
    public $_file = [];
    public $_url  = [];

    public function __construct($args) {
        global $uri_Start;
        $this->_url = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        $this->_url = explode("/", $this->_url);
        $this->_url = array_slice($this->_url, $uri_Start);
        if (array_key_exists("new_image_file", $_FILES)){
            $this->_file["new_image_file"] = $_FILES["new_image_file"];
        }
        else {$this->_file["new_image_file"] = null;}
        if (isset($args["post"])){
          $this->_post = filter_input_array(INPUT_POST, $args["post"]);
        }
        //sanitize all dates!!
        $all_dates = ["payment_datetime", "start_date", "finish_date"]; // add here every date input name!
        foreach ($all_dates as $value) {
            if (!empty($this->_post[$value])){
                $this->_post[$value] = $this->sanitizeDate($this->_post[$value]);
            }
        }
        //sanitize all times!!
        $all_times = ["start_time", "finish_time"]; // add here every time input name!
        foreach ($all_times as $value) {
            if (!empty($this->_post[$value])){
                $this->_post[$value] = $this->sanitizeTime($this->_post[$value]);
            }
        }

    }

    public function sanitizeDate($date){
        $date = explode("-", $date);
        if (count($date)!=3) return false;
        for ($i=0; $i < 3; $i++) {
            $date[$i] = filter_var($date[$i], FILTER_SANITIZE_NUMBER_INT);
        }
        return implode("-", $date);
    }

    public function sanitizeTime($time){
        //transform am/pm time in 24h time
        $time = date("G:i", strtotime($time));
        $time = explode(":", $time);
        if (count($time)>3) return false;
        for ($i=0; $i < count($time); $i++) {
            $time[$i] = filter_var($time[$i], FILTER_SANITIZE_NUMBER_INT);
        }
        return implode(":", $time);
    }

    public function postEmpty(){
        return empty($this->_post);
    }

}
