CREATE TABLE entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin VARCHAR(255),
    original VARCHAR(255),
    FOREIGN KEY (origin) REFERENCES origins(origin)
);
