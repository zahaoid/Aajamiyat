<?php


function _homePage() {
    $_main = new _Main(null);
    $_base = new _Base($_main);
    echo $_base;
    echo "<pre>";
    // print_r($forms);
    print_r( fetchEntry() );
    echo "</pre>";
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