<?php

error_reporting(E_ALL);
ini_set("error_log", '/dev/stderr');

set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');


function customErrorHandler($errno, $errstr, $errfile, $errline) {
    throw new ErrorException( $errstr,0, $errno, $errfile, $errline);
}

function customExceptionHandler($e){
    if(ob_get_length()) ob_clean();
    if ($e instanceof PageNotFoundException) {
        showNotFoundPage() ;
    }
    else {
        showErrorPage() ;
    }
    error_log('[#######!!!#######] ' . $e);
    die();
}

class PageNotFoundException extends Exception{}