<?php

namespace Phroses\Plugins\Cythral\ShortLinks;

use \Phroses\IP;
use \Phroses\JsonServer;
use \Phroses\Switcher;

class Controller {
    static private $config;
    static private $oh;

    static public function setup($config) {
        self::$config = $config;
    }

    static public function goto($link) {
        $url = ShortLink::find($link);

        if($url) {
            $url->clicks++;

            http_response_code(301);
            header("location: {$url->destination}");
            exit;
        }
    }

    static public function api($endpoint, $method) {
        self::$oh = new JsonServer;

        (new Switcher($endpoint))

        ->case("/shorten", function() use ($method) {
            if(strtolower($method) == "post") {
                self::checkWhiteList(new IP($_SERVER["REMOTE_ADDR"]));

                $source = self::setupSource($_POST["source"] ?? null);
                $url = ShortLink::create($source, $_POST["destination"]);
                
                $out->success(200, [ "link" => "/".$url->source ]);

            } else $out->error("Method not supported");
        })

        ->case(null, function() use ($out) {
            http_response_code(400);
            $out->error("Endpoint not found", 400);
        });
    }

    static public function checkWhiteList(IP $ip) {
        if(isset(self::$config->whitelist)) {
            if(!self::ipInWhiteList($ip, self::$config->whitelist)) {
                self::$oh->error("Access denied.");
            }
         }
    }

    static public function ipInWhiteList(IP $needle, array $haystack): bool {
        foreach($haystack as $ip) {
            if($needle->inRange($ip)) return true;
        }

        return false;
    }

    static public function setupSource($source) {
        if(isset(self::$config->vanityPassword) && (!isset($_POST["vanityPassword"]) || $_POST["vanityPassword"] != self::$config->vanityPassword)) {
            $source = null;
        }
        
        return $source;
    }
}