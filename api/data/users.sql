CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    blu_id TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at INTEGER NOT NULL
);

-- Pre-loaded test users with ID numbers, Blu IDs, and passwords
INSERT OR IGNORE INTO users (id, blu_id, password, created_at) VALUES (0000001, 'BluBot1', 'BluBot1SaidHi!', 1718000000);
INSERT OR IGNORE INTO users (id, blu_id, password, created_at) VALUES (0000002, 'BluBot2', 'BluBot2SaidHi!', 1718000500);
INSERT OR IGNORE INTO users (id, blu_id, password, created_at) VALUES (0000003, 'Blade_Peisker', 'Waffles&Rocky13', 1718001000);