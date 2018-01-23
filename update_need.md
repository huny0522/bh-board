#2018.01.23
bh_board_manager 에 use_secret 필드 추가

	ALTER TABLE `bh_board_manager` ADD COLUMN `use_secret` ENUM('y','n') NOT NULL DEFAULT 'n' AFTER `man_to_man`;
	
