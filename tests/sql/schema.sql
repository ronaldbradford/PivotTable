CREATE USER pt_user@localhost IDENTIFIED BY 'passwd';
GRANT ALL ON pivot_table.* TO pt_user@localhost;
CREATE SCHEMA IF NOT EXISTS pivot_table;
