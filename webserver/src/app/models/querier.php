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

function fetchEntries(?int $entry_id = null){
    
    $data = array();
    $connection = connect();
    $baseQuery = "select 
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
            entry_id,
            group_concat(form SEPARATOR '|') as forms
        from entry_forms
        group by entry_id
    ) forms on entries.id = forms.entry_id
    left join (
        select 
            entry_id,
            group_concat(category SEPARATOR '|') as categories
        from entry_categories
        group by entry_id
    ) categories on entries.id = categories.entry_id
    left join (
        select 
            entry_id,
            group_concat(example SEPARATOR '|') as examples
        from entry_examples
        group by entry_id
    ) examples on entries.id = examples.entry_id
    left join (
        select 
            entry_id,
            group_concat(meaning SEPARATOR '|') as meanings
        from entry_meanings
        group by entry_id
    ) meanings on entries.id = meanings.entry_id
    left join (
        select 
            entry_id,
            group_concat(source SEPARATOR '|') as sources
        from entry_sources
        group by entry_id
    ) sources on entries.id = sources.entry_id
    %s
    ;";
    $baseQuery = sprintf($baseQuery, isset($entry_id)? "where entries.id = ?" : "" );
    $stmt = mysqli_prepare($connection, $baseQuery);
    if(isset($entry_id)) mysqli_stmt_bind_param($stmt, "i", $entry_id);
    mysqli_execute(statement:$stmt );
    $result = mysqli_stmt_get_result($stmt);
    $entries = array();
    $headers = array('meanings', 'forms','sources', 'examples', 'categories');
    while($row = mysqli_fetch_assoc($result)){
        foreach($headers as $header) if($row[$header]) $row[$header] = explode('|', $row[$header]);
        $entries[] = $row;
    }

    return $entries;

}

function submitNewEntry($entryData){
    validateData($entryData);
    $connection = connect();
    $entryId = insertEntry($connection, $entryData['origin'], $entryData['original']);
    mysqli_begin_transaction($connection);
    try{
        insertForms($connection, $entryId, $entryData['forms']);
        if(isset($entryData['meanings']))
            insertMeanings($connection, $entryId, $entryData['meanings']);
        if(isset($entryData['examples']))
            insertExamples($connection, $entryId, $entryData['examples']);
        if(isset($entryData['categories']))
            insertCategories($connection, $entryId, $entryData['categories']);
        if(isset($entryData['sources']))
            insertSources($connection, $entryId, $entryData['sources']);
        mysqli_commit($connection);
    }
    catch(Exception $e){
        mysqli_rollback($connection);
        throw $e;
    }
    return $entryId;
}

function editEntry($entryData){
    validateData($entryData);
    $entryId = $entryData['id'];
    $connection = connect();
    mysqli_begin_transaction($connection);
    try{
        updateEntry($connection, $entryId, $entryData['origin'], $entryData['original']);
        deleteForms($connection, $entryId);
        insertForms($connection, $entryId, $entryData['forms']);
        deleteMeanings( $connection, $entryId);
        if(isset($entryData['meanings']))
            insertMeanings( $connection, $entryId, $entryData['meanings']);
        deleteExamples( $connection, $entryId);
        if(isset($entryData['examples']))
            insertExamples($connection, $entryId, $entryData['examples']);
        deleteCategories( $connection, $entryId);
        if(isset($entryData['categories']))
            insertCategories( $connection, $entryId, $entryData['categories']);
        deleteSources( $connection, $entryId);
        if(isset($entryData['sources']))
            insertSources( $connection, $entryId, $entryData['sources']);
        mysqli_commit($connection);
    }
    catch(Exception $e){
        mysqli_rollback($connection);
        throw $e;
    }
}

function insertEntry(mysqli $connection, string $origin, string $original){
    static $entries_query = 'insert into entries (origin, original) values (?, ?);';
    $stmt = mysqli_prepare($connection, $entries_query);
    $stmt->bind_param('ss', $origin, $original) ;
    $stmt->execute();
    return $connection->insert_id;
}

function insertForms(mysqli $connection, int $entryId, array $forms){
    static $forms_query = 'insert into entry_forms(entry_id, form) values (?, ?);';
    $stmt = mysqli_prepare($connection, $forms_query);
    $stmt->bind_param('is', $entryId, $form) ;
    foreach ($forms as $form){
        if (trim($form) == '') continue;
        $stmt->execute();
    }
}

function insertMeanings(mysqli $connection, int $entryId, array $meanings){
    static $meanings_query = 'insert into entry_meanings(entry_id, meaning) values(?, ?);';
    $stmt = mysqli_prepare($connection, $meanings_query);
    $stmt->bind_param('is', $entryId, $meaning) ;
    foreach ($meanings as $meaning){
        if (trim($meaning) == '') continue;
        $stmt->execute();
    }
}

function insertSources(mysqli $connection, int $entryId, array $sources){
    static $sources_query = 'insert into entry_sources(entry_id, source) values(?, ?)';
    $stmt = mysqli_prepare($connection, $sources_query);
    $stmt->bind_param('is', $entryId, $source) ;
    foreach ($sources as $source){
        if (trim($source) == '') continue;
        $stmt->execute();
    }
}

function insertCategories(mysqli $connection, int $entryId, array $categories){
    static $categories_query = 'insert into entry_categories(entry_id, category) values(?, ?)';
    $stmt = mysqli_prepare($connection, $categories_query);
    $stmt->bind_param('is', $entryId, $category) ;
    foreach ($categories as $category){
        if (trim($category) == '') continue;
        $stmt->execute();
    }
}

function insertExamples(mysqli $connection, int $entryId, array $examples){
    static $examples_query = 'insert into entry_examples(entry_id, example) values(?, ?);';
    $stmt = mysqli_prepare($connection, $examples_query);
    $stmt->bind_param('is', $entryId, $example) ;
    foreach ($examples as $example){
        if (trim($example) == '') continue;
        $stmt->execute();
    }
}

function deleteForms(mysqli $connection, int $entryId){
    static $query = 'delete from entry_forms where entry_id = ?;';
    $stmt = mysqli_prepare($connection, $query);
    $stmt->bind_param('i', $entryId) ;
    $stmt->execute();
}

function deleteCategories(mysqli $connection, int $entryId){
    static $query = 'delete from entry_categories where entry_id = ?;';
    $stmt = mysqli_prepare($connection, $query);
    $stmt->bind_param('i', $entryId) ;
    $stmt->execute();
}

function deleteExamples(mysqli $connection, int $entryId){
    static $query = 'delete from entry_examples where entry_id = ?;';
    $stmt = mysqli_prepare($connection, $query);
    $stmt->bind_param('i', $entryId) ;
    $stmt->execute();
}

function deleteMeanings(mysqli $connection, int $entryId){
    static $query = 'delete from entry_meanings where entry_id = ?;';
    $stmt = mysqli_prepare($connection, $query);
    $stmt->bind_param('i', $entryId) ;
    $stmt->execute();
}

function deleteSources(mysqli $connection, int $entryId){
    static $query = 'delete from entry_sources where entry_id = ?;';
    $stmt = mysqli_prepare($connection, $query);
    $stmt->bind_param('i', $entryId) ;
    $stmt->execute();
}

function validateData($entryData){
    if (!isset($entryData['origin'], $entryData['original'], $entryData['forms']))
    throw new InvalidArgumentException('Data is not in the expected format: ' . print_r($entryData));
}

function updateEntry(mysqli $connection, int $entryId, string $origin, string $original){
    
    static $query = 'update entries set origin = ?, original = ? where id = ?;';
    $stmt = mysqli_prepare($connection, $query);
    $stmt->bind_param('ssi', $origin, $original, $entryId) ;
    $stmt->execute();

}