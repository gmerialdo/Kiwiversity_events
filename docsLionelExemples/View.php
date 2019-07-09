<?php

class View
{
  $this->html = "";
  function __construct($data, $template)
  {
    $this->data = $data;
    $this->template = $template;
    if (isset($data[0])) $this->makeLoopHtml();
    else $this->makeHtml();
  }

  private function makeLoopHtml(){
    $size = count($this->data);
    for ($i=0; $i<$size; $i++){
      $this->html .= $this->mergeWithTemplate($this->data[$i]);
    }
  }

  private function makeHtml(){
    $this->html = $this->mergeWithTemplate($this->data);
  }

  private function mergeWithTemplate($args){
    return str_replace(
      array_keys($args),
      $args,
      file_get_contents("/template/".$this->template)
    );
  }
}

?>
