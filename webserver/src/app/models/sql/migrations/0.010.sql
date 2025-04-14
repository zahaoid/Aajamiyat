create or replace view aggregated_examples AS (
        SELECT 
            submission_id,
            GROUP_CONCAT(example SEPARATOR '|') AS examples
        FROM 
            entry_examples
        GROUP BY 
            submission_id
    );