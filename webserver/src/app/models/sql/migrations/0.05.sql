CREATE TABLE entry_sources (
    entry_id INT,
    source VARCHAR(255),
    FOREIGN KEY (entry_id) REFERENCES entries(id) on delete cascade
);