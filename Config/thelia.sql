
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- dpdclassic_price
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `dpdclassic_price`;

CREATE TABLE `dpdclassic_price`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `area_id` INTEGER NOT NULL,
    `weight` DECIMAL(16,2),
    `price` DECIMAL(16,2) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `FI_dpdclassic_price_area_id` (`area_id`),
    CONSTRAINT `fk_dpdclassic_price_area_id`
        FOREIGN KEY (`area_id`)
        REFERENCES `area` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
