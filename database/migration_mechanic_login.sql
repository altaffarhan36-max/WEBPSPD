-- Jalankan satu kali setelah database/schema.sql dan migrasi tanggal join.
USE certification_db;

SELECT COUNT(*) INTO @has_role
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role';
SET @migration_sql = IF(@has_role = 0,
  'ALTER TABLE users ADD COLUMN role VARCHAR(16) NOT NULL DEFAULT ''admin'' AFTER password_hash',
  'SELECT 1');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SELECT COUNT(*) INTO @has_mechanic_id
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'mechanic_id';
SET @migration_sql = IF(@has_mechanic_id = 0,
  'ALTER TABLE users ADD COLUMN mechanic_id BIGINT UNSIGNED NULL AFTER role',
  'SELECT 1');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

UPDATE users SET role = 'master' WHERE role IS NULL OR role = '' OR role = 'admin';

SELECT COUNT(*) INTO @has_mechanic_index
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'uq_users_mechanic_id';
SET @migration_sql = IF(@has_mechanic_index = 0,
  'ALTER TABLE users ADD UNIQUE KEY uq_users_mechanic_id (mechanic_id)',
  'SELECT 1');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SELECT COUNT(*) INTO @has_mechanic_fk
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'fk_users_mechanic';
SET @migration_sql = IF(@has_mechanic_fk = 0,
  'ALTER TABLE users ADD CONSTRAINT fk_users_mechanic FOREIGN KEY (mechanic_id) REFERENCES mechanics(id) ON DELETE CASCADE',
  'SELECT 1');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
