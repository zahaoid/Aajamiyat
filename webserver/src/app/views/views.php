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
    $_view = new _EntryView($entry);
    $_main = new _Main($_view);
    $_base = new _Base($_main);
    $GLOBALS['title'] = "أصل ومعنى كلمة " . implode(", ", $entry['forms']) . ' - ' . APP_NAME;

    echo $_base;
}

function _entrySubmission($suggestionLists, $entry = null){
    $_form = new _EntrySubmissionForm(suggestionLists: $suggestionLists, entry: $entry);
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