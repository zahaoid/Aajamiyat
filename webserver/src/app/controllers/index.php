<?php

// Database credentials

require_once 'errorHandler.php';
require_once 'request_handler.php';
require_once '../views/templates.php';
require_once '../views/views.php';
require_once '../models/config.php';
require_once '../models/connect.php';
require_once '../models/querier.php';

ob_start();
session_start();

const APP_NAME = 'مسرد الألفاظ الأعجمية';

// $data = array(
//     array(
//         "original"=> "Bir de",
//         "origin"=> "التركية",
//         "forms"=> array("بردو" ,"برضو", "برضه", "برده"),
//         "meanings"=> array("أيضًا", "كذلك"),
//         "examples"=> array(
//             "أنا مش هقدر أخلص الشغل بسرعة، بس أنت برضه حاول تساعدني",
//             "الكتب دي جديدة، وبرضه عندي كتب قديمة إذا عايز"
//         ),
//         "categories"=> array("العامية"),
//         "sources"=> array("معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي")
//     ),
//     array(
//         "original"=> "Merci",
//         "origin"=> "الفرنسية",
//         "forms"=> array("مرسي", "ميرسي"),
//         "meanings"=> array("شكرًا", "أشكرك"),
//         "examples"=> array(
//             "مرسي على المساعدة اللي قدمتها",
//             "قلتله ميرسي وهو ابتسم ومشي"
//         ),
//         "categories"=> array("العامية"),
//         "sources"=> array("معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي")
//     ),
//     array(
//         "original"=> "Kaput",
//         "origin"=> "الألمانية",
//         "forms"=> array("كابوت", "كابوتة"),
//         "meanings"=> array("تالف", "خربان"),
//         "examples"=> array(
//             "العربية دي كابوت من زمان، مش هتنفع تتصلح",
//             "التلفزيون وقع على الأرض وبقى كابوت"
//         ),
//         "categories"=> array("العامية"),
//         "sources"=> array("معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي")
//     ),
//     array(
//         "original"=> "Pantalon",
//         "origin"=> "الفرنسية",
//         "forms"=> array("بنطلون"),
//         "meanings"=> array("سروال", "ズズ"),
//         "examples"=> array(
//             "اشتريت بنطلون جديد من السوق",
//             "هو دايمًا يلبس بنطلون جينز"
//         ),
//         "categories"=> array("العامية"),
//         "sources"=> array("معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي")
//     ),
//     array(
//         "original"=> "Basta",
//         "origin"=> "الإيطالية",
//         "forms"=> array("بَسطة", "بِسطة"),
//         "meanings"=> array("مكان بيع على الرصيف", "كشك صغير"),
//         "examples"=> array(
//             "اشتريت الخضار من البسطة اللي في أول الشارع",
//             "فيه بسطة هناك بتبيع إكسسوارات حلوة"
//         ),
//         "categories"=> array("العامية"),
//         "sources"=> array("معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي")
//     )
// );

// foreach($data as $ent) submitNewEntry($ent);
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