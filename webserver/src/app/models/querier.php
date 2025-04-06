

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

function fetchEntry(?int $entry_id = null){
    $data = array();
    $connection = connect();
    $entries_query = sprintf("select * from entries %s ;", isset($entry_id)? "where id = $?" : "" );
    $stmt = mysqli_prepare($connection, $entries_query);
    if(isset($entry_id)) mysqli_stmt_bind_param($stmt, "i", $entry_id);
    mysqli_execute(statement:$stmt );
    $result = mysqli_stmt_get_result($stmt);
    $entries = array();
    while($row = mysqli_fetch_assoc($result)){
        $data["origin"] = $row["origin"];
        $data["original"] = $row["original"];
        $entry_id = $row["id"];
        
        //I guess this is safe now? because we overwrote the the only user-input in $entry_id = $row["id"]; 
        $forms_query = "select form from entry_forms where entry_id = $entry_id;"; 
        $forms = mysqli_fetch_all(mysqli_query($connection, $forms_query), MYSQLI_ASSOC);
        
        $data["forms"] = array_column($forms, "form");
        // echo "<pre>";
        // print_r($forms);
        // echo "</pre>";
        
        
        
        
        $entries[] = $data;
    }

    return $entries;

}

function insertEntry($data){

    if(isset($data['origin'], $data['original'], $data['forms'])){

        $connection = connect();

        $entries_query = 'insert into entries (origin, original) values (?, ?);';
        $forms_query = 'insert into entry_forms(entry_id, form) values (?, ?);';
        
        $meanings_query = 'insert into entry_meanings(entry_id, meaning) values(?, ?);';
        $examples_query = 'insert into entry_examples(entry_id, example) values(?, ?);';
        $categories_query = 'insert into entry_categories(entry_id, category) values(?, ?)';
        $sources_query = 'insert into entry_sources(entry_id, source) values(?, ?)';

        try{
            mysqli_begin_transaction($connection);
            
            $stmt = mysqli_prepare($connection, $entries_query);
            $stmt->bind_param('ss', $data['origin'], $data['original']) ;
            $stmt->execute();

            $entry_id = $connection->insert_id;

            $stmt = mysqli_prepare($connection, $forms_query);
            $form = '';
            $stmt->bind_param('is', $entry_id, $form) ;
            foreach ($data['forms'] as $form){
                $stmt->execute();
            }

            if(isset($data['meanings'])){
                $stmt = mysqli_prepare($connection, $meanings_query);
                $meaning = '';
                $stmt->bind_param('is', $entry_id, $meaning) ;
                foreach ($data['meanings'] as $meaning){
                    $stmt->execute();
                }
            }

            if(isset($data['examples'])){
                $stmt = mysqli_prepare($connection, $examples_query);
                $example = '';
                $stmt->bind_param('is', $entry_id, $example) ;
                foreach ($data['examples'] as $example){
                    $stmt->execute();
                }
            }

            if(isset($data['categories'])){
                $stmt = mysqli_prepare($connection, $categories_query);
                $category = '';
                $stmt->bind_param('is', $entry_id, $category) ;
                foreach ($data['categories'] as $category){
                    $stmt->execute();
                }
            }

            if(isset($data['sources'])){
                $stmt = mysqli_prepare($connection, $sources_query);
                $source = '';
                $stmt->bind_param('is', $entry_id, $source) ;
                foreach ($data['sources'] as $source){
                    $stmt->execute();
                }
            }

            mysqli_commit($connection);
        }
        catch(Exception $e){
            mysqli_rollback($connection);
            throw $e;
        }

    }
    else{
        throw new InvalidArgumentException('Data is not in the expected format');
    }
}