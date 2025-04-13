CREATE TABLE entries (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id VARCHAR(255) NOT NULL,
    origin VARCHAR(255) NOT NULL,
    original VARCHAR(255) NOT NULL,
    status enum("approved", "rejected", "pending") NOT NULL default "pending",
    submitted_at timestamp NOT NULL default current_timestamp(),
    reviewed_at timestamp
);