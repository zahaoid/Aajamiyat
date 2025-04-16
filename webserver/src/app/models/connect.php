<?php

function connect(){
    global $config;
    $maxRetries = 10;
    $retryDelay = 1;

    if (!$config['superUsername'] || !$config['superPassword'] || !$config['dbName']) {
        throw new Exception("Missing environment variables for MySQL connection.");
    }

    $attempt = 0;
    while ($attempt < $maxRetries) {
        try {
            error_log( "Trying to connect to database: ". $config['dbName'] . "\n");
            $connection = mysqli_connect($config['sqlHostAddress'], $config['superUsername'], $config['superPassword'], $config['dbName']);
            if (!$connection) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }
            return $connection;
        } catch (Exception $e) {
            error_log( $e );
            $attempt++;
            $retryDelay *=2;
            if ($attempt < $maxRetries) {
                sleep($retryDelay);
            } else {
                throw new Exception("Connection failed after {$maxRetries} attempts: " . $e);
            }
        }
    }
}