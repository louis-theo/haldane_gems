-- Setting up instructions
-- Name of the database in myphpadmin: auctiondatabase

-- Existing users details:
--       username: user1
--       password: password1
--       predefined userrole: buyer and seller

--       username: user2
--       password: password2
--       predefined userrole: buyer and seller

--       username: user3
--       password: password3
--       predefined userrole: buyer

--       username: user4
--       password: password4
--       predefined userrole: buyer


-- User table
CREATE TABLE Users (
   UserID INT PRIMARY KEY AUTO_INCREMENT,
   Username VARCHAR(255) UNIQUE NOT NULL,
   Password VARCHAR(255) NOT NULL,
   Email VARCHAR(255) UNIQUE NOT NULL,
   Address VARCHAR(255) NOT NULL,
   RegistrationDate DATETIME NOT NULL DEFAULT CURRENT_TIME
);

-- -- Roles - buyer and seller
CREATE TABLE Roles (
   RoleID INT PRIMARY KEY AUTO_INCREMENT,
   RoleName ENUM('Seller', 'Buyer') NOT NULL
);

-- intermediate table User_role
CREATE TABLE UserRole(
   UserID INT,
   RoleID INT,
   PRIMARY KEY (UserID, RoleID),

   FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
   FOREIGN KEY (RoleID) REFERENCES Roles(RoleID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Products table
CREATE TABLE Auction (
   AuctionID INT PRIMARY KEY AUTO_INCREMENT,
   SellerID INT,
   ProductName VARCHAR(255) NOT NULL,
   Description TEXT,
   StartPrice DECIMAL(10, 2) NOT NULL,
   ReservePrice DECIMAL(10, 2) DEFAULT 0,
   StartDate DATETIME NOT NULL DEFAULT CURRENT_TIME,
   EndDate DATETIME NOT NULL,
   CurrentBid DECIMAL(10, 2),
   ProductCondition ENUM('New', 'Used') NOT NULL, 
   Status ENUM('Active', 'Expired', 'Sold') NOT NULL DEFAULT 'Active',
   Watchlist BOOLEAN DEFAULT FALSE,
   FOREIGN KEY (SellerID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Category table - many to many so need intermediate
CREATE TABLE Category (
   CategoryID INT PRIMARY KEY AUTO_INCREMENT,
   CategoryName VARCHAR(50) NOT NULL
);

-- intermadiate table
CREATE TABLE CategoryAuction (
   AucCatID INT PRIMARY KEY AUTO_INCREMENT,
   AuctionID INT,
   CategoryID INT,
   FOREIGN KEY (AuctionID) REFERENCES Auction(AuctionID) ON DELETE CASCADE ON UPDATE CASCADE,
   FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID) ON DELETE CASCADE ON UPDATE CASCADE
);

--  Bids table
CREATE TABLE Bid (
   BidID INT PRIMARY KEY AUTO_INCREMENT,
   BuyerID INT,
   AuctionID INT,
   BidAmount DECIMAL(10, 2) NOT NULL,
   BidTime DATETIME NOT NULL DEFAULT CURRENT_TIME,
   FOREIGN KEY (BuyerID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
   FOREIGN KEY (AuctionID) REFERENCES Auction(AuctionID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Ratings table - buyerId added for extra functionality checks
CREATE TABLE Ratings (
   RatingID INT PRIMARY KEY AUTO_INCREMENT,
   SellerID INT,
   RatingScore INT,
   BuyerID INT,
   FOREIGN KEY (SellerID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
   FOREIGN KEY (BuyerID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Comments table about the seller
CREATE TABLE Comment (
   CommentID INT PRIMARY KEY AUTO_INCREMENT,
   SellerID INT,
   ComentatorID INT,
   CommentText TEXT,
   CommentTime DATETIME DEFAULT CURRENT_TIME,
   FOREIGN KEY (ComentatorID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE.
   FOREIGN KEY (SellerID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE

);

-- Watchlist
CREATE TABLE Watchlist (
   WatchlistID INT PRIMARY KEY AUTO_INCREMENT,
   BuyerID INT,
   AuctionID INT,
   FOREIGN KEY (BuyerID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
   FOREIGN KEY (AuctionID) REFERENCES Auction(AuctionID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Fake data
INSERT INTO Users (Username, Password, Email, Address, RegistrationDate)
VALUES
   ('user1', '$2b$12$96.DXqhxwsw5OhnAAGZf.u.py9qAiJI/BR1.YJ/.ANtr0PUuKRg0a', 'user1@email.com', '123 Main St', NOW()), 
   ('user2', '$2b$12$aJ8ANMvqFYWVIev3zcMMreN3IAFVhhGtNWynP92LuJ5Gg3Y9/1l3W', 'user2@email.com', '789 Oak St', NOW()),
   ('user3', '$2b$12$4Be5oCcGq75YM8XyIBOPEu2g29kJeW9.EnXcRk3WhRyF3Ilh0z.bq', 'user3@email.com', '101 Pine St', NOW()),
   ('user4', '$2b$12$H7/F76CFf/eiItcpIHtvIOs6zzp34YwOmLPHbqb9z.uzNJUjE8OFe', 'user4@email.com', '104 Pine St', NOW());

INSERT INTO Category (CategoryName)
VALUES
   ('Watches'), 
   ('Earrings'), 
   ('Necklaces'), 
   ('Bracelets'), 
   ('Rings'), 
   ('Male'), 
   ('Female'), 
   ('Unisex'), 
   ('Gold'), 
   ('Silver'), 
   ('Bronze'), 
   ('Platinum'), 
   ('Rose Gold'), 
   ('Gemstone'); 


INSERT INTO Auction (ProductName, SellerID, StartDate, EndDate, CurrentBid, Description, ProductCondition, StartPrice, ReservePrice, Watchlist, Status)
VALUES
('Elegant Watch', 1, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 1 DAY, 100, 'Elegant watch with leather strap', 'New', 100, 80, 0, 'Active'),
('Diamond Earrings', 2, NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 200, 'Beautiful diamond earrings', 'New', 200, 150, 0, 'Active'),
('Gold Necklace', 1, NOW(), DATE_ADD(NOW(), INTERVAL 6 DAY), 150, 'Elegant gold necklace', 'New', 150, 120, 0, 'Active'),
('Silver Bracelet', 2, NOW(), DATE_ADD(NOW(), INTERVAL 8 DAY), 75, 'Stylish silver bracelet', 'New', 75, 60, 0, 'Active'),
('Pearl Earrings', 1, NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), 90, 'Classic pearl earrings', 'New', 90, 70, 0, 'Active'),
('Luxury Ring', 2, NOW(), DATE_ADD(NOW(), INTERVAL 9 DAY), 300, 'Luxury diamond ring', 'New', 300, 250, 0, 'Active'),
('Silver Watch', 1, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 80, 'Classic silver watch with stainless steel band', 'New', 80, 65, 0, 'Active'),
('Sapphire Earrings', 2, NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 150, 'Stunning sapphire earrings', 'New', 150, 120, 0, 'Active'),
('Diamond Necklace', 1, NOW(), DATE_ADD(NOW(), INTERVAL 6 DAY), 200, 'Exquisite diamond necklace', 'New', 200, 180, 0, 'Active'),
('Leather Bracelet', 2, NOW(), DATE_ADD(NOW(), INTERVAL 8 DAY), 60, 'Casual leather bracelet', 'New', 60, 50, 0, 'Active'),
('Ruby Ring', 1, NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), 130, 'Beautiful ruby ring', 'New', 130, 110, 0, 'Active'),
('Platinum Bracelet', 2, NOW(), DATE_ADD(NOW(), INTERVAL 9 DAY), 250, 'Elegant platinum bracelet', 'New', 250, 200, 0, 'Active'),
('Diamond Necklace', 2, NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 5 DAY, 200, 'Exquisite diamond necklace in white gold setting', 'New', 150, 120, 0, 'Active'),
('Luxury Cufflinks', 1, NOW() - INTERVAL 2 DAY, NOW() + INTERVAL 10 DAY, 140, 'High-end luxury cufflinks for the sophisticated gentleman', 'New', 100, 90, 0, 'Active'),
('Tanzanite Pendant', 2, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 5 DAY, 190, 'Unique tanzanite pendant on a delicate silver chain', 'New', 130, 110, 0, 'Active'),
('Gold Bangle', 1, NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 7 DAY, 160, 'Elegant gold bangle with intricate floral pattern', 'Used', 110, 80, 0, 'Active'),
('Platinum Wedding Band', 2, NOW() - INTERVAL 3 DAY, NOW() + INTERVAL 9 DAY, 180, 'Classic platinum wedding band for a lifetime of love', 'New', 120, 100, 0, 'Active'),
('Silver Charm Bracelet', 1, NOW() - INTERVAL 6 DAY, NOW() + INTERVAL 4 DAY, 120, 'Charming silver bracelet adorned with various charms', 'New', 80, 60, 0, 'Active'),
('Topaz Stud Earrings', 2, NOW() - INTERVAL 10 DAY, NOW() + INTERVAL 2 DAY, 150, 'Sparkling topaz stud earrings for a touch of elegance', 'Used', 100, 70, 0, 'Active'),
('Rose Gold Pendant', 1, NOW() - INTERVAL 8 DAY, NOW() + INTERVAL 6 DAY, 170, 'Beautiful rose gold pendant with a dainty chain', 'New', 110, 90, 0, 'Active'),
('Pearl Drop Necklace', 2, NOW() - INTERVAL 4 DAY, NOW() + INTERVAL 8 DAY, 160, 'Timeless pearl drop necklace with sterling silver chain', 'New', 120, 100, 0, 'Active'),
('Amethyst Ring', 1, NOW() - INTERVAL 9 DAY, NOW() + INTERVAL 3 DAY, 140, 'Exquisite amethyst ring with a halo of diamonds', 'Used', 90, 60, 0, 'Active'),
('Citrine Bracelet', 2, NOW() - INTERVAL 2 DAY, NOW() + INTERVAL 10 DAY, 130, 'Radiant citrine bracelet with a mix of gemstones', 'New', 80, 50, 0, 'Active'),
('Crystal Flower Brooch', 1, NOW() - INTERVAL 7 DAY, NOW() + INTERVAL 5 DAY, 120, 'Delicate crystal flower brooch for a touch of glamour', 'New', 70, 60, 0, 'Active'),
('Onyx Cuff Bracelet', 2, NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 7 DAY, 150, 'Bold onyx cuff bracelet with silver detailing', 'Used', 100, 80, 0, 'Active'),
('Aquamarine Drop Earrings', 1, NOW() - INTERVAL 3 DAY, NOW() + INTERVAL 9 DAY, 170, 'Captivating aquamarine drop earrings for a chic look', 'New', 110, 90, 0, 'Active'),
('Diamond Stud Earrings', 2, NOW() - INTERVAL 6 DAY, NOW() + INTERVAL 4 DAY, 180, 'Classic diamond stud earrings for a touch of elegance', 'New', 120, 100, 0, 'Active'),
('Luxury Gold Watch', 1, NOW() - INTERVAL 8 DAY, NOW() + INTERVAL 6 DAY, 250, 'Luxurious gold watch with intricate detailing', 'New', 180, 150, 0, 'Active'),
('Citrine and Diamond Ring', 2, NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 7 DAY, 210, 'Exquisite citrine and diamond ring in 18k gold', 'Used', 150, 120, 0, 'Active');

   
-- Additional bids for 'Elegant Watch' (AuctionID 1) 100
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (2, 1, 10, NOW() - INTERVAL 5 DAY),
   (3, 1, 20, NOW() - INTERVAL 4 DAY),
   (4, 1, 50, NOW() - INTERVAL 3 DAY),
   (1, 1, 60, NOW() - INTERVAL 2 DAY),
   (2, 1, 100, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Diamond Earrings' (AuctionID 2) 200
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (3, 2, 100, NOW() - INTERVAL 5 DAY),
   (4, 2, 110, NOW() - INTERVAL 4 DAY),
   (1, 2, 150, NOW() - INTERVAL 3 DAY),
   (2, 2, 160, NOW() - INTERVAL 2 DAY),
   (3, 2, 200, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Gold Necklace' (AuctionID 3) 150 
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (4, 3, 70, NOW() - INTERVAL 5 DAY),
   (1, 3, 90, NOW() - INTERVAL 4 DAY),
   (2, 3, 100, NOW() - INTERVAL 3 DAY),
   (3, 3, 110, NOW() - INTERVAL 2 DAY),
   (4, 3, 150, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Silver Bracelet' (AuctionID 4) 75
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 4, 20, NOW() - INTERVAL 5 DAY),
   (2, 4, 25, NOW() - INTERVAL 4 DAY),
   (3, 4, 30, NOW() - INTERVAL 3 DAY),
   (4, 4, 40, NOW() - INTERVAL 2 DAY),
   (1, 4, 75, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Pearl Earrings' (AuctionID 5) 90
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (2, 5, 40, NOW() - INTERVAL 5 DAY),
   (3, 5, 50, NOW() - INTERVAL 4 DAY),
   (4, 5, 60, NOW() - INTERVAL 3 DAY),
   (1, 5, 70, NOW() - INTERVAL 2 DAY),
   (2, 5, 90, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Luxury Ring' (AuctionID 6) 300
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (3, 6, 50, NOW() - INTERVAL 5 DAY),
   (4, 6, 120, NOW() - INTERVAL 4 DAY),
   (1, 6, 170, NOW() - INTERVAL 3 DAY),
   (2, 6, 250, NOW() - INTERVAL 2 DAY),
   (3, 6, 300, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Silver Watch' (AuctionID 7) 80
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (4, 7, 30, NOW() - INTERVAL 5 DAY),
   (1, 7, 45, NOW() - INTERVAL 4 DAY),
   (2, 7, 60, NOW() - INTERVAL 3 DAY),
   (3, 7, 70, NOW() - INTERVAL 2 DAY),
   (4, 7, 80, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Sapphire Earrings' (AuctionID 8) 150 
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 8, 50, NOW() - INTERVAL 5 DAY),
   (2, 8, 70, NOW() - INTERVAL 4 DAY),
   (3, 8, 100, NOW() - INTERVAL 3 DAY),
   (4, 8, 130, NOW() - INTERVAL 2 DAY),
   (1, 8, 150, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Diamond Necklace' (AuctionID 9) 200
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (2, 9, 100, NOW() - INTERVAL 5 DAY),
   (3, 9, 130, NOW() - INTERVAL 4 DAY),
   (4, 9, 150, NOW() - INTERVAL 3 DAY),
   (1, 9, 180, NOW() - INTERVAL 2 DAY),
   (2, 9, 200, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Leather Bracelet' (AuctionID 10) 60
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (3, 10, 10, NOW() - INTERVAL 5 DAY),
   (4, 10, 25, NOW() - INTERVAL 4 DAY),
   (1, 10, 35, NOW() - INTERVAL 3 DAY),
   (2, 10, 40, NOW() - INTERVAL 2 DAY),
   (3, 10, 60, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Ruby Ring' (AuctionID 11) 130 
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (4, 11, 60, NOW() - INTERVAL 5 DAY),
   (1, 11, 80, NOW() - INTERVAL 4 DAY),
   (2, 11, 100, NOW() - INTERVAL 3 DAY),
   (3, 11, 120, NOW() - INTERVAL 2 DAY),
   (4, 11, 130, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Platinum Bracelet' (AuctionID 12) 250
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 12, 160, NOW() - INTERVAL 5 DAY),
   (2, 12, 180, NOW() - INTERVAL 4 DAY),
   (3, 12, 200, NOW() - INTERVAL 3 DAY),
   (4, 12, 225, NOW() - INTERVAL 2 DAY),
   (1, 12, 250, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Diamond Necklace' (AuctionID 13) 200
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (2, 13, 100, NOW() - INTERVAL 5 DAY),
   (3, 13, 120, NOW() - INTERVAL 4 DAY),
   (4, 13, 150, NOW() - INTERVAL 3 DAY),
   (1, 13, 170, NOW() - INTERVAL 2 DAY),
   (2, 13, 200, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Vintage Pocket Watch' (AuctionID 14) 140
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (3, 14, 30, NOW() - INTERVAL 5 DAY),
   (4, 14, 50, NOW() - INTERVAL 4 DAY),
   (1, 14, 75, NOW() - INTERVAL 3 DAY),
   (2, 14, 100, NOW() - INTERVAL 2 DAY),
   (3, 14, 140, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Sapphire Earrings' (AuctionID 15) 190
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (4, 15, 10, NOW() - INTERVAL 5 DAY),
   (1, 15, 100, NOW() - INTERVAL 4 DAY),
   (2, 15, 130, NOW() - INTERVAL 3 DAY),
   (3, 15, 140, NOW() - INTERVAL 2 DAY),
   (4, 15, 190, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Ruby Bracelet' (AuctionID 16) 160
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 16, 60, NOW() - INTERVAL 5 DAY),
   (2, 16, 80, NOW() - INTERVAL 4 DAY),
   (3, 16, 100, NOW() - INTERVAL 3 DAY),
   (4, 16, 120, NOW() - INTERVAL 2 DAY),
   (1, 16, 160, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Antique Pearl Brooch' (AuctionID 17) 180
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (2, 17, 140, NOW() - INTERVAL 5 DAY),
   (3, 17, 150, NOW() - INTERVAL 4 DAY),
   (4, 17, 160, NOW() - INTERVAL 3 DAY),
   (1, 17, 170, NOW() - INTERVAL 2 DAY),
   (2, 17, 180, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Crystal Chandelier Earrings' (AuctionID 18) 120
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (3, 18, 60, NOW() - INTERVAL 5 DAY),
   (4, 18, 70, NOW() - INTERVAL 4 DAY),
   (1, 18, 90, NOW() - INTERVAL 3 DAY),
   (4, 18, 100, NOW() - INTERVAL 2 DAY),
   (3, 18, 120, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Emerald Ring' (AuctionID 19) 150
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (4, 19, 60, NOW() - INTERVAL 5 DAY),
   (1, 19, 80, NOW() - INTERVAL 4 DAY),
   (3, 19, 100, NOW() - INTERVAL 3 DAY),
   (3, 19, 125, NOW() - INTERVAL 2 DAY),
   (4, 19, 150, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Luxury Cufflinks' (AuctionID 20) 170
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 20, 45, NOW() - INTERVAL 5 DAY),
   (4, 20, 50, NOW() - INTERVAL 4 DAY),
   (3, 20, 100, NOW() - INTERVAL 3 DAY),
   (4, 20, 125, NOW() - INTERVAL 2 DAY),
   (1, 20, 170, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Tanzanite Pendant' (AuctionID 21) 160
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 21, 90, NOW() - INTERVAL 5 DAY),
   (3, 21, 100, NOW() - INTERVAL 4 DAY),
   (4, 21, 120, NOW() - INTERVAL 3 DAY),
   (1, 21, 150, NOW() - INTERVAL 2 DAY),
   (3, 21, 160, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Gold Bangle' (AuctionID 22) 140
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (3, 22, 50, NOW() - INTERVAL 5 DAY),
   (4, 22, 70, NOW() - INTERVAL 4 DAY),
   (1, 22, 100, NOW() - INTERVAL 3 DAY),
   (4, 22, 120, NOW() - INTERVAL 2 DAY),
   (3, 22, 140, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Platinum Wedding Band' (AuctionID 23) 130
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (4, 23, 70, NOW() - INTERVAL 5 DAY),
   (1, 23, 80, NOW() - INTERVAL 4 DAY),
   (3, 23, 90, NOW() - INTERVAL 3 DAY),
   (3, 23, 100, NOW() - INTERVAL 2 DAY),
   (4, 23, 130, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Silver Charm Bracelet' (AuctionID 24) 120
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 24, 30, NOW() - INTERVAL 5 DAY),
   (1, 24, 50, NOW() - INTERVAL 4 DAY),
   (3, 24, 90, NOW() - INTERVAL 3 DAY),
   (4, 24, 100, NOW() - INTERVAL 2 DAY),
   (1, 24, 120, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Topaz Stud Earrings' (AuctionID 25) 150
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 25, 65, NOW() - INTERVAL 5 DAY),
   (3, 25, 90, NOW() - INTERVAL 4 DAY),
   (4, 25, 100, NOW() - INTERVAL 3 DAY),
   (1, 25, 125, NOW() - INTERVAL 2 DAY),
   (4, 25, 150, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Rose Gold Pendant' (AuctionID 26) 170
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (3, 26, 100, NOW() - INTERVAL 5 DAY),
   (4, 26, 150, NOW() - INTERVAL 4 DAY),
   (1, 26, 160, NOW() - INTERVAL 3 DAY),
   (1, 26, 165, NOW() - INTERVAL 2 DAY),
   (3, 26, 170, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Pearl Drop Necklace' (AuctionID 27) 180
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (4, 27, 140, NOW() - INTERVAL 5 DAY),
   (1, 27, 150, NOW() - INTERVAL 4 DAY),
   (4, 27, 160, NOW() - INTERVAL 3 DAY),
   (3, 27, 170, NOW() - INTERVAL 2 DAY),
   (4, 27, 180, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Amethyst Ring' (AuctionID 28) 250
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (1, 28, 125, NOW() - INTERVAL 5 DAY),
   (2, 28, 150, NOW() - INTERVAL 4 DAY),
   (3, 28, 200, NOW() - INTERVAL 3 DAY),
   (4, 28, 225, NOW() - INTERVAL 2 DAY),
   (3, 28, 250, NOW() - INTERVAL 1 DAY);

-- Additional bids for 'Citrine Bracelet' (AuctionID 29) 210
INSERT INTO Bid (BuyerID, AuctionID, BidAmount, BidTime)
VALUES
   (2, 29, 170, NOW() - INTERVAL 5 DAY),
   (3, 29, 180, NOW() - INTERVAL 4 DAY),
   (4, 29, 190, NOW() - INTERVAL 3 DAY),
   (1, 29, 200, NOW() - INTERVAL 2 DAY),
   (1, 29, 210, NOW() - INTERVAL 1 DAY);

-- User roles 
INSERT INTO Roles (RoleName)
VALUES
('Seller'),
('Buyer');

INSERT INTO UserRole (UserId, RoleId)
VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 2),
(4, 2);

-- User ratings table
INSERT INTO Ratings (SellerID, RatingScore, BuyerID)
VALUES
(1, 2, 1),
(1, 4, 2),
(1, 5, 3),
(2, 4, 1),
(2, 2, 2),
(2, 3, 3);

-- User comment table
INSERT INTO Comment (SellerID, ComentatorID, CommentText)
VALUES
(1, 2, "Great seller! Fast shipping and item as described."),
(2, 1, "Excellent service. Seller went above and beyond my expectations."),
(1, 3, "Positive experience. Seller communicated well throughout the process."),
(2, 4, "A+ seller. Would do business again."),
(1, 4, "Pleasant transaction. Seller was courteous and professional."),
(2, 3, "Item arrived on time and in great condition. Thanks, seller!"),
(1, 2, "Positive experience. Smooth transaction with a trustworthy seller."),
(2, 4, "Responsive seller. Answered all my questions promptly."),
(1, 4, "Positive feedback for the seller. Happy with my purchase."),
(2, 3, "Seller was accommodating and easy to deal with."),
(1, 2, "Highly recommend this seller. Honest and reliable."),
(2, 1, "Happy with the product. Seller exceeded my expectations."),
(1, 2, "Positive experience. Smooth transaction with a trustworthy seller."),
(2, 4, "Great communication. Seller addressed my concerns effectively."),
(1, 3, "Smooth transaction. Seller is reliable and honest."),
(2, 1, "Item as described. Smooth transaction with a reliable seller."),
(1, 3, "Great seller! Quick shipping and well-packaged."),
(2, 4, "Pleasant experience with this seller. Item arrived on time."),
(1, 2, "Reliable seller. Would buy from them again."),
(2, 1, "A+ transaction. Seller was courteous and professional."),
(1, 4, "Smooth process. Seller provided clear communication."),
(2, 3, "Happy with my purchase. Seller was easy to work with."),
(1, 2, "Excellent seller! Item arrived promptly and in good condition."),
(2, 1, "Positive feedback for the seller. Smooth transaction."),
(1, 2, "Quick and easy transaction. Seller was responsive."),
(2, 4, "Satisfied with the purchase. Seller provided accurate information."),
(1, 3, "Great seller! Item arrived as described."),
(2, 1, "Smooth transaction. Seller was professional and efficient."),
(1, 3, "Positive experience. Seller went the extra mile."),
(2, 4, "Reliable seller. Item received in good condition.");


-- CATEGORIES
-- Watches
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (1, 1), 
   (1, 7), 
   (1, 28); 

-- Earrings
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (2, 2),
   (2, 5),
   (2, 8),
   (2, 27),
   (2, 19);

-- Necklaces
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (3, 9),
   (3, 13),
   (3, 21);

-- Bracelets
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (4, 10),
   (4, 4),
   (4, 12),
   (4, 25);

-- Rings
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (5, 6),
   (5, 11);

-- Gold
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (9, 3),
   (9, 16),
   (9, 22),
   (9, 28);

-- Bronze (empty)

-- Male (empty) 6
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (6, 10),
   (6, 14),
   (6, 4);

-- Female (empty) 7
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (7, 2),
   (7, 5);

-- Unisex (empty) 8
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (8, 1),
   (8, 7);

-- Silver
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (10, 4),
   (10, 18);

-- Platinum
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (12, 12),
   (12, 17);

-- Rose Gold
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (13, 20);

-- Gemstone
INSERT INTO CategoryAuction (CategoryID, AuctionID)
VALUES
   (14, 19),
   (14, 29),
   (14, 9),
   (14, 8),
   (14, 2),
   (14, 15),
   (14, 11),
   (14, 13),
   (14, 24),
   (14, 26),
   (14, 27),
   (14, 23);
