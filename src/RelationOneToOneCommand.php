<?php

namespace Commands\Relations;

use Illuminate\Console\Command;
use Commands\Relations\Traits\RelationsTrait;

class RelationOneToOneCommand extends Command
{
    use RelationsTrait;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relation:one-to-one';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create relation one to one';
    
    /**
     * Function name for relation(first model)
     * 
     * @var string
     */
    private const FIRSTRELATIONMETHOD = 'belongsTo';
    
    /**
     * Function name for relation(second model)
     * 
     * @var string
     */
    private const SECONDRELATIONMETHOD = 'hasOne';
    
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
