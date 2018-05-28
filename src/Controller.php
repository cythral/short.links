<?php

namespace Phroses\Plugins\Cythral\ShortLinks;

use \Phroses\IP;
use \Phroses\JsonServer;
use \Phroses\Switcher;

class Controller {
    static public function goto($link) {
        $url = ShortLink::find($link);

        if($url) {
            $url->clicks++;

            http_response_code(301);
            header("location: {$url->destination}");
            exit;
        }
    }

    static public function api($endpoint, $method, $config) {
        $out = new JsonServer;

        (new Switcher($endpoint))

        ->case("/shorten", function() use ($out, $method, $config) {
            if(strtolower($method) == "post") {

                // IP Whitelist
                if(isset($config->whitelist)) {
                    $ok = false;
                    foreach($config->whitelist as $ip) {
                        if((new IP($_SERVER["REMOTE_ADDR"]))->inRange($ip)) {
                            $ok = true;
                            break;
                        }
                    }

                    if(!$ok) $out->error("Access denied.");
                }

                // Vanity URLs (/twitter, /facebook, etc.)
                $source = $_POST["source"] ?? null;
                if(isset($config->vanityPassword) && (!isset($_POST["vanityPassword"]) || $_POST["vanityPassword"] != $config->vanityPassword)) {
                    $source = null;
                }

                $url = ShortLink::create($source, $_POST["destination"]);
                $out->success(200, [ "link" => "/".$url->source ]);

            } else $out->error("Method not supported");
        })

        ->case(null, function() use ($out) {
            http_response_code(400);
            $out->error("Endpoint not found", 400);
        });
    }
}