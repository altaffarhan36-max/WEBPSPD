-- Jalankan satu kali pada certification_db yang sudah dibuat dari schema lama.
USE certification_db;

-- Tambahkan join_date bila belum tersedia.
SELECT COUNT(*) INTO @has_join_date
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mechanics' AND COLUMN_NAME = 'join_date';
SET @migration_sql = IF(@has_join_date = 0,
  'ALTER TABLE mechanics ADD COLUMN join_date DATE NULL AFTER technician_name',
  'SELECT 1');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

-- Konversi masa golongan lama menjadi tanggal join perkiraan; tanggal inilah sumber tenure selanjutnya.
UPDATE mechanics AS m
JOIN certification_assessments AS a ON a.mechanic_id = m.id
SET m.join_date = DATE_SUB(CURDATE(), INTERVAL ROUND(a.group_years * 365.2425) DAY)
WHERE m.join_date IS NULL;
UPDATE mechanics SET join_date = CURDATE() WHERE join_date IS NULL;
ALTER TABLE mechanics MODIFY join_date DATE NOT NULL;

-- Hapus kolom yang sekarang dihitung dari join_date atau dari status kriteria.
SELECT COUNT(*) INTO @legacy_columns
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_assessments'
  AND COLUMN_NAME IN ('group_years', 'grade_level', 'status_group', 'final_result');
SET @migration_sql = IF(@legacy_columns = 4,
  'ALTER TABLE certification_assessments DROP COLUMN group_years, DROP COLUMN grade_level, DROP COLUMN status_group, DROP COLUMN final_result',
  'SELECT 1');
PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
