<?php


require_once("connect.php");
require_once ('config.php');


function getAppVersionAsString($migrations): string{

    return $migrations? versionToString(end($migrations)['version']): 0;
}

// Get the current database version
function getDatabaseVersionAsString($connection, $config): string
{
    if (!isVersionControlled($connection, $config)) return 0;
    $query = "SELECT major, minor, patch FROM  {$config['versioningTableName']} order by major desc, minor desc, patch desc limit 1;";
    $result = mysqli_query($connection, $query);
    $row = $result->fetch_assoc();
    
    return $row? versionToString($row) : 0;
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
    $query = "CREATE TABLE {$config['versioningTableName'] } (
    major TINYINT UNSIGNED NOT NULL,
    minor TINYINT UNSIGNED NOT NULL,
    patch TINYINT UNSIGNED NOT NULL,
    dateapplied TIMESTAMP NOT NULL Default current_timestamp(),
    applied_sql TEXT NOT NULL,
    PRIMARY KEY (major, minor, patch)
    );";
    mysqli_query($connection, $query);
    echo ("Version control table created successfully.\n");
}

function createWebUser($connection, $config){

    $query = "CREATE USER IF NOT EXISTS '{$config['webServerUsername']}'@'%' IDENTIFIED BY '{$config['webServerPassword']}';
    GRANT SELECT, INSERT, UPDATE ON `{$config['dbName']}`.* TO '{$config['webServerUsername']}'@'%';
    FLUSH PRIVILEGES;";
    mysqli_multi_query( $connection, $query);
    echo ("Users have been created succesfully.\n");
}

// Nuke the database (drop and recreate it)
function nukeDatabase($connection, $config){
    $drop_query = "DROP DATABASE IF EXISTS {$config['dbName']};";
    $create_query = "CREATE DATABASE {$config['dbName']};";
    mysqli_multi_query($connection, $drop_query . $create_query);
    echo ("Database nuked successfully.\n");
}

// Initialize the database by checking version control and applying migrations if needed
function initializeDatabase($config){
    $errorFlag = false;
    try{
        $migrations =  loadMigrations($config);
        $connection = connect();
        $databaseVersion = getDatabaseVersionAsString($connection, $config);
        $appVersion = getAppVersionAsString($migrations);
        echo ("Checking compatibilty with the database...\n");
        echo( "Current database version = " . $databaseVersion . " current webserver version = " . $appVersion . "\n");
        if (!isVersionControlled($connection, $config)) {
            echo ("Database is not version controlled, attempting to nuke the database..\n");
            nukeDatabase($connection, $config);
            closeConnection($connection);
            $connection = connect(); // Reconnect
            createVersionControlTable($connection, $config);
            echo ("The database has been reset and is now configured properly.\n");
        } else {
            echo ("Database is already version controlled.\n");
            if(version_compare($appVersion , $databaseVersion, '<')){
                throw new Exception("This app version is behind the database version!");
            }
        }
        
        // Migrate the database if there are migration scripts
        $newMigrations = array_filter($migrations, function ($migration) use ($databaseVersion) {
            return version_compare(versionToString($migration['version']) , $databaseVersion, '>');
        });

        migrate($connection, $config, $newMigrations);                  
        createWebUser( $connection, $config);
    }
    catch(Exception $e) {  
        echo ("A fatal error has occured, attempting to shutdown the web server.. \n");
        echo("Error: " . $e);
        $errorFlag = true;
    }
    finally{
        closeConnection($connection);
        if ($errorFlag) die(1); //IMPORTANT!!! this exists php with an error code 1 so that apache doesnt run!!!!
    }
}

function loadMigrations($config){

    echo ("Checking for migration scripts...\n");

    // Get the list of migration files
    $migrationFileNames = array_filter(scandir($config['migrationsFolderPath']), function($path){
        return str_ends_with($path, '.sql');
    });

    $migrations = array();

    // load files content into an assocsiative array
    foreach($migrationFileNames as $migrationFileName){
         $version = parseVersionFromFilename($migrationFileName);
         $migrationSql = readSqlFile($config['migrationsFolderPath'] . DIRECTORY_SEPARATOR . $migrationFileName);
         $migration = array();
         $migration['version'] = $version;
         $migration['sql'] = $migrationSql;
         $migrations[] = $migration;
    }

    // Sort the migrations by version
    usort($migrations, function ($a, $b) {
        $aVersionString = versionToString($a['version']);
        $bVersionString = versionToString($b['version']);
        return version_compare($aVersionString , $bVersionString );
    });

    echo ("available migration script: \n") ;
    $versions = array_column( $migrations,'version');
    $versions = array_map(function($e){return versionToString($e); }, $versions);
    print_r($versions);
    return $migrations;

}



// Apply migrations based on available migration files
function migrate($connection, $config, $newMigrations)
{

    if ($newMigrations) {
        echo ("new SQL migration scripts found.\n");
        $migrationSuccessful = true;

        foreach ($newMigrations as $newMigration) {
            try {
                applyMigration($connection, $config, $newMigration);
            } catch (Exception $e) {
                $migrationSuccessful = false;
                throw new Exception ("Migration failed for " . versionToString($newMigration['version']) . ": " . $e . "\n");
            }
        }

        if ($migrationSuccessful) {
            echo ("All migrations applied successfully!\n");
        }
    } else {
        echo ("No new migrations found. Database is at the latest version.\n");
    }
}

function readSqlFile($path){
    return file_get_contents($path);

}

// Apply a specific migration file to the database
function applyMigration($connection, $config, $migration){
    $version = $migration['version'];
    $migrationSql = $migration['sql'];
    echo ("Applying migration: " . versionToString($migration['version']) . "\n");
    // Apply the migration SQL
    if(mysqli_query($connection, $migrationSql)){

        // Log the migration in the schema_change_log table
        $logQuery = "INSERT INTO {$config['versioningTableName']} (major, minor, patch, applied_sql) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $logQuery);
        mysqli_stmt_bind_param($stmt, "iiis", $version['major'], $version['minor'], $version['patch'], $migrationSql);
        mysqli_stmt_execute($stmt);
        echo ("Migration " . versionToString($version ) . " applied successfully\n");
    }
}

// Parse version from the migration file name
// Assuming the filename is of the format 'version.sql' (e.g., '1.sql', '2.sql')

function parseVersionFromFilename($filename): array
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $version = explode(".", $name);
    if(count($version) > 3) throw new Exception("Invalid version format: too many segments in file: '$filename'");
    $normalizedVersion = array();
    $normalizedVersion['major'] = (int)($version[0] ?? 0);
    $normalizedVersion['minor'] = (int)($version[1] ?? 0);
    $normalizedVersion['patch'] = (int)($version[2] ?? 0);
    return $normalizedVersion;
}

function versionToString(array $version): string {
    return "{$version['major']}.{$version['minor']}.{$version['patch']}";
}

// Close the database connection
function closeConnection($connection)
{
    mysqli_close($connection);
}

// Execute the database initialization and migration process
initializeDatabase($config);