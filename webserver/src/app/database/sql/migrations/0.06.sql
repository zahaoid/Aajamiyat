CREATE TABLE entry_categories (
    entry_id INT,
    category VARCHAR(255),
    FOREIGN KEY (entry_id) REFERENCES entries(id),
    FOREIGN KEY (category) REFERENCES categories(category)
);
