<?php

require_once 'macro.php';

class Navigation extends Macro {

    public function __construct() {

        $content = <<< CONTENT
        <nav>
            <ul>
                <li>
                    <a href="/">
                    الواجهة
                    </a>
                </li>
                <li>
                    <a href="/entry_submission">
                    رصد الألفاظ
                    </a>
                </li>
                <li>
                    <a href="https://github.com/zahaoid/docker-apache-php-mysql-template">
                    القِتهب
                    </a>
                </li>
            </ul>
        </nav>
        CONTENT;

        parent::__construct($content);

    }
}