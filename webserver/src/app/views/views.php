<?php


function _homePage() {
    $_main = new _Main(new _Empty());
    $_base = new _Base($_main);
    $_base->render();
}

function _entrySubmission($data){
    $_form = new _EntrySubmissionForm(origins: $data['origins'], categories: $data['categories'], references: $data['references']);
    $_main = new _Main($_form);
    $_base = new _Base($_main);
    $_base->render();

}

function _serverError(){
    $_error = new _ServerError();
    $_base = new _Base($_error);
    $_base->render();
}

function _pageNotFound(){
    $_notFound = new _PageNotFound();
    $_base = new _Base($_notFound);
    $_base->render();
}