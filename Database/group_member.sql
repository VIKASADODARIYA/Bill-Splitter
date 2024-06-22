-- Use the created database
use bill_splitter;
-- Create the group_member table
CREATE TABLE group_member (
	member_id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT,
    member_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (group_id) REFERENCES group_data(group_id) ON DELETE CASCADE,
    member_email varchar(255)
);

select * from group_member;