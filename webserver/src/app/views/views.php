<?php


function _homePage($entries) {
    $_list = new _EntryList($entries);
    $_main = new _Main($_list);
    $_base = new _Base($_main);
    // echo '<pre>';
    // var_dump($entries);
    // echo '</pre>';
    echo $_base;
}

function _entryView($entry) {
    $_list = new _EntryList($entry);
    $_main = new _Main($_list);
    $_base = new _Base($_main);
    echo $_base;
}

function _entrySubmission($data){
    $_form = new _EntrySubmissionForm(origins: $data['origins'], categories: $data['categories'], references: $data['references']);
    $_main = new _Main($_form);
    $_base = new _Base($_main);
    echo $_base;

}

function _serverError(){
    $_error = new _ServerError();
    $_base = new _Base($_error);
    echo $_base;
}

function _pageNotFound(){
    $_notFound = new _PageNotFound();
    $_base = new _Base($_notFound);
    echo $_base;
}