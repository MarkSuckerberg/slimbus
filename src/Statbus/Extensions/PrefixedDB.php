<?php

namespace Statbus\Extensions;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\EasyStatement;
use ParagonIE\EasyDB\Factory;
use stdClass;

class PrefixedDB extends EasyDB
{
    private $prefix;

    public function __construct(
        string $dsn,
        string $username = null,
        string $password = null,
        array $options = [],
        string $prefix = ""
    ) {
        $this->prefix = $prefix;

        $inner = Factory::create($dsn, $username, $password, $options);

        parent::__construct($inner->pdo, $inner->dbEngine, $inner->options);
    }

    private function prefixStatement(string $statement): string
    {
        return str_replace(
            'tbl_',
            $this->prefix,
            $statement
        );
    }

    public function safeQuery(
        string $statement,
        array $params = [],
        int $fetchStyle = EasyDB::DEFAULT_FETCH_STYLE,
        bool $returnNumAffected = false,
        bool $calledWithVariadicParams = false
    ): array|int|object {
        return parent::safeQuery($this->prefixStatement($statement), $params, $fetchStyle, $returnNumAffected, $calledWithVariadicParams);
    }

    public function single(string $statement, array $params = []): bool|float|int|string|null
    {
        return parent::single($this->prefixStatement($statement), $params);
    }

    public function column(string $statement, array $params = [], int $offset = 0): array|bool
    {
        return parent::column($this->prefixStatement($statement), $params, $offset);
    }

    public function escapeLikeValue(string $value): string
    {
        return parent::escapeLikeValue($this->prefixStatement($value));
    }

    public function escapeIdentifier(string $string, bool $quote = true): string
    {
        return parent::escapeIdentifier($this->prefixStatement($string), $quote);
    }

    public function delete(string $table, EasyStatement|array $conditions): int
    {
        return parent::delete($this->prefixStatement($table), $conditions);
    }

    public function row(string $statement, ...$params): array
    {
        /**
         * @var array|int $result
         */
        $result = $this->safeQuery(
            $statement,
            $params,
            self::DEFAULT_FETCH_STYLE,
            false,
            true
        );
        if (is_array($result)) {
            $first = array_shift($result);
            if (is_null($result)) {
                /* Do not TypeError on empty results */
                return [];
            }
            if (is_object($first)) {
                return get_object_vars($first);
            }
            return $first;
        }
        return [];
    }

    public function rowObj(string $statement, ...$params): stdClass
    {
        /**
         * @var array|int $result
         */
        $result = $this->safeQuery(
            $statement,
            $params,
            self::DEFAULT_FETCH_STYLE,
            false,
            true
        );
        if (is_array($result)) {
            $first = array_shift($result);
            if (is_null($result)) {
                /* Do not TypeError on empty results */
                return new stdClass;
            }
            if (is_object($first)) {
                return $first;
            }
            return $first;
        }
        return new stdClass;
    }
}