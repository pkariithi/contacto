<?php

namespace SMVC\Model;

use SMVC\Core\Model;

class Module extends Model {

  public function __construct($app) {
    parent::__construct($app);

    // module and table
    $this->module = 'Module';
    $this->table = 'modules';

    // table columns
    $this->columns = [
      $this->table.'.id',
      $this->table.'.module',
      $this->table.'.slug',
      $this->table.'.table_name',
      $this->table.'.description',
      'creator.username AS created_by',
      $this->table.'.created_at',
      'updator.username AS updated_by',
      $this->table.'.updated_at',
    ];

    // columns to display on UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'module' => ['table'=>$this->table, 'label'=>'Module'],
      'description' => ['table'=>$this->table, 'label'=>'Description'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      $this->table.'.id',
      $this->table.'.module',
      $this->table.'.slug',
      $this->table.'.description',
      'creator.username',
      $this->table.'.created_at',
      'updator.username',
      $this->table.'.updated_at',
    ];

    // mysql joins
    $this->joins = [
      'inner' => [
        "users creator ON {$this->table}.created_by = creator.id",
        "users updator ON {$this->table}.updated_by = updator.id",
      ]
    ];
  }

  public function fetchModule($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchModules($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

}
