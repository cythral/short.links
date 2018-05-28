<?php

namespace Phroses\Plugins\Cythral\ShortLinks;

use \PDO;
use \InvalidArgumentException;
use \Phroses\Database\Database;
use \Phroses\Database\Queries\UpdateQuery;
use \Phroses\Database\Queries\InsertQuery;
use \Phroses\Database\Queries\SelectQuery;

use function \Phroses\{ stringStartsWith };

class ShortLink {
    private $db;
    private $properties;

    use \Phroses\Traits\UnpackOptions;

    const REQUIRED_OPTIONS = [
        "source",
        "destination",
        "clicks"
    ];

    public function __construct(array $options) {
        $this->unpackOptions($options, $this->properties);
        $this->db = Database::getInstance();
    }

    public function __get($key) {
        return $this->properties[$key] ?? null;
    }

    public function __set($key, $value) {
        if(!in_array($key, self::REQUIRED_OPTIONS)) {
            throw new InvalidArgumentException("Property $key does not exist");
        }

        (new UpdateQuery)
            ->setTable("links")
            ->addColumns([ $key => ":val" ])
            ->addWhere("source", "=", ":source")
            ->execute([ ":val" => $value, ":source" => $this->source ]);

        $this->properties[$key] = $value;
    }

    static public function create($source = null, $destination) {
        $db = Database::getInstance();
        $i = 3;

        if(!stringStartsWith($destination, "http")) {
            $destination = "http://".$destination;
        }

        do {
            $source = $source ?? bin2hex(random_bytes(($i++ / 3) * 3));
            $db->insert("links", [ "source" => $source, "destination" => $destination ]);
        } while($db->getHandle()->errorCode() !== "00000");

        return self::find($source);
    }

    static public function find($source): ?self {
        $db = Database::getInstance();

        $info = (new SelectQuery)
            ->setTable("links")
            ->addColumns(["*"])
            ->addWhere("source", "=", ":source")
            ->execute([ ":source" => $source ])
            ->fetchAll(PDO::FETCH_ASSOC);

        return (isset($info[0])) ? new self($info[0]) : null;
    }
}