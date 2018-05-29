DROP TABLE IF EXISTS `links`;

CREATE TABLE `links` (
    `source` VARCHAR(180) NOT NULL PRIMARY KEY,
    `destination` VARCHAR(2083) NOT NULL,
    `clicks` BIGINT(20) unsigned DEFAULT 0,
    `timestamp` timestamp not null default CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

REPLACE INTO `options` (`key`, `value`) VALUES ('short.links:version', <{var::version}>);