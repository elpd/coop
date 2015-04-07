# Table: money transfers

CREATE TABLE `coops-prod`.`money_transfers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transferring_party_type` INT NOT NULL,
  `transferring_party_id` INT UNSIGNED NOT NULL,
  `receiving_party_type` INT NOT NULL,
  `receiving_party_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(19,4) NOT NULL DEFAULT 0,
  `transfer_date` DATETIME NOT NULL,
  `feeder_id` INT UNSIGNED NOT NULL,
  `comment` TINYTEXT NULL,
  PRIMARY KEY (`id`));

# Table: orders

ALTER TABLE `coops-prod`.`orders`
ADD COLUMN `previous_debt_when_closed` DECIMAL(19,4) NOT NULL DEFAULT 0 AFTER `order_reset_day`,
ADD COLUMN `order_total_when_closed` DECIMAL(19,4) NOT NULL DEFAULT 0,
ADD COLUMN `time_of_closing` DATE NULL;
