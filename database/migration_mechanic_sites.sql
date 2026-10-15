USE certification_db;

ALTER TABLE mechanics
  ADD COLUMN site VARCHAR(16) NOT NULL DEFAULT 'ADRO' AFTER grade_level;

ALTER TABLE users
  ADD COLUMN gl_site VARCHAR(16) NULL AFTER mechanic_id;

-- Assign the existing ten sample mechanics to their requested sites.
UPDATE mechanics SET site = CASE WHEN id BETWEEN 1 AND 5 THEN 'ADRO' ELSE 'AMM' END
WHERE id BETWEEN 1 AND 10;

-- Existing GL accounts are associated with ADRO; create a separate AMM account
-- from the master dashboard when required.
UPDATE users SET gl_site = 'ADRO' WHERE role = 'gl' AND gl_site IS NULL;
