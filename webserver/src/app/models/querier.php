<?php

$cache = array();
const AGGREGATED_COLUMNS = array('meanings', 'forms','sources', 'examples', 'categories');


function getReferences(){
    $sources = array();
    $connection = connect();
    $references_query = 'select source from entry_sources';
    $result = mysqli_query($connection, $references_query);
    while ($row = mysqli_fetch_assoc($result)){
        $sources[] = $row['source'];
    }
    return $sources;
}

function getCategories(){
    $categories = array();
    $connection = connect();
    $categories_query = 'select category from entry_categories';
    $result = mysqli_query($connection, $categories_query);
    while ($row = mysqli_fetch_assoc($result)){
        $categories[] = $row['category'];
    }
    return $categories;
}

function getOrigins(){
    $origins = array();
    $connection = connect();
    $origins_query = 'select origin from entries';
    $result = mysqli_query($connection, $origins_query);
    while ($row = mysqli_fetch_assoc($result)){
        $origins[] = $row['origin'];
    }
    return $origins;
}  

function fetchAllLatestApproved (){
    
    $connection = connect();
    $query = "
    SELECT 
        e.*,
        f.forms,
        c.categories,
        ex.examples,
        m.meanings,
        s.sources
    FROM 
        (approved_ranked e
    LEFT JOIN aggregated_forms f ON e.submission_id = f.submission_id
    LEFT JOIN aggregated_categories c ON e.submission_id = c.submission_id
    LEFT JOIN aggregated_examples ex ON e.submission_id = ex.submission_id
    LEFT JOIN aggregated_meanings m ON e.submission_id = m.submission_id
    LEFT JOIN aggregated_sources s ON e.submission_id = s.submission_id) where version_rank = 1
    ;";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_execute($stmt );
    $result = mysqli_stmt_get_result($stmt);
    
    return extractEntriesFromResult($result)[''];
}

function fetchAllPending(){
    $connection = connect();
    $query = "
    SELECT 
        pa.a_submission_id,
        pa.a_entry_id,
        pa.a_origin,
        pa.a_original,
        fa.forms as a_forms,
        ca.categories as a_categories,
        xa.examples as a_examples,
        ma.meanings as a_meanings,
        sa.sources as a_sources,
        
        pa.p_submission_id,
        pa.p_entry_id,
        pa.p_origin,
        pa.p_original,
        fp.forms as p_forms,
        cp.categories as p_categories,
        xp.examples as p_examples,
        mp.meanings as p_meanings,
        sp.sources as p_sources
        
    FROM 
        (pending_approved pa
    LEFT JOIN aggregated_forms fa ON pa.a_submission_id = fa.submission_id
    LEFT JOIN aggregated_categories ca ON pa.a_submission_id = ca.submission_id
    LEFT JOIN aggregated_examples xa ON pa.a_submission_id = xa.submission_id
    LEFT JOIN aggregated_meanings ma ON pa.a_submission_id = ma.submission_id
    LEFT JOIN aggregated_sources sa ON pa.a_submission_id = sa.submission_id

    LEFT JOIN aggregated_forms fp ON pa.p_submission_id = fp.submission_id
    LEFT JOIN aggregated_categories cp ON pa.p_submission_id = cp.submission_id
    LEFT JOIN aggregated_examples xp ON pa.p_submission_id = xp.submission_id
    LEFT JOIN aggregated_meanings mp ON pa.p_submission_id = mp.submission_id
    LEFT JOIN aggregated_sources sp ON pa.p_submission_id = sp.submission_id)
    ;";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_execute($stmt );
    $result = mysqli_stmt_get_result($stmt);
    
    return extractEntriesFromResult($result, array('a_', 'p_'));
}

function fetchLatestApproved ($entryId){
    
    $connection = connect();
    $query = "
    SELECT 
        e.*,
        f.forms,
        c.categories,
        ex.examples,
        m.meanings,
        s.sources
    FROM 
        (approved_ranked e
    LEFT JOIN aggregated_forms f ON e.submission_id = f.submission_id
    LEFT JOIN aggregated_categories c ON e.submission_id = c.submission_id
    LEFT JOIN aggregated_examples ex ON e.submission_id = ex.submission_id
    LEFT JOIN aggregated_meanings m ON e.submission_id = m.submission_id
    LEFT JOIN aggregated_sources s ON e.submission_id = s.submission_id) where version_rank = 1 and entry_id = ?
    ;";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "s", $entryId);
    mysqli_execute($stmt );
    $result = mysqli_stmt_get_result($stmt);
    
    return extractEntriesFromResult($result)[''][0];
}

function extractEntriesFromResult($result, $prefixes = array('')){
    $entries = array();

    while($row = mysqli_fetch_assoc($result)){
        $groups = extractEntriesFromRow($row, $prefixes);
        foreach($groups as $key=>$value){
            $entries[$key][] = $value;
        }
    }
    return $entries;
}

function extractEntriesFromRow($row, $prefixes){
    $entryGroups = array();
    foreach($prefixes as $prefix){
        $entry = array();
        foreach($row as $key => $value){
            if (str_starts_with($key, $prefix)){
                $normalizedColumnName = substr($key, strlen($prefix));
                foreach(AGGREGATED_COLUMNS as $aggregatedColumn){
                    if($normalizedColumnName == $aggregatedColumn){
                        if($value != null) $value = explode('|', $value);
                    }
                }
                $entry[$normalizedColumnName] = $value;
            }
        }
        $entryGroups[$prefix] = $entry;
    }
    return $entryGroups;

}

function approve($submissionId){
    $connection = connect();
    $query = 'update entries set approved at = current_timestamp() where submission_id = ?;';
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt,'i', $submissionId );
    mysqli_stmt_execute($stmt);

}

function submitNewEntry($entryData){
    $connection = connect();
    $entryId = $entryData["id"]?? $entryData['forms'][0];
    mysqli_begin_transaction($connection);
    try{
        $submissionId = insertEntry($connection, $entryId, $entryData['origin'], $entryData['original']);
        insertForms($connection, $submissionId, $entryData['forms']);
        if(isset($entryData['meanings']))
            insertMeanings($connection, $submissionId, $entryData['meanings']);
        if(isset($entryData['examples']))
            insertExamples($connection, $submissionId, $entryData['examples']);
        if(isset($entryData['categories']))
            insertCategories($connection, $submissionId, $entryData['categories']);
        if(isset($entryData['sources']))
            insertSources($connection, $submissionId, $entryData['sources']);
        mysqli_commit($connection);
    }
    catch(Exception $e){
        mysqli_rollback($connection);
        throw $e;
    }
    return $entryId;
}

function insertEntry(mysqli $connection, string $entryId, string $origin, string $original){
    static $entryQuery = 'insert into entries (entry_id, origin, original) values (?, ?, ?);';
    $stmt = mysqli_prepare($connection, $entryQuery);
    $stmt->bind_param('sss', $entryId, $origin, $original) ;
    $stmt->execute();
    $submissionId = $connection->insert_id;
    static $IdQuery = 'update entries set entry_id = ? where submission_id = ?;';
    $stmt = mysqli_prepare($connection, $IdQuery);
    $stmt->bind_param('si', $entryId, $submissionId) ;
    $stmt->execute();
    return $submissionId;
}

function insertForms(mysqli $connection, int $submissionId, array $forms){
    static $forms_query = 'insert into entry_forms(submission_id, form) values (?, ?);';
    $stmt = mysqli_prepare($connection, $forms_query);
    $stmt->bind_param('is', $submissionId, $form) ;
    foreach ($forms as $form){
        if (trim($form) == '') continue;
        $stmt->execute();
    }
}

function insertMeanings(mysqli $connection, int $submissionId, array $meanings){
    static $meanings_query = 'insert into entry_meanings(submission_id, meaning) values(?, ?);';
    $stmt = mysqli_prepare($connection, $meanings_query);
    $stmt->bind_param('is', $submissionId, $meaning) ;
    foreach ($meanings as $meaning){
        if (trim($meaning) == '') continue;
        $stmt->execute();
    }
}

function insertSources(mysqli $connection, int $submissionId, array $sources){
    static $sources_query = 'insert into entry_sources(submission_id, source) values(?, ?)';
    $stmt = mysqli_prepare($connection, $sources_query);
    $stmt->bind_param('is', $submissionId, $source) ;
    foreach ($sources as $source){
        if (trim($source) == '') continue;
        $stmt->execute();
    }
}

function insertCategories(mysqli $connection, int $submissionId, array $categories){
    static $categories_query = 'insert into entry_categories(submission_id, category) values(?, ?)';
    $stmt = mysqli_prepare($connection, $categories_query);
    $stmt->bind_param('is', $submissionId, $category) ;
    foreach ($categories as $category){
        if (trim($category) == '') continue;
        $stmt->execute();
    }
}

function insertExamples(mysqli $connection, int $submissionId, array $examples){
    static $examples_query = 'insert into entry_examples(submission_id, example) values(?, ?);';
    $stmt = mysqli_prepare($connection, $examples_query);
    $stmt->bind_param('is', $submissionId, $example) ;
    foreach ($examples as $example){
        if (trim($example) == '') continue;
        $stmt->execute();
    }
}