<?php


function homePage() {
    $navigation = new Navigation();
    $main = new Main(APP_NAME, $navigation);
    $base = new Base(APP_NAME, $main);
    $base->render();
}