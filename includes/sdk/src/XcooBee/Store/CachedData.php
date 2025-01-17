<?php

namespace XcooBee\Store;

use Stash\Pool;
use Stash\Driver\FileSystem;

use XcooBee\Exception\XcooBeeException;

class CachedData
{
    const CONFIG_KEY = "CONFIG_KEY";
    const CURRENT_USER_KEY = "CURRENT_USER";
    const AUTH_TOKEN_KEY = "AUTH_TOKEN";
    const CONSENT_KEY = "CONSENT_KEY_";

    protected static $_instance = null;

    protected $_store = null;

    public function __construct(FileSystem $driver = null)
    {
        if ($driver !== null) {
            $this->_store = new Pool($driver);
        }
    }

    /**
     * Returns instance of CachedData
     *
     * @return null|CachedData
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self(self::_getDriver());
        }

        return self::$_instance;
    }

    /**
     * Creates cache filesystem driver
     *
     * @return FileSystem
     *
     * @throws XcooBeeException
     */
    private static function _getDriver() {
        try {
            // try use root of sdk as cache folder
            return new FileSystem(['path' => __DIR__ . '/../../../cache']);
        } catch (\Exception $ex) {
            // in case when no permission, try to use system tmp folder
            try {
                return new FileSystem([]);
            } catch (\Exception $ex) {
                // in case we weren't able to use filesystem, don't use cache at all
                return null;
            }
        }
    }

    /**
     * Setting data to storage
     *
     * @param $key
     * @param $value
     */
    public function setStore($key, $value)
    {
        if ($this->_store === null) {
            return null;
        }

        $item = $this->_store->getItem(md5($key));
        $this->_store->save($item->set($value));
    }

    /**
     * Getting data from storage
     *
     * @param $key
     * @return mixed
     */
    public function getStore($key)
    {
        if ($this->_store === null) {
            return null;
        }

        $item = $this->_store->getItem(md5($key));
        return $item->get();
    }

    /**
     * Remove all data from storage
     */
    public function clearStore()
    {
        if ($this->_store === null) {
            return null;
        }

        $this->_store->clear();
    }

    /**
     * setting consent data
     *
     * @param String $consentId
     */
    public function setConsent($consentId, $consent)
    {
        $this->setStore(self::CONSENT_KEY . $consentId, $consent);
    }

    /**
     * Getting consent data from storage
     *
     * @param String $consentId
     * @return mixed
     */
    public function getConsent($consentId)
    {
        return $this->getStore(self::CONSENT_KEY . $consentId);
    }
}
