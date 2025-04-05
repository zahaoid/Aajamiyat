<?php

interface Template{

        public function render();
}
class Navigation implements Template{
    function render(){
        ?>
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
        <?php
    }
}

class Main implements Template{

    private Template $navigation;
    private $appname;
    function __construct($appname, $navigation){
        $this->appname = $appname;
        $this->navigation = $navigation;
    }

    function render(){
        ?>
            <header>
                <h1>
                    <?= $this->appname ?>
                </h1>
                <?php $this->navigation->render()  ?>
            </header>
            <main>
                this is main
            </main>
            <footer>
                this is a footer
            </footer>
        <?php
    }
}

class Base implements Template{

    private $appname;
    private Template $body;
    function __construct($appname, $body){
        $this->appname = $appname;
        $this->body = $body;
    }
    function render(){

        ?>
        <!DOCTYPE html>
            <html lang="ar" dir="rtl">
                <head>
                    <meta charset="utf-8">
                    <title><?= $this->appname ?></title>
                </head>
                <body>
                    <?php $this->body->render() ?>
                </body>
            </html>
        <?php

    }
}