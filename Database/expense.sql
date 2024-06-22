-- Use the created database
USE bill_splitter;
-- Create the expense table
-- CREATE TABLE expense (
--     expense_id INT PRIMARY KEY AUTO_INCREMENT,
--     group_id INT NOT NULL,
--     expense_name VARCHAR(255) NOT NULL,
--     expense_description VARCHAR(255) NOT NULL,
--     expense_amount DECIMAL(10, 2) NOT NULL,
--     expense_category VARCHAR(255) NOT NULL,
--     expense_currency VARCHAR(3) NOT NULL,
--     expense_date DATE NOT NULL,
--     expense_owner VARCHAR(255) NOT NULL,
--     expense_members JSON,
--     expense_per_member DECIMAL(10, 2) NOT NULL,
--     expense_type VARCHAR(255) NOT NULL,
--     FOREIGN KEY (group_id) REFERENCES group_data(group_id)
-- );

CREATE TABLE expense (
    expense_id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    expense_name VARCHAR(255) NOT NULL,
    expense_amount DECIMAL(10, 2) NOT NULL,
    expense_per_member DECIMAL(10, 2) NOT NULL
);
SELECT * FROM expense;
drop table expense;
truncate table expense;
ALTER TABLE expense ADD COLUMN total_expenses DECIMAL(10, 2) DEFAULT 0;