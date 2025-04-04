<?php

include_once "macro.php";


class Main extends Macro { 
    public function __construct($appname, $navigation) {

        $content = <<<CONTENT
        <header>
            <h1>
                $appname
                <?php echo "teest"w ?>
            </h1>
            $navigation
        </header>
        <main>
            this is main
        </main>
        <footer>
            this is a footer
        </footer>
        CONTENT;
        parent::__construct($content);
    }
}