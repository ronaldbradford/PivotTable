DROP TABLE IF EXISTS category;
CREATE TABLE category(
  category_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  PRIMARY KEY(category_id)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS product;
CREATE TABLE product (
  product_id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id   INT UNSIGNED NOT NULL,
  name          VARCHAR(100) NOT NULL,
  amt           DECIMAL(10,2) NOT NULL,
  PRIMARY KEY(product_id),
  CONSTRAINT FOREIGN KEY (category_id) REFERENCES category(category_id) 
) ENGINE=InnoDB;

DROP TABLE IF EXISTS country;
CREATE TABLE country(
  country_code CHAR(2) NOT NULL,
  name         VARCHAR(60) NOT NULL,
  iso_alpha3   CHAR(3) NOT NULL,
  iso_numeric  CHAR(3) NOT NULL,
  PRIMARY KEY(country_code)
);

DROP TABLE IF EXISTS customer;
CREATE TABLE customer (
  customer_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name    VARCHAR(20) NOT NULL,
  last_name     VARCHAR(20) NOT NULL,
  email         VARCHAR(50) NOT NULL,
  country_code  CHAR(2) NOT NULL,
  PRIMARY KEY(customer_id),
  CONSTRAINT FOREIGN KEY (country_code) REFERENCES country(country_code) 
) ENGINE=InnoDB;


DROP TABLE IF EXISTS `order`;
CREATE TABLE `order`(
  order_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id   INT UNSIGNED NOT NULL,
  order_date    DATE NOT NULL,
  PRIMARY KEY(order_id),
  CONSTRAINT FOREIGN KEY (customer_id) REFERENCES customer(customer_id) 
) ENGINE=InnoDB;

DROP TABLE IF EXISTS order_line;
CREATE TABLE order_line(
  order_line_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id      INT UNSIGNED NOT NULL,
  product_id    INT UNSIGNED NOT NULL,
  amt           DECIMAL(13,2) NOT NULL,
  PRIMARY KEY(order_line_id),
  CONSTRAINT FOREIGN KEY (order_id) REFERENCES `order`(order_id) ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (product_id) REFERENCES product(product_id)
) ENGINE=InnoDB;


CREATE OR REPLACE VIEW sale_item AS
SELECT c.name AS category, o.order_date, cu.country_code, p.name as product, ol.amt, 
       COUNT(DISTINCT o.order_id) AS sales, COUNT(ol.order_line_id) AS products
FROM   `order` o
INNER JOIN customer cu USING (customer_id)
INNER JOIN order_line ol USING (order_id)
INNER JOIN product p USING (product_id)
INNER JOIN category c USING (category_id)
GROUP BY c.name, o.order_date, cu.country_code, p.name, ol.amt;




SELECT category, DATE_FORMAT(order_date, '%Y-%m') AS sale_date, SUM(amt) AS sum_amt, SUM(sales) AS sum_sales, SUM(products) AS sum_products
FROM   sale_item
GROUP BY category, sale_date
ORDER BY category, sale_date;

LOAD DATA LOCAL INFILE 'data/category.tsv' INTO TABLE category;
LOAD DATA LOCAL INFILE 'data/product.tsv' INTO TABLE product;
LOAD DATA LOCAL INFILE 'data/country.tsv' INTO TABLE country;
LOAD DATA LOCAL INFILE 'data/person.tsv' INTO TABLE customer(first_name,last_name,@username,email) SET country_code='US';
UPDATE customer SET country_code = 'DE' WHERE email LIKE '%.de';
UPDATE customer SET country_code = 'AU' WHERE email LIKE '%.au';
UPDATE customer SET country_code = 'RU' WHERE email LIKE '%.ru';
UPDATE customer SET country_code = 'JP' WHERE email LIKE '%.jp';
UPDATE customer SET country_code = 'CA' WHERE email LIKE '%.net';
UPDATE customer SET country_code = 'VU' WHERE customer_id % 10 = 4 AND country_code='US';
UPDATE customer SET country_code = 'GB' WHERE customer_id % 10 = 5 AND country_code='US';
UPDATE customer SET country_code = 'BR' WHERE customer_id % 10 = 6 AND country_code='US';
UPDATE customer SET country_code = 'CH' WHERE customer_id % 10 = 7 AND country_code='US';
UPDATE customer SET country_code = 'VI' WHERE customer_id % 10 = 8 AND country_code='US';
UPDATE customer SET country_code = 'SG' WHERE customer_id % 10 = 9 AND country_code='US';
SELECT country_code,count(*) FROM customer GROUP BY country_code;


DELIMITER $$
DROP PROCEDURE IF EXISTS generate_order$$
CREATE PROCEDURE generate_order(p_total_count INT, p_max_products INT)
BEGIN
  DECLARE counter INT DEFAULT 1;
  DECLARE id INT;
  DECLARE done INT DEFAULT FALSE;
  DECLARE cur CURSOR FOR SELECT customer_id FROM customer ORDER BY RAND();
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;



  OPEN cur;
  l: LOOP
    FETCH cur INTO id;
    IF done THEN
      LEAVE l;
    END IF;
    IF counter > p_total_count THEN
      LEAVE l;
    END IF;

    SET @product_limit = FORMAT(RAND()*p_max_products,0) + 1;
    SET @day = FORMAT(RAND()*365*3,0);
    SET @order_date = DATE_ADD(CURDATE() - INTERVAL 3 YEAR,INTERVAL @day DAY);


    START TRANSACTION;
    INSERT INTO `order` (customer_id, order_date) VALUES(id, @order_date);
    SET @sql=CONCAT('INSERT INTO order_line (order_id, product_id, amt) SELECT @@LAST_INSERT_ID,product_id,amt FROM product ORDER BY RAND() LIMIT ',@product_limit);

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    COMMIT;
    SET counter := counter + 1;

  END LOOP;

  CLOSE cur;
  SELECT 'orders' AS tbl,COUNT(*) FROM `order`
  UNION
  SELECT 'order lines',COUNT(*) FROM order_line;
END $$
DELIMITER ;

