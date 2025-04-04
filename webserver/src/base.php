<?php

require_once 'macro.php';


class Base extends Macro{
    public function __construct($appname, $body){
        $conetnt = <<< CONTENT
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
            <head>
                <meta charset="utf-8">
                <title>$appname</title>
            </head>
            <body>
                $body
            </body>
        </html>
        CONTENT;
        parent::__construct($conetnt);

    }
}