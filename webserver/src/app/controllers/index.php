<?php

require_once 'errorHandler.php';
require_once 'request_handler.php';
require_once '../../app/views/templates.php';
require_once '../../app/views/views.php';
require_once '../../app/models/connect.php';
require_once '../../app/models/querier.php';

ob_start();

const APP_NAME = 'مسرد الألفاظ الأعجمية';

//xss prevention (hopefully lol)
$_GET = sanitizeInput($_GET);

$_POST = sanitizeInput($_POST);
$_REQUEST = (array)$_POST + (array)$_GET + (array)$_REQUEST;

function sanitizeInput($input){
    if (is_array($input)){
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
        return $input;
    }
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}



// $data = array(
//     "original"=> "Bir de",
//     "origin"=> "التركية",
//     "forms"=> array("بردو" ,"برضو", "برضه", "برده"),
//     "meanings"=> array("أيضًا", "كذلك"),
//     "examples"=> array("أنا مش هقدر أخلص الشغل بسرعة، بس أنت برضه حاول تساعدني", "الكتب دي جديدة، وبرضه عندي كتب قديمة إذا عايز"),
//     "categories"=> array("العامية"),
//     "sources"=> array("معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي")dfhkvud
// );

// insertEntry($data);

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