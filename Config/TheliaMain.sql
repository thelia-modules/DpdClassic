
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- prices_slices
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `prices_slices`;

CREATE TABLE `prices_slices`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `weight` FLOAT NOT NULL,
    `price` FLOAT NOT NULL,
    `id_area` INTEGER NOT NULL,
    `info` VARCHAR(255),
    PRIMARY KEY (`id`),
    INDEX `fi_area_prices_slices_id` (`id_area`),
    CONSTRAINT `fk_area_prices_slices_id`
        FOREIGN KEY (`id_area`)
        REFERENCES `area` (`id`)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
