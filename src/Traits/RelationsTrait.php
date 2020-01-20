<?php

namespace Commands\Relations\Traits;

use Commands\Relations\{RelationOneToOneCommand, RelationOneToManyCommand, RelationManyToManyCommand};
use Illuminate\Database\Eloquent\Relations\{hasOne, belongsTo, hasMany, belongsToMany};
use DB, Exception, ReflectionClass, Schema;

trait RelationsTrait 
{    
    /**
     * First model name
     * 
     * @var string
     */
    private $firstModel;
    
    /**
     * Second model name
     * 
     * @var string
     */
    private $secondModel;
    
    /**
     * Potential values: existing and created
     * 
     * @var string
     */
    private $relation;
    
    /**
     * Model folder
     * 
     * @var string
     */
    private static $folder = 'App\\';
    
    /**
     * File type
     * 
     * @var string
     */
    private static $php = '.php';
    
    private function applying(): void
    {
        try{  
            $firstModel = $this->ask('Enter a first model');
            if(true === $this->hasModel($firstModel)){
                $secondModel = $this->ask('Enter a second model');
                if(true === $this->hasModel($secondModel)){ 
                    if($this->firstModel === $this->secondModel){
                        $this->error('The models are the same');
                        return;
                    }
                    if(false === $this->hasColumnRelation($this->firstModel, $this->secondModel)){
                        $this->error('Link fields missing');
                        return;
                    }
                    // first model checks and actions
                    $isFirstRelation = $this->hasRelation($this->firstModel, $this->secondModel, self::FIRSTRELATIONMETHOD);
                    if(false === $isFirstRelation){ 
                        $this->createRelation($this->firstModel, $this->secondModel, self::FIRSTRELATIONMETHOD);
                        $this->info("$this->firstModel relation method created: $this->relation");
                    }else{
                        $this->info("$this->firstModel relation method existing: $this->relation");
                    }
                    // second model checks and actions    
                    $isSecondRelation = $this->hasRelation($this->secondModel, $this->firstModel, self::SECONDRELATIONMETHOD);
                    if(false === $isSecondRelation){ 
                        $this->createRelation($this->secondModel, $this->firstModel, self::SECONDRELATIONMETHOD);
                        $this->info("$this->secondModel relation method created: $this->relation");
                    }else{
                        $this->info("$this->secondModel relation method existing: $this->relation");
                    }
                }
            }
        }catch(Exception $e){
            $this->error("An error occurred");
        }
    }        
    
    private function hasColumnRelation(string $fModel, string $sModel): bool
    {   
        switch ($this){
            
            case $this instanceof RelationOneToOneCommand:
                $obj = $this->getObject($fModel);
                return $this->hasRelationField($obj->getTable(), $sModel); 
                
            case $this instanceof RelationOneToManyCommand:
                $obj = $this->getObject($sModel);
                return $this->hasRelationField($obj->getTable(), $fModel);
                
            case $this instanceof RelationManyToManyCommand:
                $table = strtolower($fModel.'_'.$sModel);
                $isTbl = $this->hasTable($table);
                if(false === $isTbl){
                    $table = strtolower($sModel.'_'.$fModel);
                    $isTbl_ = $this->hasTable($table);
                    if(false === $isTbl_){
                        $this->error('Missing link table');
                        return false;
                    }
                }
                return $this->hasRelationField($table, $sModel) && $this->hasRelationField($table, $fModel);
            
            default:
                return false;
        }
    }
    
    private function hasRelationField(string $table, string $model): bool
    {
        foreach (DB::select("describe $table") as $field){
                if(strpos($field->Type, 'unsigned') && false !== strpos($field->Field, strtolower($model))){
                    return true;
                }
            }
        return false;
    }
    
    private function hasRelation(string $fModel, string $sModel, string $type): bool
    {
        $relations = $this->getRelations($fModel); 
        if(!empty($relations)){
            foreach($relations as $relation){ 
                if($relation['class'] === self::$folder.$sModel && $relation['type'] === $type){
                    $this->relation = $relation['relation'];
                    return true;
                } 
            }
        }
        return false;
    }
    
    private function hasModel(string $model): bool
    {   
        $files = glob(self::$folder.'*'.self::$php);
        if(in_array(self::$folder.$model.self::$php, $files)){
            if(empty($this->firstModel)){
                $this->firstModel = $model;
            }
            if(!empty($this->firstModel)){
                $this->secondModel = $model;
            }
            return true;
        }
        $this->error("Note model: $model");
        return false;
    }
    
    private function createRelation(string $sModel, string $fModel, string $relation): void
    { 
        $model = lcfirst($fModel);
        $name_ = in_array($relation, ['belongsTo', 'hasOne']) ? $model : $model.'s';
        $name = method_exists(self::$folder.$sModel, $name_) ? $name_.'_' : $name_;
        $content ='
    /**
     * @return \Illuminate\Database\Eloquent\Relations\\'.$relation.'
     */
    public function '.$name.'()
    {      
        return $this->'.$relation.'('.$fModel.'::class);
    }'; 
        $file = fopen(self::$folder.$sModel.self::$php, "r+");
        fseek($file, -2, SEEK_END);
        fwrite($file, "\n$content\n}"); 
        fflush($file);
        fclose($file);
        $this->relation = $name;
    }       
    
    private function getRelations(string $model): array
    { 
        $relations = [];
        $class = self::$folder.$model;
        $obj = new $class();
        $reflection = new ReflectionClass($class);
        foreach($reflection->getMethods() as $method){
            $mName = $method->getName();
            if($class === $method->getDeclaringClass()->getName() && 0 === $method->getNumberOfParameters()){
                if(is_object($obj->$mName()) && true === $this->hasRelationType($obj->$mName())){ 
                    $strType = explode('\\', get_class($obj->$mName()));
                    $relations [] = [
                        'relation' => $method->getName(),
                        'class' => get_class($obj->$mName()->getRelated()),
                        'type' => lcfirst($strType[count($strType)-1])
                    ]; 
                }
            }
        }
        return $relations;
    }
    
    private function hasRelationType(object $type): bool
    {
        switch($type){
            case $type instanceof hasOne: 
                return true;
            case $type instanceof belongsTo: 
                return true;
            case $type instanceof hasMany: 
                return true;    
            case $type instanceof belongsToMany: 
                return true;
            default:
                return false;
        }
    }
    
    private function getObject(string $model): object
    {
        $class = self::$folder.$model;
        return new $class;
    }
    
    private function hasTable(string $table): bool
    {
        if(true === Schema::hasTable($table)){
            return true;
        }
        return false;
    }
}