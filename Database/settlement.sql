-- Use the created database
use bill_splitter;
-- Create the settlement table
CREATE TABLE settlement (
    settlementId INT PRIMARY KEY,
    groupld INT,
    settleTo VARCHAR(255),
    settleFrom VARCHAR(255),
    settleDate DATETIME,
    settleAmount DECIMAL(10, 2)
);

select * from settlement;