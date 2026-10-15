-- Tambahkan grade untuk mekanik dummy tanpa mengubah mekanik lain.
SET @has_grade_level = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'mechanics'
    AND COLUMN_NAME = 'grade_level'
);
SET @grade_migration_sql = IF(
  @has_grade_level = 0,
  'ALTER TABLE mechanics ADD COLUMN grade_level VARCHAR(8) NULL AFTER technician_name',
  'SELECT 1'
);
PREPARE grade_migration_stmt FROM @grade_migration_sql;
EXECUTE grade_migration_stmt;
DEALLOCATE PREPARE grade_migration_stmt;

UPDATE mechanics
SET grade_level = CASE technician_name
  WHEN 'Teknisi 01' THEN '1a'
  WHEN 'Teknisi 02' THEN '1b'
  WHEN 'Teknisi 03' THEN '1c'
  WHEN 'Teknisi 04' THEN '1d'
  WHEN 'Teknisi 05' THEN '1e'
  WHEN 'Teknisi 06' THEN 'f'
  WHEN 'Teknisi 07' THEN '2a'
  WHEN 'Teknisi 08' THEN '2b'
  WHEN 'Teknisi 09' THEN '2c'
  WHEN 'Teknisi 10' THEN '2d'
END
WHERE technician_name IN (
  'Teknisi 01', 'Teknisi 02', 'Teknisi 03', 'Teknisi 04', 'Teknisi 05',
  'Teknisi 06', 'Teknisi 07', 'Teknisi 08', 'Teknisi 09', 'Teknisi 10'
);
