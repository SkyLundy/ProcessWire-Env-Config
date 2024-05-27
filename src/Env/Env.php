<?php

declare(strict_types=1);

/**
 * Parses, caches, and loads .env file variables
 */

namespace App\Env;

use Dotenv\Dotenv;
use ProcessWire\Config;
use RuntimeException;

class Env
{
    private const CACHE_FILE = '/cache/env.php';

    /**
     * Memoized keys/values
     */
    private array $memData = [];

    /**
     * Values that are utomatically cast to booleans when .env is parsed
     */
    private array|false $boolCasts = ['true', 'false', '0', '1'];

    private function __construct(
        bool $createEnvVars,
        bool|array $castBools,
        private bool $castInts
    ) {
        if ($castBools === false) {
            $this->boolCasts = [];
        }

        if (is_array($castBools)) {
            $this->boolCasts = $castBools;
        }

        $this->memData = self::loadEnv($createEnvVars);
    }

    /**
     * Initialization method
     *
     * Load env config from cached file or .env if cache does not exist
     * @param  bool $createEnvVars Create server env variables, otherwise use getter only
     */
    public static function load(
        bool $createEnvVars = false,
        bool|array $castBools = true,
        bool $castInts = true
    ): self {
        return new self($createEnvVars, $castBools, $castInts);
    }

    /**
     * Retreives values by key
     * @throws RuntimeException
     */
    public function get(string $key): mixed
    {
        !array_key_exists($key, $this->memData) && throw new RuntimeException(
            "The {$key} environment variable does not exist or could not be loaded"
        );

        return $this->memData[$key];
    }

    /**
     * Shorthand support method for configuring ProcessWire using property/env variable mapping
     *
     * @param  Config                 $processWireConfig The ProcessWire config object
     * @param  array<string, string>  $associations      Key: ProcessWire config property, Value: env var
     */
    public function pushToConfig(Config $processWireConfig, array $associations = []): Config
    {
        foreach ($associations as $configProperty => $envVar) {
            $processWireConfig->{$configProperty} = $this->get($envVar);
        }

        return $processWireConfig;
    }

    /**
     * Get all as an array
     */
    public function toArray(): array
    {
        return $this->memData;
    }

    /**
     * Deletes cached .env if it exists
     */
    public static function clearCache(): void
    {
        if (!file_exists(__DIR__ . self::CACHE_FILE)) {
            return;
        }

        unlink(__DIR__ . self::CACHE_FILE);
    }

    /**
     * Loads data from cache, falls back to loading .env then caches
     */
    private function loadEnv(bool $createEnvVars): ?array
    {
        $envVars = self::getCachedEnv();

        if (!$envVars) {
            $envVars = self::getFromEnv();
            $envVars = $this->castValues($envVars);
        }

        self::saveToCache($envVars);

        // Load to actual ENV vars if set to
        $createEnvVars && $this->pushToEnvironment($envVars);

        return $envVars;
    }

    /**
     * Creates actual environment variables accessible by $_ENV[]
     */
    private function pushToEnvironment(array $envVars): void
    {
        foreach ($envVars as $key => $value) {
            $_ENV[$key] = $value;
        }
    }

    /**
     * Casts bool and int values retreived as strings to their correct type
     */
    private function castValues(array $envVars): array
    {
        if (!$this->boolCasts && !$this->castInts) {
            return $envVars;
        }

        // Convert booleans and integers if configured to
        array_walk($envVars, function(&$value) {
            if (
                in_array($value, $this->boolCasts)
            ) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                return;
            }

            if ($this->castInts) {
                $ints = preg_replace('/[^\d]/', '', $value);

                // Check length of value with ints removed, if same length then convert value to int
                if (is_string($value) && strlen($ints) === strlen($value)) {
                    $value = (int) $value;
                }
            }
        });

        return $envVars;
    }

    /**
     * Load cached values by including the PHP as cache file
     */
    private function getCachedEnv(): ?array
    {
        if (!file_exists(__DIR__ . self::CACHE_FILE)) {
            return null;
        }

        return require_once __DIR__ . self::CACHE_FILE;
    }

    /**
     * New cache who dis
     */
    private function saveToCache(array $envVars): void
    {
        file_put_contents(
            __DIR__ . self::CACHE_FILE,
            '<?php return '. var_export($envVars, true) . ';' . PHP_EOL
        );
    }

    /**
     * Parse the .env file and return as associative array
     */
    private function getFromEnv(): array
    {
        return Dotenv::createArrayBacked(__DIR__ . '/../../')->load();
    }

    /**
     * Hey cache, you there?
     */
    private function cachedConfigExists(): bool
    {
        return file_exists(__DIR__ . self::CACHE_FILE);
    }
}
