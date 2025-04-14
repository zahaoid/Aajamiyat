create or replace view aggregated_sources AS (
        SELECT 
            submission_id,
            GROUP_CONCAT(source SEPARATOR '|') AS sources
        FROM 
            entry_sources
        GROUP BY 
            submission_id
    );