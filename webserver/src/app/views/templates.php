<?php

interface _Template{

        public function render();
}
class Navigation implements _Template{
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

class _Main implements _Template{

    private _Template $_main;
    function __construct($_main){
        $this->_main = $_main;
    }

    function render(){
        ?>
            <header>
                <h1>
                    <?= APP_NAME ?>
                </h1>
                <?php $nav = new Navigation(); $nav->render();  ?>
            </header>
            <main>
                <?php $this->_main->render() ?>
            </main>
            <footer>
                this is a footer
            </footer>
        <?php
    }
}

class _Base implements _Template{

    private _Template $_body;
    function __construct($_body){
        $this->_body = $_body;
    }
    function render(){

        ?>
        <!DOCTYPE html>
            <html lang="ar" dir="rtl">
                <head>
                    <meta charset="utf-8">
                    <title><?= APP_NAME ?></title>
                </head>
                <body>
                    <?php $this->_body->render() ?>
                </body>
            </html>
        <?php

    }
}

class _Empty implements _Template{

    function render(){
        echo 'This is an empty test template';
    }
}

class _ServerError implements _Template{
    function render(){
        echo 'نستميحك عذرًا حصل خلل, كرمًا أبلغ القائمين على الصفحة';
    }
}

class _PageNotFound implements _Template{
    function render(){
        echo 'ما من شيء هنا';
    }
}

class _DataList implements _Template{

    private $name, $list;
    public function __construct($name, $list) {
        $this->name = $name;
        $this->list = $list;
    }
    function render(){
        ?>
            <datalist id=<?= $this->name ?>>
                <?php foreach($this->list as $element): ?>
                    <option value=<?= $element ?>></option>
                <?php endforeach; ?>
            </datalist>
        <?php
    }
}
class _EntrySubmissionForm implements _Template{

    private $origins, $categories, $references;
    function __construct($origins, $categories, $references) {
        $this->origins = $origins;
        $this->categories = $categories;
        $this->references = $references;
    }

    function render(){
        ?>
        <form method="post">
            <fieldset>
                <legend>اللفظة:</legend>
                <?php $field = new _DynamicTextInput(name: 'forms', required: true); $field->render() ?>
            </fieldset>
            <fieldset>
                <legend>معناها المراد:</legend>
                <?php $field = new _DynamicTextInput(name: 'meanings'); $field->render() ?>
            </fieldset>
            <fieldset>
                <legend>أصل اللفظة:</legend>
                <label for="original">الكلمة مكتوبة بلغتها:</label>
                <input type="text" id="original" name="original" required>
                <label for="origin">من اللغة:</label>
                <input type="text" id="origin" name="origin" list="origins" required>
                <?php $datalist = new _DataList(name: 'origins', list: $this->origins); $datalist->render() ?>
            </fieldset>
            <fieldset>
                <legend>أمثلة:</legend>
                <?php $field = new _DynamicTextInput(name: 'examples'); $field->render() ?>
            </fieldset>
            <fieldset>
                <legend>التصانيف:</legend>
                <?php $field = new _DynamicTextInput(name: 'categories', list: 'categories'); $field->render() ?>
                <?php $datalist = new _DataList(name: 'categories', list: $this->categories); $datalist->render() ?>
            </fieldset>
            <fieldset>
                <legend>المراجع:</legend>
                <?php $field = new _DynamicTextInput(name: 'references', list: 'references'); $field->render() ?>
                <?php $datalist = new _DataList(name: 'references', list: $this->references); $datalist->render() ?>
            </fieldset>
            <button>رصد</button>
        </form>
        <?php
    }
}

class _DynamicTextInput{

    private $name, $list, $required;
    function __construct($name, $list = '', $required = false){
        $this->name = $name;
        $this->list = $list;
        $this->required = $required;
    }

    function render(){
        require_once('dynamic_text_input.html');
        ?>
            <div id="<?= $this->name ?>-input-container"></div>
            <button type="button" id="<?= $this->name ?>-add-button">إضافة</button>
            <button type="button" id="<?= $this->name ?>-delete-button">حذف</button>
            <script>
                addDynamicTextInputLogic('<?= $this->name ?>', '<?= $this->list ?>', <?= $this->required ?>);
            </script>
        <?php
    }
}
