<?php



require_once 'errorHandler.php';
include_once "main.php";
require_once 'navigation.php';
require_once 'base.php';

$uri = $_SERVER['REQUEST_URI'];

// $targetContent = match ($uri) {
//     '/' => 'latest.php',
//     '/entry_submission' => 'entry_submission.php',
//     default => null
// };

// if($targetContent==null){
//     throw new PageNotFoundException('Requested URI: $uri');
// }

$appname = 'معجم الألفاظ الأعجمية المعربة والدخيلة';


$navigation = new Navigation();
$main = new Main($appname, $navigation);
$base = new Base($appname, $main);
eval ( "?>" . $base);

