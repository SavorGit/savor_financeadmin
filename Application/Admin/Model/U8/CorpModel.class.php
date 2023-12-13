<?php
namespace Admin\Model\U8;

class CorpModel extends BaseModel{

    protected $connection = 'DB_U8';

    protected $tableName='bd_corp';

    public function __consruct($name){
        parent::__construct();
        $this->tableName = $name;
    }

}