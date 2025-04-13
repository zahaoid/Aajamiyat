<?php

function recieveEntrySubmission(){
    $requiredFields = array("forms","original","origin");

    if(validateRequirement($requiredFields)){
        
        $data = array();
        $data["id"] = $_GET["id"]?? null;
        $data["origin"] = $_POST["origin"];
        $data["original"] = $_POST["original"];
        $data["forms"] = $_POST["forms"];
        $data["examples"] = $_POST["examples"] ?? null;
        $data["meanings"] = $_POST["meanings"] ?? null;
        $data["sources"] = $_POST["references"] ?? null;
        $data["categories"] = $_POST["categories"] ?? null;
        $newEntryId = submitNewEntry( $data);
        if($data["id"] == null){
            showMessageOnNextPage("رُصِدَت اللفظة وهي قيد المراجعة");
            header('Location:/');
        }
        else{
            showMessageOnNextPage("عُدِّلَت اللفظة وهي قيد المراجعة");
            header('Location:/view-entry?id=' . $newEntryId);
        }
        

    }
    else{
        ?>
            <script>
                alert("بعض الخانات الإلزامية ناقصة, إن كان هذا خللاً فأبلغ القائمين على الموقع");
            </script>
        <?php
    }
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

function showEntrySubmissionForm(){
    $id = $_GET['id']?? null;
    $lists = array();
    $lists['origins'] = getOrigins();
    $lists['sources'] = getReferences();
    $lists['categories'] = getCategories();
    _entrySubmission($lists, ($id && ($entry = fetchEntries($id)[0]?? null) )? $entry: null);
}

function showNotFoundPage(){
    http_response_code(404);
    _pageNotFound() ;
}

function showErrorPage(){
    http_response_code(500);
    _serverError();
}

function showHomePage(){
    $entries = fetchEntries(status: 'pending');
    _homePage($entries) ;
}

function viewEntry(){
    if(($id = $_GET['id']?? null) && !empty($entries = fetchEntries($id))){
        _entryView($entries[0]);
    }
    else{
        throw new PageNotFoundException();
    }
}

function showMessageOnNextPage(string $message){
    if (isset($_SESSION['messages']) == false) $_SESSION['messages'] = array();
    $_SESSION['messages'][] = $message;
}

function showReviewPage(){
    requireAdmin();
    _entriesReviewPage();
}

function showLoginForm(){
    _loginForm();
}

function authenticate(){
    global $config;
    if(isset($_POST['username'], $_POST['password']) && $config['webServerUsername'] == $_POST['username'] && $config['webServerPassword'] == $_POST['password']){
        $_SESSION['admin'] = true;
        header('Location:/');
        exit;
    }
    else{
        showMessageOnNextPage('كلمة السر واسم الدخول أو إحداهما خاطئ');
        header('Location:/login');
        exit;
    }
}

function logout(){
    if (isset($_SESSION['admin'])) unset($_SESSION['admin']);
    header('Location:/');
    exit;
}

function requireAdmin(){
    if (!isset($_SESSION['admin'])){
        header('Location: /login');
        exit();
    }
}