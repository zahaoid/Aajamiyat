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
        if($data["id"] == null){
            $newEntryId = submitNewEntry( $data);
            showMessageOnNextPage("رُصِدَت اللفظة");
            header("Location:/view-entry?id=".$newEntryId);
        }
        else{
            editEntry($data);
            showMessageOnNextPage("عُدِّلَت اللفظة");
            header("Location:/view-entry?id=".$data['id']);
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
    $entries = fetchEntries();
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