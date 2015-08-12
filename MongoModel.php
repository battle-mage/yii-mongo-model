<?php

/**
 * Class MongoModel
 *
 * @author Danatbek Argimbayev
 */

class MongoModel
{
    private $_attributes = array();
    private $_collection;
    public $isNewRecord = true;

    public function __construct()
    {
        $this->_collection = $this->collectionName();
    }

    public function collectionName()
    {
        return strtolower(get_called_class());
    }

    public function &__get($name)
    {
        $value = null;
        if($name==='attributes'){
            $value = $this->getAttributes();
        }elseif($name==='id'){
            if(!isset($this->_attributes['id'])){
                $attributes = $this->_attributes;
                $attributes['id'] = $this->_attributes['_id']->__toString();
                $this->_attributes = $attributes;
            }
            $value = $this->_attributes['id'];
        }
        else
            if(isset($this->_attributes[$name]))
                $value = $this->_attributes[$name];
        return $value;
    }

    public function __set($name,$value)
    {
        if($name==='attributes'){
            $this->setAttributes($value);
        }else
            if($name ==='id'){
                $attributes = $this->_attributes;
                $attributes['id'] = $value;
                $attributes['_id'] = new MongoId($value);
                $this->_attributes = $attributes;
            }else
                if(property_exists($this,$name))
                    $this->$name=$value;
                else{
                    $attributes = $this->_attributes;
                    $attributes[$name] = $value;
                    $this->_attributes = $attributes;
                }
    }

    public function __unset($name)
    {
        unset($this->_attributes[$name]);
    }

    public function __isset($name)
    {
        return isset($this->_attributes[$name]);
    }

    public function setAttributes($attributes)
    {
        $result = $this->_attributes;
        foreach($attributes as $name => $value){
            $result[$name] = $value;
        }
        $this->_attributes=$result;
    }

    public function getAttributes()
    {
        return $this->_attributes;

    }

    /**
     * @return MongoModel
     */
    public static function model()
    {
        $model_name = get_called_class();
        return new $model_name;
    }

    public function afterFind()
    {}
    public function beforeSave()
    {
        return true;
    }
    public function afterSave()
    {}
    public function beforeDelete()
    {}

    private function prepareParams($params)
    {
        $keys = array('sort');
        $init_params = array();
        foreach( $keys as $key){
            $init_params[$key] = array();
        }
        $init_params['skip'] = null;
        $init_params['limit'] = null;
        return array_merge($init_params, $params);
    }

    public function findByAttributes($query = array(), $params = array())
    {
        $params = $this->prepareParams($params);
        $client = new MongoDoc($this->collectionName());
        $cursor = $client->find($query)->limit(1)->sort($params['sort'])->skip($params['skip']);
        $document = $cursor->getNext();
        if(is_null($document)){
            return null;
        }
        $this->attributes = $document;
        $this->isNewRecord = false;
        $this->afterFind();
        return $this;
    }

    public function findByPk($id)
    {
        $client = new MongoDoc($this->collectionName());
        $document = $client->findOne(array(
            '_id' => new MongoId($id)
        ));
        if(is_null($document)){
            return null;
        }
        $this->attributes = $document;
        $this->isNewRecord = false;
        $this->afterFind();
        return $this;
    }

    public function findAllByAttributes($query = array(), $params = array())
    {
        $params = $this->prepareParams($params);
        $client = new MongoDoc($this->collectionName());
        $cursor = $client->find($query)->sort($params['sort'])->limit($params['limit'])->skip($params['skip']);
        $models = array();
        $model_name = get_called_class();
        foreach($cursor as $cur){
            $model = new $model_name;
            $model->attributes = $cur;
            $model->isNewRecord = false;
            $model->afterFind();
            $models[] = $model;
        }
        return $models;
    }

    public function delete()
    {
        if(!is_null($this->_id)){
            $client = new MongoDoc($this->collectionName());
            $this->beforeDelete();
            $client->remove(array('_id' => $this->_id));
            return true;
        }else{
            return false;
        }
    }

    public function save()
    {
        $result =false;
        if($this->beforeSave()){
            if(!is_null($this->_id)){
                $result =  $this->documentUpdate();
            }else{
                $result =  $this->documentSave();
            }
        }
        if($result){
            $this->afterSave();
        }
        return $result;

    }

    private function documentSave()
    {
        $client = new MongoDoc($this->collectionName());
//        try{
        $document = $this->attributes;
        $client->insert($document);
        $this->_id = $document['_id'];
        return true;
//        }catch (MongoException $e){
//            return false;
//        }
    }

    private function documentUpdate()
    {
        $client = new MongoDoc($this->collectionName());
        $attributes = $this->attributes;
        unset($attributes['_id']);
//        try{
        $client->update(
            array('_id' => $this->_id),
            array(
                '$set' => $attributes,
            )
        );
        return true;
//        }catch (MongoException $e){
//            return false;
//        }
    }
}