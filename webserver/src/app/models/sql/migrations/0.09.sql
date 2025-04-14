create or replace view aggregated_meanings AS (
        SELECT 
            submission_id,
            GROUP_CONCAT(meaning SEPARATOR '|') AS meanings
        FROM 
            entry_meanings
        GROUP BY 
            submission_id
    );