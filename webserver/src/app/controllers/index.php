<?php

require_once 'errorHandler.php';
require_once 'request_handler.php';
require_once '../views/templates.php';
require_once '../views/views.php';
require_once '../models/config.php';
require_once '../models/connect.php';
require_once '../models/querier.php';
require_once '../../helpers.php';

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

$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($uri === '') $uri = '/'; 
$method = $_SERVER['REQUEST_METHOD'];
$request = [$uri, $method];

match ($request) {
    ['/', 'GET'] => showHomePage(),
    ['/submit-entry', 'GET'] => showEntrySubmissionForm(),
    ['/submit-entry', 'POST'] => recieveEntrySubmission(),
    ['/view-entry', 'GET'] => viewEntry(),
    ['/review-entries', 'GET'] => showReviewPage(),
    ['/login', 'GET'] => showLoginForm(),
    ['/login', 'POST'] => authenticate(),
    ['/logout', 'GET'] => logout(),
    ['/approve-entry', 'GET'] => approveSubmission(),
    ['/reject-entry', 'GET'] => deleteSubmission(),
    default => throw new PageNotFoundException('[' . implode(', ',$request) .']'),
};