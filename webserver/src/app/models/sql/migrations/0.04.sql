CREATE TABLE entry_examples (
    entry_id INT,
    example VARCHAR(255),
    FOREIGN KEY (entry_id) REFERENCES entries(id)
);
