--for adding the intial table entries.

-- Insert 5 records into the account table (tied to user Id's that have not been created yet)
INSERT OR IGNORE INTO ACCOUNT (accountId, userId, accountType, points) VALUES 
(1, 1, 'Standard', 15000),
(2, 2, 'Gold', 35000),
(3, 3, 'Platinum', 85000),
(4, 4, 'Standard', 12000),
(5, 5, 'Platinum', 120000);

-- Inserting 20 records to giftcard table (some same company with different $ amount)
INSERT OR IGNORE INTO GIFTCARD (cardName, cardType, cardValue, points) VALUES 
('Amazon $10', 'Retail', 10.00, 1000),
('Amazon $25', 'Retail', 25.00, 2500),
('Amazon $50', 'Retail', 50.00, 5000),
('Amazon $100', 'Retail', 100.00, 10000),
('Starbucks $5', 'Dining', 5.00, 600),
('Starbucks $10', 'Dining', 10.00, 1200),
('Starbucks $25', 'Dining', 25.00, 2800),
('Chilis $25', 'Dining', 25.00, 3000),
('Chilis $50', 'Dining', 50.00, 5800),
('Apple $15', 'Electronics', 15.00, 1500),
('Apple $50', 'Electronics', 50.00, 4800),
('Target $25', 'Retail', 25.00, 2600),
('Target $50', 'Retail', 50.00, 5100),
('Walmart $25', 'Retail', 25.00, 2500),
('Walmart $50', 'Retail', 50.00, 5000),
('Nike $15', 'Retail', 15.00, 1600),
('Nike $30', 'Retail', 30.00, 3100),
('Nike $50', 'Retail', 50.00, 5200),
('Best Buy $25', 'Electronics', 25.00, 2700),
('Best Buy $50', 'Electronics', 50.00, 5400);