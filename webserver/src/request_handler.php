<?php 


function homePage(){
    $appname = 'معجم الألفاظ الأعجمية المعربة والدخيلة';
    $navigation = new Navigation();
    $main = new Main($appname, $navigation);
    $base = new Base($appname, $main);
    eval ( "?>" . $base);
}

