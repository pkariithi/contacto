<?php

namespace SMVC\Model;

use SMVC\Core\Model;

class Status extends Model {

  // status ids - maps to DB ids
  const ACTIVE_STATUS = 1;
  const INACTIVE_STATUS = 2;
  const CREATED_STATUS = 3;
  const DELETED_STATUS = 4;
  const DORMANT_STATUS = 5;
  const CLOSED_STATUS = 6;
  const EXPIRED_STATUS = 7;
  const LOGGED_OUT_STATUS = 8;
  const USED_STATUS = 9;

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'Status';
    $this->table = 'status';

    $this->columns = [
      $this->table.'.id',
      $this->table.'.status',
      $this->table.'.slug',
      $this->table.'.description',
      'creator.username AS created_by',
      $this->table.'.created_at',
      'updator.username AS updated_by',
      $this->table.'.updated_at',
    ];

    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'status' => ['table'=>$this->table, 'label'=>'Status'],
      'description' => ['table'=>$this->table, 'label'=>'Description'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At']
    ];

    $this->columnsToSearch = [
      $this->table.'.id',
      $this->table.'.status',
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

  public function fetchStatus($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchStatuss($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

}
