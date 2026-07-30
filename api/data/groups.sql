CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room TEXT NOT NULL,
    sender TEXT NOT NULL,
    text TEXT NOT NULL,
    timestamp INTEGER NOT NULL
);

-- Pre-loaded test message in the 'general' group
INSERT OR IGNORE INTO messages (id, room, sender, text, timestamp) 
VALUES (1, 'Bob & Liv', 'Bob', 'Welcome to Blu Chat!', 1718001500);