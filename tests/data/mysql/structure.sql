CREATE TABLE users (
  id int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(255) NOT NULL,
  surname varchar(255) NOT NULL
) ENGINE='InnoDB';

CREATE TABLE orders (
  id int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  users_id int(11) unsigned NOT NULL,
  name text NULL COMMENT 'trigger',
FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE='InnoDB';

DELIMITER ;;
CREATE TRIGGER orders_bi BEFORE INSERT ON orders FOR EACH ROW
BEGIN
SET new.name = COALESCE(new.name, 'test');
END;;

CREATE PROCEDURE test_procedure ()
BEGIN
SELECT * FROM users;
END;;
DELIMITER ;
