<?php

namespace App\Infrastructure\Utils;

/**
 * Read a simple JSON configuration file.
 */
class Properties
{
    /**
     * \stdClass JSON conf.
     */
    private $conf;

    private $rootDir;

    private static $instance;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->conf = json_decode('{}');
    }

    /**
     * Read an integer property.
     *
     * @param string $key     : key
     * @param int    $default : default value if configuration is empty
     *
     * @return int value
     */
    public function getInteger(string $key, int $default = 0): int
    {
        $result = $default;

        if (!empty($this->getConf()->{$key})) {
            if (\is_string($this->getConf()->{$key})) {
                $result = (int) $this->getConf()->{$key};
            } else {
                $result = $this->getConf()->{$key};
            }
        }

        return $result;
    }

    /**
     * Read a boolean property.
     *
     * @param string $key     : key
     * @param bool   $default : default value if configuration is empty
     *
     * @return bool value
     */
    public function getBoolean(string $key, bool $default): bool
    {
        $result = $default;

        if (!empty($this->getConf()->{$key})) {
            // if else with 'true' and 'false' string values :
            // it allow to use a default value
            if ('true' === $this->getConf()->{$key}) {
                $result = true;
            } elseif ('false' === $this->getConf()->{$key}) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Read a boolean property.
     *
     * @param string $key : key
     *
     * @return string value
     */
    public function getString(string $key): string
    {
        $result = '';

        if (!empty($this->getConf()->{$key})) {
            $result = $this->getConf()->{$key};
        }

        return $result;
    }

    public function getArray(string $key): array
    {
        $result = [];
        if (!empty($this->getConf()->{$key})) {
            $result = $this->getConf()->{$key};
        }

        return $result;
    }

    /**
     * Read a JSON configuration file.
     *
     * @param string $file : file
     */
    public function loadConf(string $file)
    {
        if (\file_exists($file)) {
            $this->setConf(json_decode(file_get_contents($file)));
        } else {
            throw new \Exception('conf file not found '.$file);
        }
    }

    public function loadRecordConf(string $rootDir, string $file)
    {
        $this->rootDir = $rootDir;

        if (\file_exists($file)) {
            $this->setConf(json_decode(file_get_contents($file)));
        } else {
            throw new \Exception('conf file not found '.$file);
        }
    }

    public function setConf(\stdClass $conf)
    {
        $this->conf = $conf;
    }

    /**
     * get JSON conf.
     *
     * @return \stdClass JSON conf
     */
    public function getConf(): \stdClass
    {
        return $this->conf;
    }

    /**
     * initialize.
     */
    public static function init(string $rootDir, string $file)
    {
        if (!isset(self::$instance)) {
            self::$instance = new Properties();
            self::$instance->rootDir = \realpath($rootDir);
            self::$instance->loadConf($file);
        }

        return self::$instance;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Get main working directory.
     *
     * @return string rootDir main working directory
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * Get public directory.
     *
     * @return string publicdir main public directory
     */
    public function getPublicDirPath(): string
    {
        return $this->getRootDir().$this->getConf()->{'publicdir'};
    }
}
