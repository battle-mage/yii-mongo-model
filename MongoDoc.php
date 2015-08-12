<?php

/**
 * Class MongoDoc
 *
 * Proxy class for MongoClient
 * @author Danatbek Argimbayev
 *
 */
class MongoDoc{
    private $_connection;
    private $_collection;
    private $_db;

    public function __construct($collection=""){
        $con=Yii::app()->getComponent('mongodoc');
        if(!empty($collection))$this->_collection=$collection;
        $this->_db=$con->getDb();
        $this->_connection=$con->getMongo();
    }

    public function __call($name, $arguments) {
        $collection=$this->_collection;
        return call_user_func_array(array($this->_db->$collection,$name),$arguments);
    }

    public function toArray($cursor){
        $doc=array();
        foreach($cursor as $cur){
            $doc[]=$cur;
        }
        return $doc;
    }

    public function on($collection){
        $this->_collection=$collection;
        return $this;
    }

} 