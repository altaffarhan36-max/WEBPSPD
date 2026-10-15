USE certification_db;

ALTER TABLE users
  ADD COLUMN gl_name VARCHAR(120) NULL AFTER mechanic_id;

-- Give existing GL accounts a display name based on their login username.
UPDATE users
SET gl_name = username
WHERE role = 'gl' AND (gl_name IS NULL OR gl_name = '');
