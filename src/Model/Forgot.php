<?php

namespace SMVC\Model;

use SMVC\Core\Model;

class Forgot extends Model {

  public function __construct($app) {
    parent::__construct($app);

    // module and table
    $this->module = 'Forgot';
    $this->table = 'forgot';

    // table columns
    $this->columns = [
      $this->table.'.id',
      $this->table.'.token',
      'user.username',
      $this->table.'.comments',
      'status.status',
      'creator.username AS created_by',
      $this->table.'.created_at',
      'updator.username AS updated_by',
      $this->table.'.updated_at',
      $this->table.'.used_at',
    ];

    // columns to display on UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'username' => ['table'=>'user', 'label'=>'Username'],
      'comments' => ['table'=>$this->table, 'label'=>'Comments'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At'],
      'used_at' => ['table'=>$this->table, 'label'=>'Used At']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      $this->table.'.id',
      'user.username',
      $this->table.'.comments',
      'creator.username',
      $this->table.'.created_at',
      'updator.username',
      $this->table.'.updated_at',
      $this->table.'.used_at',
    ];

    // mysql joins
    $this->joins = [
      'inner' => [
        "status ON {$this->table}.status_id = status.id",
        "users user ON {$this->table}.user_id = user.id",
        "users creator ON {$this->table}.created_by = creator.id",
        "users updator ON {$this->table}.updated_by = updator.id",
      ]
    ];
  }

  public function fetchForgot($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchForgots($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function newForgot($user_id, $token, $created_by = null) {
    return $this->insert(
      [
        'user_id' => $user_id,
        'token' => $token,
        'status_id' => Status::ACTIVE_STATUS,
        'created_by' => $created_by ?? $user_id,
        'updated_by' => $created_by ?? $user_id
      ]
    );
  }

  public function updateForgot($values, $where, $options = []) {
    return $this->update($values, $where, $options);
  }
}
