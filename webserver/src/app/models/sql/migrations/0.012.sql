create or replace view approved_ranked AS (
    SELECT *, 
           ROW_NUMBER() OVER (
             PARTITION BY entry_id 
             ORDER BY approved_at DESC, submission_id DESC
           ) AS version_rank
    FROM entries
    WHERE approved_at IS NOT NULL
  );