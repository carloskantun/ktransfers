ALTER TABLE providers
  ADD COLUMN contact_name VARCHAR(190) NULL AFTER name;

UPDATE providers
SET contact_name = name
WHERE contact_name IS NULL OR contact_name = '';