<?php

class View
{

    public $_html;

    public function __construct($data, $template){
        $this->_html = "";
        if (isset($data[0])) $this->makeLoopHtml($data, $template);
        else $this->makeHtml($data, $template);
    }

    private function makeHtml($data, $template){
        //replace in the template the {{ ... }} by the values
        $this->_html = str_replace(array_keys($data), $data, file_get_contents("template/".$template));
    }

    private function makeLoopHtml($data, $template){
        foreach ($data as $value) {
            $this->_html .= $this->makeHtml($value, $template);
        }
    }

}
