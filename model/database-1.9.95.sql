ALTER TABLE `audit_trails` ADD `is_admin` int(1) DEFAULT 0;
ALTER TABLE `system_errors` ADD `is_common` int(1) DEFAULT 0;

UPDATE `system_errors` SET `is_common` = 1 
    WHERE `message` LIKE 'Illuminate\\\\Auth\\\\AuthenticationException%' OR
          `message` LIKE 'Illuminate\\\\Session\\\\TokenMismatchException%' OR
          `message` LIKE 'Symfony\\\\Component\\\\HttpKernel\\\\Exception\\\\NotFoundHttpException%';


INSERT INTO `version` (`number`, `date`) VALUES (1.995, NOW());
