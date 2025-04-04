<?php
include("database/connect.php");

$requiredFields = array("forms","original","origin");

$cache = array();

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(validateRequirement($requiredFields)){
        $entries_query = 'insert into entries (entry, origin, original) values (?, ?, ?);';

        $forms_query = 'insert into entry_forms(entry_id, form) values (?, ?);';

        $meanings_query = 'insert into entry_meanings(entry_id, meaning) values(?, ?);';

        $examples_query = 'insert into entry_examples(entry_id, example) values(?, ?);';


    }
    else{
        echo <<< LOGIC
            <script>
                alert("بعض الخانات الإلزامية ناقصة, إن كان هذا خللاً فأبلغ القائمين على الموقع");
            </script>
        LOGIC;
    }
    function validateRequirement($requiredFields){
        foreach ($requiredFields as $requiredField){
            if (!isset($_POST[$requiredField])){
                echo $requiredField;
                return false;
            }
        }
        return true;
    }

}
else if ($_SERVER['REQUEST_METHOD'] === 'GET'){
    $origins_query = 'select * from origins';
    $categories_query = 'select * from categories';
    $references_query = 'select * from sources';

    $connection = connect($config);

    $result = mysqli_query($connection, $origins_query);
    while ($row = mysqli_fetch_assoc($result)){
        $cache['origins'][] = $row['origin'];
    }
    
    $result = mysqli_query($connection, $categories_query);
    while ($row = mysqli_fetch_assoc($result)){
        $cache['categories'][] = $row['category'];
    }
    
    $result = mysqli_query($connection, $references_query);
    while ($row = mysqli_fetch_assoc($result)){
        $cache['sources'][] = $row['source'];
    }

    print_r($cache);



}


// if(is_array($_POST['forms'])){
//     foreach($_POST['forms'] as $id => $value){

//     }
// }

include('dynamic_text_input.php');

?>




<form method="post">
    <fieldset>
        <legend>اللفظة:</legend>
        <?php addDynamicTextInput(name: 'forms', required: true) ?>
    </fieldset>

    <fieldset>
        <legend>معناها المراد:</legend>
        <?php addDynamicTextInput(name: 'meanings') ?>
    </fieldset>

    <fieldset>
        <legend>أصل اللفظة:</legend>
        <label for="original">الكلمة مكتوبة بلغتها:</label>
        <input type="text" id="original" name="original" required>
        <label for="origin">من اللغة:</label>
        <input type="text" id="origin" name="origin" list="origins" required>
        <datalist id="origins">
            <option value="التركية"></option>
            <option value="الفارسية"></option>
            <option value="الهندية"></option>
        </datalist>
    </fieldset>

    <fieldset>
        <legend>أمثلة:</legend>
        <?php addDynamicTextInput(name: 'examples') ?>
    </fieldset>
    <fieldset>
        <legend>التصانيف:</legend>
        <?php addDynamicTextInput(name: 'categories', list: 'categories') ?>
        <datalist id="categories">
            <option value="العامية"></option>
            <option value="الفصحى"></option>
            <option value="ألفاظ القرآن"></option>
        </datalist>
    </fieldset>

    <fieldset>
        <legend>المراجع:</legend>
        <?php addDynamicTextInput(name: 'references', list: 'references') ?>
        <datalist id="references">
            <option value="معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي"></option>
        </datalist>
    </fieldset>

    <button>رصد</button>
</form>

