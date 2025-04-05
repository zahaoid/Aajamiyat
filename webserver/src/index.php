<?php

const APP_NAME = 'معجم الألفاظ الأعجمية معربها ودخيلها';


require_once 'errorHandler.php';
require_once 'templates.php';
require_once 'views.php';

$uri = $_SERVER['REQUEST_URI'];

// $targetContent = match ($uri) {
//     '/' => 'latest.php',
//     '/entry_submission' => 'entry_submission.php',
//     default => null
// };

// if($targetContent==null){
//     throw new PageNotFoundException('Requested URI: $uri');
// }

render();

function render(){

    ob_start();
    homePage();
    ob_flush();

}
