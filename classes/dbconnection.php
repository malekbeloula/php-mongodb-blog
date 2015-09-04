<?php

class DBConnection {
    const HOST = 'localhost';
    const PORT = 27017;
    const DBNAME = 'mongo_blog';
    private static $instance;
    public $connection;
    public $databse;
    private function __construct() {
        $connectionString = sprintf('mongodb://%s:%d', DBConnection::HOST, DBConnection::PORT);
        try {
            $this->connection = new Mongo($connectionString);
            $this->database = $this->connection->selectDB(DBConnection::DBNAME);
        } catch (MongoConnectionException $e) {
            throw $e;
        }
    }
    
    static public function singleton() {
        if(!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }
    
    public function __clone(){
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    
    public function __wakeup(){
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }
    
    public function getCollection($name) {
        return $this->database->selectCollection($name);
    }
    
    // CRUD operations for the article item
    public function getById($id, $collection){
        // Convert strings of right length to MongoID
        if (strlen($id) == 24) {
            $id = new \MongoId($id);
        }
        $table = $this->getCollection($collection);
        $cursor = $table->find(array('_id' => $id));
        $article = $cursor->getNext();

        if (!$article) {
            return false;
        }
        return $article;
    }
    
    /**
     * Create article
     * @return boolean
     */
    public function create($collection, $article) {

        $table = $this->getCollection($collection);
        return $result = $table->insert($article);
    }
    
    /**
     * delete article via id
     * @return boolean
     */
    public function delete($id, $collection) {
        // Convert strings of right length to MongoID
        if (strlen($id) == 24) {
            $id = new \MongoId($id);
        }
        $table = $this->getCollection($collection);
        $result = $table->remove(array('_id' => $id));
        if (!$id) {
            return false;
        }
        return $result;
    }
    
    /**
     * 
     * @param type $collection
     * @param type $limit
     * @return type $array
     */
    public function getLatestArticles($collection, $limit){
        $table = $this->getCollection($collection);
        $result = $table->find(array(), array('_id', 'title', 'published_at', 'description'))
                ->sort(array('published_at', -1))
                ->limit($limit);
        return $result;
    }
}