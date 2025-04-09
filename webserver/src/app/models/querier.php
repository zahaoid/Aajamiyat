

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
    try{
        mysqli_begin_transaction($connection);
        insertForms($connection, $entryId, $entryData['forms']);
        if(isset($entryData['meanings']))
            insertMeanings($connection, $entryId, $entryData['meanings']);
        if(isset($entryData['examples']))
            insertExamples($connection, $entryId, $entryData['examples']);
        if(isset($entryData['categories']))
            insertCategories($connection, $entryId, $entryData['categoies']);
        if(isset($entryData['sources']))
            insertSources($connection, $entryId, $entryData['sources']);
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
    $form = '';
    $stmt->bind_param('is', $entry_id, $form) ;
    foreach ($forms as $form){
        $stmt->execute();
    }
}

function insertMeanings(mysqli $connection, int $entryId, array $meanings){
    static $meanings_query = 'insert into entry_meanings(entry_id, meaning) values(?, ?);';
    $stmt = mysqli_prepare($connection, $meanings_query);
    $meaning = '';
    $stmt->bind_param('is', $entry_id, $meaning) ;
    foreach ($meanings as $meaning){
        $stmt->execute();
    }
}

function insertSources(mysqli $connection, int $entryId, array $sources){
    static $sources_query = 'insert into entry_sources(entry_id, source) values(?, ?)';
    $stmt = mysqli_prepare($connection, $sources_query);
    $source = '';
    $stmt->bind_param('is', $entry_id, $source) ;
    foreach ($sources as $source){
        $stmt->execute();
    }
}

function insertCategories(mysqli $connection, int $entryId, array $categories){
    static $categories_query = 'insert into entry_categories(entry_id, category) values(?, ?)';
    $stmt = mysqli_prepare($connection, $categories_query);
    $category = '';
    $stmt->bind_param('is', $entry_id, $category) ;
    foreach ($categories as $category){
        $stmt->execute();
    }
}

function insertExamples(mysqli $connection, int $entryId, array $examples){
    static $examples_query = 'insert into entry_examples(entry_id, example) values(?, ?);';
    $stmt = mysqli_prepare($connection, $examples_query);
    $example = '';
    $stmt->bind_param('is', $entry_id, $example) ;
    foreach ($examples as $example){
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
    if (!isset($data['origin'], $data['original'], $data['forms']))
    throw new InvalidArgumentException('Data is not in the expected format');
}

// function updateEntry(mysqli $connection, int $entryId){
//     validateData($entryData);
    
    
//     $connection = connect();




// }