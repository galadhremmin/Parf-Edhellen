CREATE TABLE `mail_settings`(
    `account_id` int(5) unsigned NOT NULL,
    `forum_post_created` int(1) unsigned NOT NULL DEFAULT 1,
    `forum_contribution_approved` int(1) unsigned NOT NULL DEFAULT 1,
    `forum_contribution_rejected` int(1) unsigned NOT NULL DEFAULT 1,
    `forum_posted_on_profile` int(1) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`account_id`)
);

CREATE TABLE `mail_setting_overrides`(
    `account_id` int(5) unsigned NOT NULL,
    `entity_id` int(11) unsigned NOT NULL,
    `entity_type` varchar(16) NOT NULL COLLATE utf8_swedish_ci,
    `disabled` int(1) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`account_id`, `entity_type`, `entity_id`)
);

INSERT INTO `version` VALUES (4.2, NOW());
