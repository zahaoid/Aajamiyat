<?php

class Macro{

    private $content ;
    public function __construct($content){
        $this->content = $content;
    }


    function __tostring(){
        
        return $this->content;
    }
}