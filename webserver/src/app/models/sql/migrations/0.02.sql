CREATE TABLE entry_forms (
    submission_id INT not null,
    form VARCHAR(255) not null,
    FOREIGN KEY (submission_id) REFERENCES entries(submission_id) on delete cascade
);
