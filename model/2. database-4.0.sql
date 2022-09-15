CREATE TABLE `mail_settings`(
  `account_id` int(5) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  `updated_at` datetime DEFAULT NULL,
  `forum_post_created` int(1) unsigned NOT NULL DEFAULT 1,
  `forum_contribution_approved` int(1) unsigned NOT NULL DEFAULT 1,
  `forum_contribution_rejected` int(1) unsigned NOT NULL DEFAULT 1,
  `forum_posted_on_profile` int(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB;

CREATE TABLE `mail_setting_overrides`(
  `account_id` int(5) unsigned NOT NULL,
  `entity_id` int(11) unsigned NOT NULL,
  `entity_type` varchar(16) NOT NULL COLLATE utf8_swedish_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  `updated_at` datetime DEFAULT NULL,
  `disabled` int(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`account_id`, `entity_type`, `entity_id`)
) ENGINE=InnoDB;

ALTER TABLE `account_role_rels` ENGINE=InnoDB;
ALTER TABLE `accounts` ENGINE=InnoDB;
ALTER TABLE `audit_trails` ENGINE=InnoDB;
ALTER TABLE `authorization_providers` ENGINE=InnoDB;
ALTER TABLE `contributions` ENGINE=InnoDB;
ALTER TABLE `favourites` ENGINE=InnoDB;
ALTER TABLE `flashcard_results` ENGINE=InnoDB;
ALTER TABLE `flashcards` ENGINE=InnoDB;
ALTER TABLE `forum_post_likes` ENGINE=InnoDB;
ALTER TABLE `forum_posts` ENGINE=InnoDB;
ALTER TABLE `gloss_groups` ENGINE=InnoDB;
ALTER TABLE `glosses` ENGINE=InnoDB;
ALTER TABLE `inflections` ENGINE=InnoDB;
ALTER TABLE `keywords` ENGINE=InnoDB;
ALTER TABLE `languages` ENGINE=InnoDB;
ALTER TABLE `roles` ENGINE=InnoDB;
ALTER TABLE `sentence_fragments` ENGINE=InnoDB;
ALTER TABLE `sentences` ENGINE=InnoDB;
ALTER TABLE `speeches` ENGINE=InnoDB;
ALTER TABLE `system_errors` ENGINE=InnoDB;
ALTER TABLE `words` ENGINE=InnoDB;

INSERT INTO `version` VALUES (4.2, NOW());
