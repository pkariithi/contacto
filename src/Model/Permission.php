<?php

namespace SMVC\Model;

use SMVC\Core\Model;
use SMVC\Helpers\Utils;

class Permission extends Model {

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'Permission';
    $this->table = 'permissions';
    $this->base_url_path = 'permissions';

    $this->columns = [
      $this->table.'.id',
      $this->table.'.module_id',
      'modules.module',
      $this->table.'.permission',
      'status.status',
      'creator.username AS created_by',
      $this->table.'.created_at',
    ];

    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'module' => ['table'=>'modules', 'label'=>'Module'],
      'permission' => ['table'=>$this->table, 'label'=>'Permission'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At']
    ];

    $this->columnsToSearch = [
      'modules.module',
      $this->table.'.permission',
      'status.status',
      'creator.username',
      $this->table.'.created_at'
    ];

    $this->joins = [
      'inner' => [
        "modules ON {$this->table}.module_id = modules.id",
        "status ON {$this->table}.status_id = status.id",
        "users creator ON {$this->table}.created_by = creator.id"
      ]
    ];

    // form to enable records
    $this->form['enable'] = [
      'submit' => [
        'enable' => ['label' => 'Enable Permission'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to disable records
    $this->form['disable'] = [
      'submit' => [
        'disable' => ['label' => 'Disable Permission'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // links and buttons, with their respective permission
    $this->links = [
      'header' => [
        [
          'name' => 'export',
          'label' => 'Export All Permissions',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export permissions']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view permissions']
        ],
        [
          'name' => 'enable',
          'label' => 'Enable',
          'icon' => 'enable',
          'href' => $this->base_url_path.'/{id}/enable',
          'permissions' => ['Can enable permissions']
        ],
        [
          'name' => 'disable',
          'label' => 'Disable',
          'icon' => 'disable',
          'href' => $this->base_url_path.'/{id}/disable',
          'permissions' => ['Can disable permissions']
        ]
      ],
      'detail' => [
        'SMVC-detail-buttons-left' => [
          [
            'name' => 'enable',
            'label' => 'Enable',
            'icon' => 'enable',
            'href' => $this->base_url_path.'/{id}/enable',
            'permissions' => ['Can enable permissions']
          ],
          [
            'name' => 'disable',
            'label' => 'Disable',
            'icon' => 'disable',
            'href' => $this->base_url_path.'/{id}/disable',
            'permissions' => ['Can disable permissions']
          ]
        ],
        'SMVC-detail-buttons-right' => [
          [
            'name' => 'audit',
            'label' => 'Audit Trail',
            'href' => $this->base_url_path.'/{id}/audit',
            'permissions' => ['Can view usage audit log trail']
          ]
        ]
      ]
    ];
  }

  public function fetchPermission($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchPermissions($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function updatePermission($values, $where, $options = []) {
    return $this->update($values, $where, $options);
  }

  public function disablePermission($where, $options = []) {
    return $this->disable($where, $options);
  }

  public function enablePermission($where, $options = []) {
    return $this->enable($where, $options);
  }

  public function loadUserPermissions($user_id) {
    $role_ids_sql = "SELECT DISTINCT role_id FROM user_role_mapping WHERE user_id = :user_id";
    $role_ids_res = $this->db->query($role_ids_sql, ['user_id' => $user_id]);

    $role_ids = [];
    foreach($role_ids_res as $r) {
      $role_ids[] = $r->role_id;
    }

    if(empty($role_ids)) {
      return [];
    }

    // load other role permissions
    $role_ids = '('.implode(', ', $role_ids).')';
    $sql = "SELECT DISTINCT permission_id FROM role_permission_mapping WHERE role_id IN {$role_ids}";
    $perm_ids_res = $this->db->query($sql);

    $perm_ids = [];
    foreach($perm_ids_res as $p) {
      $perm_ids[] = $p->permission_id;
    }

    if(empty($perm_ids)) {
      return [];
    }

    // fetch permissions
    $sql = "SELECT id, permission FROM {$this->table} WHERE id IN (".implode(', ', $perm_ids).")";
    return $this->db->query($sql);
  }

  public function loadRolePermissions($role_id) {
    $sql = "SELECT id, permission FROM {$this->table} WHERE id IN (SELECT permission_id FROM role_permission_mapping WHERE role_id = :role_id)";
    return $this->db->query($sql, ['role_id' => $role_id]);
  }

  public function savePermissions($role_id, $user_id, $permissions = [], $new_permissions = []) {

    if(empty($permissions)) {
      return;
    }

    // delete removed permissions
    $perms = implode(', ', $permissions);
    $delete_sql = "DELETE FROM role_permission_mapping WHERE role_id = :role_id AND permission_id NOT IN ({$perms})";
    $this->db->query($delete_sql, ['role_id' => $role_id]);

    // save new permissions
    if(!empty($new_permissions)) {
      $values = [];
      foreach($new_permissions as $p) {
        $values[] = "({$role_id}, {$p}, {$user_id})";
      }
      $values_sql = implode(', ', $values);

      $insert_sql = "INSERT INTO role_permission_mapping (`role_id`,`permission_id`,`created_by`) VALUES {$values_sql}";
      return $this->db->query($insert_sql);
    }
  }

}
