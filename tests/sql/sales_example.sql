DROP TABLE IF EXISTS sale;
CREATE TABLE sale(
  sale_id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_type  ENUM('Laptop','Desktop','Accessory','Service') NOT NULL,
  sale_date     DATE NOT NULL,
  amt           DECIMAL(13,2) NOT NULL,
  qty           SMALLINT NOT NULL,
  PRIMARY KEY(sale_id)
) ENGINE=InnoDB;

INSERT INTO sale(product_type, sale_date, amt, qty) VALUES
('Laptop', '2015-01-01', 1500.00, 1),
('Laptop', '2015-01-01', 1800.00, 1),
('Laptop', '2015-01-03', 1500.00, 1),
('Laptop', '2015-01-05', 1500.00, 1),
('Laptop', '2015-01-06', 1500.00, 1),
('Laptop', '2015-01-07', 1800.00, 1),
('Desktop', '2015-01-01', 1200.00, 1),
('Desktop', '2015-01-04', 1200.00, 1),
('Desktop', '2015-01-06', 1500.00, 1),
('Accessory', '2015-01-01', 20.00, 1),
('Accessory', '2015-01-01', 60.00, 3),
('Accessory', '2015-01-02', 10.00, 1),
('Accessory', '2015-01-03', 40.00, 2),
('Service', '2015-01-05', 250.00, 1);


SELECT product_type, sale_date, SUM(amt) AS sum_amt, SUM(qty) AS sum_qty
FROM   sale
GROUP BY product_type, sale_date
ORDER BY product_type, sale_date;
