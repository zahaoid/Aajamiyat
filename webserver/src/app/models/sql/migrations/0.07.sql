create or replace view aggregated_forms AS (
        SELECT 
            submission_id,
            GROUP_CONCAT(form SEPARATOR '|') AS forms
        FROM 
            entry_forms
        GROUP BY 
            submission_id
    );