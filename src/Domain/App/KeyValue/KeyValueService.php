<?php

namespace KikCMS\Domain\App\KeyValue;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class KeyValueService
{
    public function __construct(
        #[Autowire('%cms.key_value_table%')] private string $tableName,
        #[Autowire('%cms.key_value_namespace%')] private string $namespace,
        private CacheItemPoolInterface $keyValueStore,
        private Connection $connection,
        private bool $writeJsonToDb = true,
    ) {}

    public function set(string $key, mixed $value): bool
    {
        // store in cache
        $item  = $this->keyValueStore->getItem($key)->set($value);
        $saved = $this->keyValueStore->save($item);

        if ( ! $saved) {
            return false;
        }

        // store as JSON in the DB for an easy view
        $jsonValue   = json_encode($value, JSON_PRETTY_PRINT);
        $prefixedKey = $this->getPrefixedKey($key);

        if($this->writeJsonToDb) {
            $this->connection->update($this->tableName, ['item_json' => $jsonValue], ['item_id' => $prefixedKey]);
        }

        return true;
    }

    public function get(string $key): mixed
    {
        $item = $this->keyValueStore->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }

    public function exists(string $key): bool
    {
        return $this->keyValueStore->getItem($key)->isHit();
    }

    private function getPrefixedKey(string $key): string
    {
        return $this->namespace . KeyValueConfig::NS_SEPARATOR . $key;
    }
}