CREATE TABLE entry_meanings (
    entry_id INT,
    meaning VARCHAR(255),
    FOREIGN KEY (entry_id) REFERENCES entries(id) on delete cascade
);
