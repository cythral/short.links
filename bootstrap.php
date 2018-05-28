<?php


use \listen\Events;

use \Phroses\Plugins\Cythral\ShortLinks\Controller;
use \Phroses\Routes\Controller as RouteController;
use \Phroses\Switcher;
use function \Phroses\{ println };
use const \reqc\{ URI, METHOD };

if(!defined("reqc\URI")) define("reqc\URI", "");
if(!defined("SL_ROOT")) define("SL_ROOT", __DIR__);

spl_autoload_register(function($class) {
    $class = str_replace("Phroses\Plugins\Cythral\ShortLinks\\", "", $class);

    if(file_exists(SL_ROOT."/src/{$class}.php")) {
        include SL_ROOT."/src/{$class}.php";
    }
});

$config = $this->config;

Events::listen("route.follow", function($response, $method, $site, $page) use ($config) {
    if($site->url == $this->config->url) {

        (new Switcher($response))

        ->case(RouteController::RESPONSES["PAGE"][404], function() {
            Controller::goto(substr(URI, 1));
        })

        ->case(RouteController::RESPONSES["API"], function() use ($config) {
            Controller::api(substr(URI, 4), METHOD, $config);
        });
        
    }
});

Events::listen("commandsmapped", function() {
    if($_SERVER["argv"][0] == "install-shortlinks") {
        include SL_ROOT."/install.php";
        println("Installed successfully");
        exit;
    }
});