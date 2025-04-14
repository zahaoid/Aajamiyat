create or replace view pending_approved as (
    select 
        a.submission_id as a_submission_id, 
        a.entry_id as a_entry_id, 
        a.origin as a_origin, 
        a.original as a_original,

        p.submission_id as p_submission_id, 
        p.entry_id as p_entry_id, 
        p.origin as p_origin, 
        p.original as p_original

    from (
        select * 
        from approved_ranked 
        where version_rank = 1
    ) a
    right join (
        select * 
        from entries 
        where approved_at is null
    ) p
    on p.entry_id = a.entry_id

    where a.submission_id is null 
    or p.submission_id > a.submission_id
);
