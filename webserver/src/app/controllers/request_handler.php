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
        $newEntryId = submitNewEntry( $data, isAdmin());
        $requireReviewMessage = isAdmin()? '' : " وهي قيد المراجعة";
        if($data["id"] == null){
            showMessageOnNextPage("رُصِدَت اللفظة" . $requireReviewMessage);
            header('Location:/');
        }
        else{
            showMessageOnNextPage("عُدِّلَت اللفظة" . $requireReviewMessage);
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
    _entrySubmission($lists, ($id && ($entry = fetchLatestApproved($id)?? null) )? $entry: null);
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
    $entries = fetchAllLatestApproved();
    _homePage($entries) ;
}

function viewEntry(){
    if(($id = $_GET['id']?? null) && !empty($entry = fetchLatestApproved($id))){
        _entryView($entry);
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
    $entries = fetchAllPending();
    _entriesReviewPage($entries['a_'], $entries['p_']);
}

function showLoginForm(){
    _loginForm();
}

function authenticate(){
    global $config;
    if(isset($_POST['username'], $_POST['password']) && $config['webServerUsername'] == $_POST['username'] && $config['webServerPassword'] == $_POST['password']){
        $_SESSION['admin'] = true;
        $location = $_SESSION['redirect_after_login']?? '/';
        header('Location:'. $location);
        exit;
    }
    else{
        showMessageOnNextPage('كلمة السر واسم الدخول أو إحداهما خاطئ');
        header('Location:/login');
        exit;
    }
}

function logout(){
    if (isset($_SESSION['admin'])) {
        unset($_SESSION['admin']);
        unset($_SESSION['redirect_after_login']);
    }
    header('Location:/');
    exit;
}

function requireAdmin(){
    if (!isset($_SESSION['admin'])){
        $_SESSION['redirect_after_login'] = $GLOBALS['uri'];
        header('Location: /login');
        exit();
    }
}

function isAdmin(){
    return isset($_SESSION['admin']);
}

function approveSubmission(){
    requireAdmin();
    if(isset($_GET['submission_id'])){
        approve($_GET['submission_id']);
        showMessageOnNextPage('قبلت اللفظة');
        header('Location: /review-entries');
        exit;
    }
    else{
        throw new PageNotFoundException();
    }
}

function deleteSubmission(){
    requireAdmin();
    if(isset($_GET['submission_id'])){
        deletePending($_GET['submission_id']);
        showMessageOnNextPage('حذفت اللفظة');
        header('Location: /review-entries');
        exit;
    }
    else{
        throw new PageNotFoundException();
    }

}