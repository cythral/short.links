<?php

use \Phroses\Database\Database;

define("SQL_FILE", __DIR__."/setup.sql");

$db = Database::getInstance();
$db->getHandle()->query(file_get_contents(SQL_FILE));

$this->config->installed = true;
$this->config->save();