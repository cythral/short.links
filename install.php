<?php

use \phyrex\Template;
use \Phroses\Database\Database;

define("SQL_INSTALL_FILE", __DIR__."/schema/install.sql");
define("SL_DBVER_OPTION", "short.links:version");

$db = Database::getInstance();

// fetch version
$version = $db->prepare("select `value` from `options` where `key`=?", [ SL_DBVER_OPTION ])->fetchColumn();

if(!$version) {
    $sql = new Template(SQL_INSTALL_FILE);
    $sql->version = SL_VERSION;

    $db->getHandle()->query((string) $sql);

} else if($version != SL_VERSION) {
    for($i = ($version + 1); $i <= SL_VERSION; $i++) {
        $db->getHandle()->query(file_get_contents(SL_ROOT."/schema/update-$i.sql"));
    }
}