<?php


include("connect.php");

function printVersionInfo($connection, $config, $migrationFiles){
    error_log( "Current database version = " . getDatabaseVersion($connection, $config) . " current webserver version = " . getAppVersion($migrationFiles) . "\n");
}

function getAppVersion($migrationFiles): string{

    return $migrationFiles? parseVersion(end($migrationFiles)) : '0';
}

// Get the current database version
function getDatabaseVersion($connection, $config): string
{
    if (!isVersionControlled($connection, $config)) return 0;
    $query = "SELECT MAX(version) AS currentversion FROM  {$config['versioningTableName']};";
    $result = mysqli_query($connection, $query);
    $row = $result->fetch_assoc();
    
    return $row['currentversion']? $row['currentversion']: '0';
}

// Check if the database is version-controlled
function isVersionControlled($connection, $config)  
{
    $query = "SELECT * FROM information_schema.tables WHERE table_schema = '{$config['dbName']}' AND table_name = '{$config['versioningTableName']}' LIMIT 1";
    $result = mysqli_query($connection, $query);
    return $result->fetch_all();
}

// Create the version control table
function createVersionControlTable($connection, $config)
{
    $query = "CREATE TABLE {$config['versioningTableName'] }(version VARCHAR(10) PRIMARY KEY, dateapplied TIMESTAMP NOT NULL, sql_query TEXT NOT NULL);";
    mysqli_query($connection, $query);
    error_log ("Version control table created successfully.\n");
}

function createWebUser($connection, $config){

    $query = "CREATE USER IF NOT EXISTS '{$config['webServerUsername']}'@'%' IDENTIFIED BY '{$config['webServerPassword']}';
    GRANT SELECT, INSERT, UPDATE ON `{$config['dbName']}`.* TO '{$config['webServerUsername']}'@'%';
    FLUSH PRIVILEGES;";
    mysqli_multi_query( $connection, $query);
    error_log ("Users have been created succesfully.\n");
}

// Nuke the database (drop and recreate it)
function nukeDatabase($connection, $config){
    $drop_query = "DROP DATABASE IF EXISTS {$config['dbName']};";
    $create_query = "CREATE DATABASE {$config['dbName']};";
    mysqli_multi_query($connection, $drop_query . $create_query);
    error_log ("Database nuked successfully.\n");
}

// Initialize the database by checking version control and applying migrations if needed
function initializeDatabase($config){
    $errorFlag = false;
    try{
        $migrationFiles =  loadMigrationFiles($config);
        $connection = connect($config);
        error_log ("Checking compatibilty with the database...\n");
        printVersionInfo($connection, $config, $migrationFiles);
        if (!isVersionControlled($connection, $config)) {
            error_log ("Database is not version controlled, attempting to nuke the database..\n");
            nukeDatabase($connection, $config);
            closeConnection($connection);
            $connection = connect($config); // Reconnect
            createVersionControlTable($connection, $config);
            error_log ("The database has been reset and is now configured properly.\n");
        } else {
            error_log ("Database is already version controlled.\n");
            if(getAppVersion($migrationFiles) < getDatabaseVersion($connection, $config)){
                throw new Exception("This app version is behind the database version!");
            }
        }
        
        // Migrate the database if there are migration scripts
        migrate($connection, $config, $migrationFiles);                  
        createWebUser( $connection, $config);
    }
    catch(Exception $e) {  
        error_log ("A fatal error has occured, attempting to shutdown the web server.. \n");
        error_log("Error: " . $e);
        $errorFlag = true;
    }
    finally{
        closeConnection($connection);
        if ($errorFlag) die(1); //IMPORTANT!!! this exists php with an error code 1 so that apache doesnt run!!!!
    }
}

function loadMigrationFiles($config){

    error_log ("Checking for migration scripts...\n");

    // Get the list of migration files
    $migrationFiles = array_filter(scandir($config['migrationsFolderPath']), function($path){
        return str_ends_with($path, '.sql');
    });

    // Sort the migrations by version
    usort($migrationFiles, function ($a, $b) {
        return version_compare(parseVersion($a) , parseVersion($b) );
    });
    error_log ("available migration script: \n") ;
    print_r($migrationFiles);
    return $migrationFiles;

}



// Apply migrations based on available migration files
function migrate($connection, $config, $migrationFiles)
{
    $databaseVersion = getDatabaseVersion($connection, $config);

    $newMigrations = array_filter($migrationFiles, function ($file) use ($databaseVersion) {
        return version_compare(parseVersion($file) , $databaseVersion) > 0;
    });

    if ($newMigrations) {
        error_log ("new SQL migration scripts found.\n");
        $migrationSuccessful = true;

        foreach ($newMigrations as $file) {
            try {
                applyMigration($connection, $config, $file);
            } catch (Exception $e) {
                $migrationSuccessful = false;
                throw new Exception ("Migration failed for {$file}: " . $e->getMessage() . "\n");
            }
        }

        if ($migrationSuccessful) {
            error_log ("All migrations applied successfully!\n");
        }
    } else {
        error_log ("No new migrations found. Database is at the latest version.\n");
    }
}

function readSqlFile($path){
    return file_get_contents($path);

}
// Apply a specific migration file to the database
function applyMigration($connection, $config, $file){
    $version = parseVersion(filename: $file);
    $migrationSql = readSqlFile($config['migrationsFolderPath'] . DIRECTORY_SEPARATOR . $file);
    error_log ("Applying " . $file . "\n");
    // Apply the migration SQL
    if(mysqli_query($connection, $migrationSql)){

        // Log the migration in the schema_change_log table
        $dateApplied = date('Y-m-d H:i:s');
        $logQuery = "INSERT INTO {$config['versioningTableName']} (version, sql_query, dateapplied) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($connection, $logQuery);
        mysqli_stmt_bind_param($stmt, "sss", $version, $migrationSql, $dateApplied);
        mysqli_stmt_execute($stmt);
        error_log ("Migration version " . $version . " applied successfully\n");
    }
}

// Parse version from the migration file name
function parseVersion($filename): string
{
    // Assuming the filename is of the format 'version.sql' (e.g., '1.sql', '2.sql')
    $version = pathinfo($filename, PATHINFO_FILENAME);
    return $version;
}

// Close the database connection
function closeConnection($connection)
{
    mysqli_close($connection);
}

// Execute the database initialization and migration process
initializeDatabase($config);