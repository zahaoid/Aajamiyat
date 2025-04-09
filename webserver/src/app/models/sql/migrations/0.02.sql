CREATE TABLE entry_forms (
    entry_id INT,
    form VARCHAR(255),
    FOREIGN KEY (entry_id) REFERENCES entries(id) on delete cascade
);
