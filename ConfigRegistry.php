<?php
namespace vollossy\DAL;

/**
 * Class ConfigRepository
 * Репозиторий для работы с конфигурацией
 * @package vollossy\DAL
 */
class ConfigRegistry {
    private static $instance;

    /**
     * @var string
     */
    private $_dsn;

    /**
     * @var string
     */
    private $_user;

    /**
     * @var string
     */
    private $_password;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new ConfigRegistry();
        }
        return self::$instance;
    }

    /**
     * @param string $dsn
     */
    public function setDsn($dsn)
    {
        $this->_dsn = $dsn;
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->_dsn;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->_user;
    }


}