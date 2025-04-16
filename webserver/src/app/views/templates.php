<?php

const FLAGS = array(
    "التركية" => "🇹🇲🇹🇷",
    "الفارسية" => "🇮🇷",
    "الإنجليزية" => "🇬🇧🇺🇸",
    "الفرنسية" => "🇫🇷",
    "الإسبانية" => "🇪🇸",
    "الألمانية" => "🇩🇪",
    "الإيطالية" => "🇮🇹",
    "البرتغالية" => "🇵🇹🇧🇷",
    "الروسية" => "🇷🇺",
    "الهندية" => "🇮🇳",
    "الصينية" => "🇨🇳",
    "اليابانية" => "🇯🇵",
    "الكورية" => "🇰🇷",
    "البولندية" => "🇵🇱",
    "الرومانية" => "🇷🇴",
    "اليونانية" => "🇬🇷",
    "السويدية" => "🇸🇪",
    "النرويجية" => "🇳🇴",
    "الدنماركية" => "🇩🇰",
    "الهولندية" => "🇳🇱",
    "السويسرية" => "🇨🇭",
);


abstract class _Template{

        protected abstract function writeToBuffer();

        final public function __tostring(){
            ob_start();
            $this->writeToBuffer();
            return ob_get_clean();
        }
}


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
                        <a href="/submit-entry">
                        رصد الألفاظ
                        </a>
                    </li>
                    <li>
                        <a href="review-entries">
                        صفحة الأدمن
                        </a>
                    </li>
                    <?php if (isset($_SESSION['admin'])) : ?>
                    <li>
                        <a href="logout">
                        خروج
                        </a>
                    </li>
                    <?php else: ?>
                    <li>
                        <a href="login">
                        دخول
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="https://github.com/zahaoid/Aajamiyat">
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
                <?php if (isset($_SESSION['messages'])): ?>
                <div class="messages">
                    <?php foreach ($_SESSION['messages'] as $message): ?>
                    <p><?= $message ?></p>
                    <script>alert("<?= $message ?>")</script>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['messages']) ; endif; ?>
            </header>
            <main>
                <div id="content"><?= $this->_main ?></div>
            </main>
            <footer>
                حقوق المراجع محفوظة لأصحابها
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
                    <title><?php echo ($GLOBALS['title']?? APP_NAME) ?></title>
                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@100..900&display=swap" rel="stylesheet">
                    <style><?php require_once('style.css') ?></style>
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
        ?>
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
            <p style="font-size: 60px;">
                نستميحك عذرًا حصل خلل, كرمًا أبلغ القائمين على الصفحة
            </p>
        </div>
        <?php
    }
}

class _PageNotFound extends _Template{
    function writeToBuffer(){
        ?>
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
            <p style="font-size: 60px;">
                ما من شيء هنا,
                إما أنك أخطأت في الرابط أو أن الصفحة محذوفة
            </p>
        </div>
        <?php
    }
}

abstract class _Entry extends _Template{

    protected array $entryData;
    public function __construct($entryData) {
        $this->entryData = $entryData;
    }

    function echoForms(){
        echo implode(', ', $this->entryData['forms']);
    }

    function echoOrigin(){
        echo ($this->entryData['origin'] . (isset(FLAGS[$this->entryData['origin']]) ? " " . FLAGS[$this->entryData['origin']] : ""));
    }

    function echoMeanings(){
        echo implode(', ', $this->entryData['meanings']);
    }

    function echoExamples(){
        ?>
        <ul>
            <?php foreach($this->entryData['examples'] as $example): ?>
                <li><?php echo $example; ?></li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    function echoCategories(){
        ?>
        <ul>
            <?php foreach($this->entryData['categories'] as $category): ?>
                <li><?php echo $category; ?></li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    function echoSources(){
        ?>
        <ul>
            <?php foreach($this->entryData['sources'] as $reference): ?>
                <li><?php echo $reference; ?></li>
                <?php endforeach; ?>
        </ul>
        <?php
    }

}

class _EntrySummary extends _Entry{

    function writeToBuffer(){
        ?>
        <article class="entry-summary">
            <div class="categories">
                <?php if ($this->entryData['categories']): ?>
                <?php $this->echoCategories() ?>
                <?php endif ?>
            </div>
            <div class="info">
                <h2><?php $this->echoForms() ?></h2>
                <p><strong>من اللغة: </strong><?php $this->echoOrigin() ?></p>
                <p><strong>أصلها: </strong><?php echo $this->entryData['original']; ?></p>
            </div>
        </article>
        <?php
    }
}

class _EntryDetailed extends _Entry{
    


    function writeToBuffer(){
        ?>
        <article class="entry-detailed">
            <h2> الكلمة: <?php $this->echoForms() ?></h2>
            <p><strong>من اللغة: </strong><?php $this->echoOrigin() ?></p>
            <p><strong>أصلها: </strong><?php echo $this->entryData['original']; ?></p>
            
            <?php if ($this->entryData['meanings']): ?>
            <p><strong>المعنى المراد:</strong> <?php $this->echoMeanings() ?></p> 
            <?php endif ?>

            <?php if ($this->entryData['examples']): ?>
            <h3>سياقات:</h3>
            <?php $this->echoExamples() ?>
            <?php endif ?>

            <?php if ($this->entryData['categories']): ?>
            <h3>التصنيف:</h3>
            <?php $this->echoCategories() ?>
            <?php endif ?>

            <?php if ($this->entryData['sources']): ?>
            <h3>المراجع:</h3>
            <?php $this->echoSources() ?>
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
            ?> 
            <section> 
                <?php
                echo new _EntrySummary($entry);
                $id = $entry["entry_id"];
                ?> 
                <a href="view-entry?id=<?= $id ?>">اطلاع</a> 
            </section> 
            <?php
            
        }
    }
}

class _ReviewEntryList extends _Template{
    private $approvedEntries;
    private $pendingEntries;
    private $entriesCount;
    function __construct($approvedEntries, $pendingEntries){
        $this->entriesCount = count($approvedEntries);
        if($this->entriesCount != count($pendingEntries)){
            throw new InvalidArgumentException("Entries provided have different lengths");
        }
        $this->approvedEntries = $approvedEntries;
        $this->pendingEntries = $pendingEntries;
    }

    function writeToBuffer(){
        for($i = 0 ; $i < $this->entriesCount ; $i++){
            $approvedEntry = $this->approvedEntries[$i];
            $pendingEntry = $this->pendingEntries[$i];
            $newEntry = $approvedEntry['submission_id'] == null;
            $contextMessage = $newEntry? 'لفظة جديدة' : 'تعديل';
            ?> 
            <h3><?= $contextMessage ?></h3>
            <section> 
                <?php
                if (!$newEntry) echo new _EntryDetailed($approvedEntry);
                ?> <br> <?php
                echo new _EntryDetailed($pendingEntry);
                $id = $pendingEntry["submission_id"];
                ?> 
                <a href="approve-entry?submission_id=<?= $id ?>">قبول</a> 
                <a href="reject-entry?submission_id=<?= $id ?>">حذف</a> 
            </section> 
            <?php
            
        }
    }
}



class _EntryView extends _Template{ 

    private $entry;
    function __construct($entry){
        $this->entry = $entry;
    }

    function writeToBuffer(){
        ?> 
        <section> 
            <?php
            echo new _EntryDetailed($this->entry); $id = $this->entry["entry_id"];
            ?> 
            <a href="/submit-entry?id=<?= $id ?>" >تعديل</a>
        </section> 
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
            <datalist id= "<?=$this->name?>" >
                <?php foreach($this->list as $element):?>
                    <option value= "<?= $element ?>" ></option>
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
                <?= new _DynamicTextInput(attributes: array('name' => 'forms', 'required' => true, 'maxlength' => "255"), preloadedValues: $this->entry['forms']?? null ) ?>
                <p>۞ خانة إلزامية</p>
            </fieldset>
            <fieldset>
                <legend>معناها المراد:</legend>
                <?= new _DynamicTextInput(attributes: array('name' => 'meanings', 'maxlength' => "255"), preloadedValues: $this->entry['meanings']?? null) ?>
            </fieldset>
            <fieldset>
                <legend>أصل اللفظة:</legend>
                <label for="original">الكلمة مكتوبة بلغتها:</label>
                <input type="text" id="original" name="original" maxlength="255" <?php if ($this->entry): ?> value="<?= $this->entry['original'] ?>" <?php endif ?> required>
                <p>۞ خانة إلزامية</p>
                <label for="origin">من اللغة:</label>
                <input type="text" id="origin" name="origin" list="origins" maxlength="255" <?php if ($this->entry): ?> value="<?= $this->entry['origin'] ?>" <?php endif ?>  required>
                <p>۞ خانة إلزامية</p>
                <?= new _DataList(name: 'origins', list: $this->suggestionLists['origins']) ?>
            </fieldset>
            <fieldset>
                <legend>أمثلة:</legend>
                <?= new _DynamicTextInput(attributes: array('name' => 'examples', 'maxlength' => "255"), preloadedValues: $this->entry['examples']?? null) ?>
            </fieldset>
            <fieldset>
                <legend>التصانيف:</legend>
                <?= new _DynamicTextInput(attributes: array('name' => 'categories', 'list' => 'categories', 'maxlength' => "255"), preloadedValues: $this->entry['categories']?? null) ?>
                <?= new _DataList(name: 'categories', list: $this->suggestionLists['categories']) ?>
            </fieldset>
            <fieldset>
                <legend>المراجع:</legend>
                <?= new _DynamicTextInput(attributes: array('name' => 'references', 'list' => 'references', 'maxlength' => "255"), preloadedValues: $this->entry['sources']?? null) ?>
                <?= new _DataList(name: 'references', list: $this->suggestionLists['sources']) ?>
            </fieldset>
            <button class="submit-button" onclick="validate()">رصد</button>
        </form>

        <script>
            function validate(){
                const requiredInputs = document.querySelectorAll('[required]');
                const map = {
                    'forms[]': 'اللفظة',
                    'origin': 'أصل الكلمة',
                    'original': 'لغة الكلمة'
                }

                emptyInputs = [];
                requiredInputs.forEach((element, index) => {
                    if (element.value.trim() == "") {
                        emptyInputs.push(map[element.name]);
                    }
                });
                if (emptyInputs.length > 0){
                    message = 'تركتَ بعض الخانات الإلزامية فارغة: ';
                    emptyInputs.forEach(element => {message += '\n' + element})
                    alert(message);
                    return false;
                }
                return true;
            }
        </script>
        <?php
    }
}

class _DynamicTextInput extends _Template{

    private $attributes, $preloadedValues;
    function __construct($attributes, $preloadedValues = null){
        $this->attributes = $attributes;
        $this->preloadedValues = $preloadedValues;
    }

    function writeToBuffer(){
        require_once('dynamic_text_input.html');
        ?>
            <div id="<?= $this->attributes['name'] ?>-input-container"></div>
            <button type="button" id="<?= $this->attributes['name'] ?>-add-button" class="add-button">إضافة</button>
            <button type="button" id="<?= $this->attributes['name'] ?>-delete-button" class="delete-button">حذف</button>
            <script>
                addDynamicTextInputLogic(<?= json_encode($this->attributes) ?>, <?= json_encode($this->preloadedValues) ?>);
            </script>
        <?php
    }
}

class _LoginForm extends _Template{

    function writeToBuffer(){
        ?>
        <form action="login" method="post">
            <fieldset>
                <legend>معلومات الدخول:</legend>
                <label for="username">اسم الدخول</label>
                <input type="text" name="username" required>
                <label for="password">كلمة السر</label>
                <input type="password" name="password" required>
                <button type="submit">دخول</button>
            </fieldset>
        </form>
        <?php
    }
}