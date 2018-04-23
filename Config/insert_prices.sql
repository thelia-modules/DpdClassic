# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Data for dpdclassic_price
-- ---------------------------------------------------------------------
-- Then add new entries
SELECT @max := MAX(`id`) FROM `dpdclassic_price`;
SET @max := @max+1;
-- insert dpdclassic_price
INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '0.25',
   '5.15'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '0.5',
   '5.59'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '1',
   '5.89'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '2',
   '6.19'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '3',
   '6.51'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '5',
   '7.15'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '6',
   '7.52'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '7',
   '7.89'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '8',
   '8.26'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '9',
   '8.63'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '10',
   '9'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '11',
   '9.4'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '12',
   '9.75'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '13',
   '10.2'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '14',
   '10.62'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '15',
   '11.05'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '16',
   '11.55'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '17',
   '12.05'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '18',
   '12.55'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '19',
   '13.05'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '20',
   '13.55'
  );

INSERT INTO `dpdclassic_price` (`id`, `area_id`, `weight`,`price`) VALUES
  (@max,
   '1',
   '100',
   '67.75'
  );

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;