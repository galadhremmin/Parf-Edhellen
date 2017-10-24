ALTER TABLE `forum_threads` ADD `account_id` int(5) unsigned DEFAULT NULL;
ALTER TABLE `forum_threads` ADD `number_of_posts` int(10) unsigned DEFAULT 0;
ALTER TABLE `forum_threads` ADD `number_of_likes` int(10) unsigned DEFAULT 0;
ALTER TABLE `forum_threads` MODIFY COLUMN `subject` varchar(512) NOT NULL;
ALTER TABLE `forum_threads` DROP `roles`;
ALTER TABLE `forum_threads` ADD UNIQUE INDEX `ix_thread_entity` (`entity_id`, `entity_type`);

UPDATE `forum_threads` ft
    SET ft.`number_of_posts` = (SELECT COUNT(*) FROM `forum_posts` WHERE `forum_thread_id` = ft.`id`),
        ft.`number_of_likes` = (SELECT SUM(`number_of_likes`) FROM `forum_posts` WHERE `forum_thread_id` = ft.`id`),
        ft.`account_id`      = (SELECT `account_id` FROM `forum_posts` WHERE `forum_thread_id` = ft.`id` ORDER BY `id` DESC LIMIT 1),
        ft.`updated_at`      = (SELECT `created_at` FROM `forum_posts` WHERE `forum_thread_id` = ft.`id` ORDER BY `id` DESC LIMIT 1);

CREATE TABLE `forum_discussions` (
    `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
    `account_id` int(6) unsigned NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

INSERT INTO `version` VALUES (3.1, NOW());
