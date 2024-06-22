-- Use the created database
use bill_splitter;
-- Create the group_data table
CREATE TABLE group_data (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_name (group_name),
    currency VARCHAR(10) NOT NULL,
	category VARCHAR(50) NOT NULL,
    descriptions VARCHAR(255) NOT NULL
);

select * from group_data;