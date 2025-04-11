CREATE TABLE entry_examples (
    submission_id INT  not null,
    example VARCHAR(255) not null,
    FOREIGN KEY (submission_id) REFERENCES entries(submission_id) on delete cascade
);
