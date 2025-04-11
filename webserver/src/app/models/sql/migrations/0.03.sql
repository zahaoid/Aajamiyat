CREATE TABLE entry_meanings (
    submission_id INT not null,
    meaning VARCHAR(255) not null,
    FOREIGN KEY (submission_id) REFERENCES entries(submission_id) on delete cascade
);
