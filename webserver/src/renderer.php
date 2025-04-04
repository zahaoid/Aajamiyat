<?php 


const BASE_TEMP_PATH = "layouts/base.php";
const MAIN_TEMP_PATH = "layouts/main.php";

function renderContent($contentPath, $base = false) {
    $contents = array();
    
    function pullNextContent(){
        if(!empty($contents)){
            include array_shift($contents);
        }
        else{
            new Exception("Content underflow");
        }
    }
    
    $contents[] = BASE_TEMP_PATH;
    if ($base) $contents[] = MAIN_TEMP_PATH;
    $contents[] = $contentPath;

    ob_start();
    pullNextContent();
    ob_flush();
}