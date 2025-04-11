CREATE TABLE entry_sources (
    submission_id INT not null,
    source VARCHAR(255) not null,
    FOREIGN KEY (submission_id) REFERENCES entries(submission_id) on delete cascade
);