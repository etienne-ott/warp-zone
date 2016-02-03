CREATE TABLE `warp_zone`.`section` (
  `section_id` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `name` VARCHAR(255) NOT NULL COMMENT '',
  `priority` INT NOT NULL COMMENT '',
  PRIMARY KEY (`section_id`)  COMMENT '',
  UNIQUE INDEX `section_id_UNIQUE` (`section_id` ASC)  COMMENT '');
