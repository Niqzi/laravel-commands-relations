<?php

namespace Commands\Relations;

use Illuminate\Console\Command;
use Commands\Relations\Traits\RelationsTrait;

class RelationManyToManyCommand extends Command
{
    use RelationsTrait;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relation:many-to-many';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create relation many to many';
    
    /**
     * Function name for relation(first model)
     * 
     * @var string
     */
    private const FIRSTRELATIONMETHOD = 'belongsToMany';
    
    /**
     * Function name for relation(second model)
     * 
     * @var string
     */
    private const SECONDRELATIONMETHOD = self::FIRSTRELATIONMETHOD;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {   
        parent::__construct(); 
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        $this->applying();
    }
          
}
