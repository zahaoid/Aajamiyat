<?php

$cache = array();

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

function fetchEntries(?string $entryId = null, ?string $status = null, ?bool $latest = false){
    
    $connection = connect();
    $query = "select 
        entries.*, 
        forms.forms,
        categories.categories,
        examples.examples,
        meanings.meanings,
        sources.sources
    from 
        entries
    left join (
        select 
            submission_id,
            group_concat(form SEPARATOR '|') as forms
        from entry_forms
        group by submission_id
    ) forms on entries.submission_id = forms.submission_id
    left join (
        select 
            submission_id,
            group_concat(category SEPARATOR '|') as categories
        from entry_categories
        group by submission_id
    ) categories on entries.submission_id = categories.submission_id
    left join (
        select 
            submission_id,
            group_concat(example SEPARATOR '|') as examples
        from entry_examples
        group by submission_id
    ) examples on entries.submission_id = examples.submission_id
    left join (
        select 
            submission_id,
            group_concat(meaning SEPARATOR '|') as meanings
        from entry_meanings
        group by submission_id
    ) meanings on entries.submission_id = meanings.submission_id
    left join (
        select 
            submission_id,
            group_concat(source SEPARATOR '|') as sources
        from entry_sources
        group by submission_id
    ) sources on entries.submission_id = sources.submission_id
    %s
    ORDER BY entry_id
    ;";
    $conditions = array();
    if(isset($status)) $conditions[]= "status = ?";
    if(isset($entryId)) $conditions[]= "entry_id = ?";
    $clauses = array();
    if (!empty($conditions)) $clauses[] = "where";
    $clauses[] = implode(' and ', $conditions);
    $details = implode(" ", $clauses);
    $query = sprintf($query, $details );
    $stmt = mysqli_prepare($connection, $query);
    if(isset($entryId, $status)) mysqli_stmt_bind_param($stmt, "ss", $entryId, $status);
    else if(isset($entryId)) mysqli_stmt_bind_param($stmt, "s", $entryId);
    else if(isset($status)) mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_execute($stmt );
    $result = mysqli_stmt_get_result($stmt);
    $entries = array();
    $headers = array('meanings', 'forms','sources', 'examples', 'categories');
    $lastEntryId = null;
    $sameDefinitionGroup = array();
    while($row = mysqli_fetch_assoc($result)){
        foreach($headers as $header) if($row[$header]) $row[$header] = explode('|', $row[$header]);
        $entryId = $row['entry_id'];
        if($entryId != $lastEntryId){
            $sameDefinitionGroup = array();
        }
        else{
            $sameDefinitionGroup[] = $row;
        }
        $entries[] = $row;
    }

    return $entries;

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