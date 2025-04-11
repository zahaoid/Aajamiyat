<?php

require_once 'errorHandler.php';
require_once 'request_handler.php';
require_once '../../app/views/templates.php';
require_once '../../app/views/views.php';
require_once '../../app/models/connect.php';
require_once '../../app/models/querier.php';

ob_start();
session_start();

const APP_NAME = 'مسرد الألفاظ الأعجمية';

//xss prevention (hopefully)
$_GET = sanitizeInput($_GET);
$_POST = sanitizeInput($_POST);
$_REQUEST = (array)$_POST + (array)$_GET + (array)$_REQUEST + (array)$_SESSION;

function sanitizeInput($input){
    if (is_array($input)){
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
        return $input;
    }
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$request = [$uri, $method];

match ($request) {
    ['/', 'GET'] => showHomePage(),
    ['/entry_submission', 'GET'] => showEntrySubmissionForm(),
    ['/entry_submission', 'POST'] => recieveEntrySubmission(),
    ['/view-entry', 'GET'] => viewEntry(),
    default => throw new PageNotFoundException('[' . implode(', ',$request) .']'),
};