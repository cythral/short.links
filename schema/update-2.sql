ALTER TABLE `links` ADD (
    `timestamp` timestamp not null default CURRENT_TIMESTAMP
);

REPLACE INTO `options` (`key`, `value`) VALUES ('short.links:version', 2);