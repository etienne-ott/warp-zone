CREATE TABLE `warp_zone`.`entry` (
  `entry_id` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `url` LONGTEXT NOT NULL COMMENT '',
  `display_name` VARCHAR(255) NOT NULL COMMENT '',
  `section_id` INT NOT NULL COMMENT '',
  `priority` INT NOT NULL COMMENT '',
  PRIMARY KEY (`entry_id`)  COMMENT '',
  UNIQUE INDEX `entry_id_UNIQUE` (`entry_id` ASC)  COMMENT '');

ALTER TABLE `warp_zone`.`entry` 
ADD INDEX `section_idx` (`section_id` ASC)  COMMENT '';
ALTER TABLE `warp_zone`.`entry` 
ADD CONSTRAINT `section`
  FOREIGN KEY (`section_id`)
  REFERENCES `warp_zone`.`section` (`section_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;