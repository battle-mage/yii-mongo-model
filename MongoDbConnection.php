<?php

/**
 * Class MongoDbConnection
 *
 * Singleton to connect MongoDB
 * @author Danatbek Argimbayev
 */
class MongoDbConnection extends CApplicationComponent
{

    private $_username;
    private $_password;
    private $_db;
    private $_host;
    private $_mongo;

    public function init(){
        parent::init();

        $this->connect();
    }

    public function connect(){

        if(is_null($this->_mongo)) {
            try {
                $params = array();

                if(!empty($this->_username)) {
                    $params["username"] = $this->_username;
                }

                if(!empty($this->_db)) {
                    $params["db"]=$this->_db;
                }

                if(!empty($this->_password)) {
                    $params["password"]=$this->_password;
                }

                $this->_mongo = new MongoClient("mongodb://{$this->_host}", $params);
            } catch(MongoConnectionException $e) {
                throw new CDbException("Can't connect to Mongo DB <br>".$e->getMessage());
            }
        }
    }

    public function getMongo()
    {
        return $this->_mongo;
    }

    protected function setDb($config)
    {
        $this->_db = $config;
    }

    public function getDb()
    {
        $db = $this->_db;

        return $this->_mongo->$db;
    }

    protected function setUsername($config)
    {
        $this->_username = $config;
    }

    protected function getUsername()
    {
        return $this->_username;
    }

    protected function setPassword($config)
    {
        $this->_password = $config;
    }

    protected function getPassword()
    {
        return $this->_password;
    }

    protected function setHost($config)
    {
        $this->_host = $config;
    }

    protected function getHost()
    {
        return $this->_host;
    }
} 