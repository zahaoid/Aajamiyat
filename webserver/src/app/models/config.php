<?php

$config = [
    'sqlHostAddress' => getenv("MYSQL_HOST_ADDRESS"),
    'superUsername' => 'root',
    'superPassword' => getenv("MYSQL_ROOT_PASSWORD"),
    'webServerUsername' => getenv("MYSQL_USER"),
    'webServerPassword' => getenv("MYSQL_PASSWORD"),
    'dbName' => getenv("MYSQL_DATABASE"),
    'sqlFolderPath' => __DIR__ . '/sql',
    'migrationsFolderPath' => DIRECTORY_SEPARATOR . __DIR__ . '/sql/migrations',
    'versioningTableName' => 'version_control'
];