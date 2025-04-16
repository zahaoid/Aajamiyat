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
            $connection = mysqli_connect($config['sqlHostAddress'], $config['superUsername'], $config['superPassword'], $config['dbName']);
            if (!$connection) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }
            return $connection; // Exit the function if connection is successful
        } catch (Exception $e) {
            $attempt++;
            $retryDelay *=2;
            if ($attempt < $maxRetries) {
                sleep($retryDelay); // Wait for 3 seconds before retrying
            } else {
                throw new Exception("Connection failed after {$maxRetries} attempts: " . $e->getMessage());
            }
        }
    }
}