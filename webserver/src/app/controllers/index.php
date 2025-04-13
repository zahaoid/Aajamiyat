<?php

// Database credentials

$config = [
    'sqlHostAddress' => getenv("MYSQL_HOST_ADDRESS"),
    'superUsername' => 'root',
    'superPassword' => getenv("MYSQL_ROOT_PASSWORD"),
    'webServerUsername' => getenv("MYSQL_USER"),
    'webServerPassword' => getenv("MYSQL_PASSWORD"),
    'dbName' => getenv("MYSQL_DATABASE"),
    'sqlFolderPath' => __DIR__ . '/sql',
    'migrationsFolderPath' => DIRECTORY_SEPARATOR . __DIR__ . '/sql/migrations',
    'versioningTableName' => 'version_control'
];


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
    ['/submit-entry', 'GET'] => showEntrySubmissionForm(),
    ['/submit-entry', 'POST'] => recieveEntrySubmission(),
    ['/view-entry', 'GET'] => viewEntry(),
    ['/review-entries', 'GET'] => showReviewPage(),
    ['/login', 'GET'] => showLoginForm(),
    ['/login', 'POST'] => authenticate(),
    ['/logout', 'GET'] => logout(),
    default => throw new PageNotFoundException('[' . implode(', ',$request) .']'),
};