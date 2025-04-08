<?php

abstract class _Template{

        protected abstract function writeToBuffer();

        final public function __tostring(){
            ob_start();
            $this->writeToBuffer();
            return ob_get_clean();
        }
}

// class _Empty extends _Template{

//     function writeToBuffer(){

//     }
// }
class Navigation extends _Template{
    function writeToBuffer(){
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

class _Main extends _Template{

    private ?_Template $_main;
    function __construct($_main){
        $this->_main = $_main;
    }

    function writeToBuffer(){
        ?>
            <header>
                <h1>
                    <?= APP_NAME ?>
                </h1>
                <?= new Navigation()?>
            </header>
            <main>
                <?= $this->_main ?>
            </main>
            <footer>
                this is a footer
            </footer>
        <?php
    }
}

class _Base extends _Template{

    private _Template $_body;
    function __construct($_body){
        $this->_body = $_body;
    }
    function writeToBuffer(){

        ?>
        <!DOCTYPE html>
            <html lang="ar" dir="rtl">
                <head>
                    <meta charset="utf-8">
                    <title><?= APP_NAME ?></title>
                </head>
                <body>
                    <?= $this->_body ?>
                </body>
            </html>
        <?php

    }
}

class _ServerError extends _Template{
    function writeToBuffer(){
        echo 'نستميحك عذرًا حصل خلل, كرمًا أبلغ القائمين على الصفحة';
    }
}

class _PageNotFound extends _Template{
    function writeToBuffer(){
        echo 'ما من شيء هنا';
    }
}

class _Entry extends _Template{
    
    private array $entryData;
    private bool $summarize;
    public function __construct($entryData, $summarize = false) {
        $this->entryData = $entryData;
        $this->summarize = $summarize;
    }

    function writeToBuffer(){
        ?>
        <article>
            <h2>الكلمة: <?php echo implode(', ', $this->entryData['forms']); ?></h2>
            <p><strong>دخيلة من:</strong> <?php echo $this->entryData['origin']; ?></p>
            <p><strong>أصلها:</strong> <?php echo $this->entryData['original']; ?></p>
            
            <?php if(!$this->summarize) : ?>
                <?php if ($this->entryData['meanings']): ?>
                <p><strong>المعنى المراد:</strong> <?php echo implode(', ', $this->entryData['meanings']); ?></p> 
                <?php endif ?>

                <?php if ($this->entryData['examples']): ?>
                <h3>سياقات:</h3>
                <ul>
                    <?php foreach($this->entryData['examples'] as $example): ?>
                        <li><?php echo $example; ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif ?>

                <?php if ($this->entryData['categories']): ?>
                <h3>التصنيف:</h3>
                <ul>
                    <?php foreach($this->entryData['categories'] as $category): ?>
                        <li><?php echo $category; ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif ?>

                <?php if ($this->entryData['sources']): ?>
                <h3>المراجع:</h3>
                <ul>
                    <?php foreach($this->entryData['sources'] as $reference): ?>
                        <li><?php echo $reference; ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif ?>
            <?php endif ?>
        </article>
        <?php
    }
}

class _EntryList extends _Template{

    private $entries;
    function __construct($entries){
        $this->entries = $entries;
    }

    function writeToBuffer(){
        foreach($this->entries as $entry){
            echo new _Entry($entry, true);
            $id = $entry["id"];
            ?> <a href="view-entry?id=<?= $id ?>">اطلاع</a> <?php
        }
    }
}

class _EntryView extends _Template{ 

    private $entry;
    function __construct($entry){
        $this->entry = $entry;
    }

    function writeToBuffer(){
        echo new _Entry($this->entry, true);
        $id = $this->entry["id"];
        ?> 
        <button id="edit-button">تعديل</button>
        <script>
            const editButton = document.getElementById(`edit-button`);
            editButton.addEventListener('click', function(){
                var xhr = new XMLHttpRequest();

                // Configure it as a GET request to a URL (e.g., 'example.com/data')
                xhr.open('GET', '/', true);

                // Set up what to do when the request completes
                xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // The request was successful
                    console.log('Response:', xhr.responseText);
                } else if (xhr.readyState === 4) {
                    // There was an error with the request
                    console.log('Error:', xhr.status);
                }
                };

                // Send the request
                xhr.send();
            })
        </script>
        <?php
    }
}


class _DataList extends _Template{

    private $name, $list;
    public function __construct($name, $list) {
        $this->name = $name;
        $this->list = $list;
    }
    function writeToBuffer(){
        ?>
            <datalist id=<?= $this->name ?>>
                <?php foreach($this->list as $element): ?>
                    <option value=<?= $element ?>></option>
                <?php endforeach; ?>
            </datalist>
        <?php
    }
}
class _EntrySubmissionForm extends _Template{

    private $suggestionLists;
    private $entry;
    function __construct($suggestionLists, $entry = null) {
        $this->suggestionLists = $suggestionLists;
        $this->entry = $entry;
    }

    function writeToBuffer(){
        ?>
        <form method="post">
            <fieldset>
                <legend>اللفظة:</legend>
                <?= new _DynamicTextInput(name: 'forms', required: true, preloadedValues: $this->entry['forms']?? null ) ?>
            </fieldset>
            <fieldset>
                <legend>معناها المراد:</legend>
                <?= new _DynamicTextInput(name: 'meanings', preloadedValues: $this->entry['meanings']?? null) ?>
            </fieldset>
            <fieldset>
                <legend>أصل اللفظة:</legend>
                <label for="original">الكلمة مكتوبة بلغتها:</label>
                <input type="text" id="original" name="original" <?php if ($this->entry): ?> value="<?= $this->entry['original'] ?>" <?php endif ?> required>
                <label for="origin">من اللغة:</label>
                <input type="text" id="origin" name="origin" list="origins" <?php if ($this->entry): ?> value="<?= $this->entry['origin'] ?>" <?php endif ?>  required>
                <?= new _DataList(name: 'origins', list: $this->suggestionLists['origins']) ?>
            </fieldset>
            <fieldset>
                <legend>أمثلة:</legend>
                <?= new _DynamicTextInput(name: 'examples', preloadedValues: $this->entry['examples']?? null) ?>
            </fieldset>
            <fieldset>
                <legend>التصانيف:</legend>
                <?= new _DynamicTextInput(name: 'categories', list: 'categories', preloadedValues: $this->entry['categories']?? null) ?>
                <?= new _DataList(name: 'categories', list: $this->suggestionLists['categories']) ?>
            </fieldset>
            <fieldset>
                <legend>المراجع:</legend>
                <?= new _DynamicTextInput(name: 'references', list: 'references', preloadedValues: $this->entry['sources']?? null) ?>
                <?= new _DataList(name: 'references', list: $this->suggestionLists['sources']) ?>
            </fieldset>
            <button>رصد</button>
        </form>
        <?php
    }
}

class _DynamicTextInput extends _Template{

    private $name, $list, $required, $preloadedValues;
    function __construct($name, $list = '', $required = false, $preloadedValues = null){
        $this->name = $name;
        $this->list = $list;
        $this->required = $required;
        $this->preloadedValues = $preloadedValues;
    }

    function writeToBuffer(){
        require_once('dynamic_text_input.html');
        ?>
            <div id="<?= $this->name ?>-input-container"></div>
            <button type="button" id="<?= $this->name ?>-add-button">إضافة</button>
            <button type="button" id="<?= $this->name ?>-delete-button">حذف</button>
            <script>
                addDynamicTextInputLogic('<?= $this->name ?>', '<?= $this->list ?>', '<?= $this->required ?>', <?= json_encode($this->preloadedValues) ?>);
            </script>
        <?php
    }
}
