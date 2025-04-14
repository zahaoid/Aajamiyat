create or replace view aggregated_categories AS (
        SELECT 
            submission_id,
            GROUP_CONCAT(category SEPARATOR '|') AS categories
        FROM 
            entry_categories
        GROUP BY 
            submission_id
    );