<?php

namespace SMVC\Model;

use SMVC\Core\Model;

class Role extends Model {

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'Role';
    $this->table = 'roles';
    $this->base_url_path = 'roles';

    // table columns including joined ones
    $this->columns = [
      $this->table.'.id',
      $this->table.'.role',
      $this->table.'.description',
      'status.status',
      'creator.username AS created_by',
      $this->table.'.created_at',
      'updator.username AS updated_by',
      $this->table.'.updated_at',
    ];

    // table columns to display on the UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'role' => ['table'=>$this->table, 'label'=>'Role'],
      'description' => ['table'=>$this->table, 'label'=>'Description'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At']
    ];

    // columns to filter by
    $this->columnsToSearch = [
      $this->table.'.role',
      $this->table.'.description',
      'status.status',
      'creator.username',
      $this->table.'.created_at',
      'updator.username',
      $this->table.'.updated_at',
    ];

    $this->joins = [
      'inner' => [
        "status ON {$this->table}.status_id = status.id",
        "users creator ON {$this->table}.created_by = creator.id",
        "users updator ON {$this->table}.updated_by = updator.id"
      ]
    ];

    // form to add / edit records
    $this->form['resource'] = [
      'role' => [
        'label'=>'Role',
        'helptext'=>'A simple descriptive name.',
        'placeholder' => 'The role\'s name',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The role is required'
            ]
          ]
        ]
      ],
      'description' => [
        'label'=>'Description (Optional)',
        'helptext'=>'A description of the role\'s purpose.',
        'placeholder' => 'The role\'s description',
        'type' => 'text',
        'validate' => [
          'rules' => []
        ]
      ],
      'submit' => [
        'new' => ['label' => 'Add Role'],
        'edit' => ['label' => 'Save Role'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to delete records
    $this->form['delete'] = [
      'submit' => [
        'delete' => ['label' => 'Delete Role'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to enable records
    $this->form['enable'] = [
      'submit' => [
        'enable' => ['label' => 'Enable Role'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to disable records
    $this->form['disable'] = [
      'submit' => [
        'disable' => ['label' => 'Disable Role'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // links and buttons, with their respective permission
    $this->links = [
      'header' => [
        [
          'name' => 'new',
          'label' => 'Add New Role',
          'href' => $this->base_url_path.'/new',
          'permissions' => ['Can add roles']
        ],
        [
          'name' => 'export',
          'label' => 'Export All Roles',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export roles']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view roles']
        ],
        [
          'name' => 'edit',
          'label' => 'Edit',
          'icon' => 'edit',
          'href' => $this->base_url_path.'/{id}/edit',
          'permissions' => ['Can edit roles']
        ],
        [
          'name' => 'delete',
          'label' => 'Delete',
          'icon' => 'delete',
          'href' => $this->base_url_path.'/{id}/delete',
          'permissions' => ['Can delete roles']
        ],
        [
          'name' => 'enable',
          'label' => 'Enable',
          'icon' => 'enable',
          'href' => $this->base_url_path.'/{id}/enable',
          'permissions' => ['Can enable roles']
        ],
        [
          'name' => 'disable',
          'label' => 'Disable',
          'icon' => 'disable',
          'href' => $this->base_url_path.'/{id}/disable',
          'permissions' => ['Can disable roles']
        ]
      ],
      'detail' => [
        'SMVC-detail-buttons-left' => [
          [
            'name' => 'edit',
            'label' => 'Edit',
            'icon' => 'edit',
            'href' => $this->base_url_path.'/{id}/edit',
            'permissions' => ['Can edit roles']
          ],
          [
            'name' => 'delete',
            'label' => 'Delete',
            'icon' => 'delete',
            'href' => $this->base_url_path.'/{id}/delete',
            'permissions' => ['Can delete roles']
          ],
          [
            'name' => 'enable',
            'label' => 'Enable',
            'icon' => 'enable',
            'href' => $this->base_url_path.'/{id}/enable',
            'permissions' => ['Can enable roles']
          ],
          [
            'name' => 'disable',
            'label' => 'Disable',
            'icon' => 'disable',
            'href' => $this->base_url_path.'/{id}/disable',
            'permissions' => ['Can disable roles']
          ]
        ],
        'SMVC-detail-buttons-right' => [
          [
            'name' => 'permissions',
            'label' => 'Manage Permissions',
            'icon' => 'manage-permissions',
            'href' => $this->base_url_path.'/{id}/permissions',
            'permissions' => ['Can manage role permissions']
          ],
          [
            'name' => 'users',
            'label' => 'Manage Users',
            'icon' => 'manage-users',
            'href' => $this->base_url_path.'/{id}/users',
            'permissions' => ['Can manage role users']
          ]
        ]
      ]
    ];

  }

  public function fetchRole($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchRoles($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function newRole($role, $description, $user_id) {
    return $this->insert(
      [
        'role' => $role,
        'description' => $description,
        'status_id' => Status::ACTIVE_STATUS,
        'created_by' => $user_id,
        'updated_by' => $user_id
      ]
    );
  }

  public function updateRole($values, $where, $options = []) {
    return $this->update($values, $where, $options);
  }

  public function deleteRole($where, $options = []) {
    return $this->delete($where, $options, true);
  }

  public function disableRole($where, $options = []) {
    return $this->disable($where, $options);
  }

  public function enableRole($where, $options = []) {
    return $this->enable($where, $options);
  }

  public function deleteRolePermissions($role_id) {
    $sql = "DELETE FROM role_permission_mapping WHERE role_id = :role_id";
    return $this->db->query($sql, [
      'role_id' => $role_id
    ]);
  }

  public function loadUserRoles($user_id) {
    $sql = "SELECT DISTINCT id, role FROM {$this->table} WHERE id IN (SELECT role_id FROM user_role_mapping WHERE user_id = :user_id) ORDER BY id";
    return $this->db->query($sql, ['user_id' => $user_id]);
  }

  public function saveRoles($user_id, $created_by, $roles = [], $new_roles = []) {

    if(empty($roles)) {
      return;
    }

    // delete removed roles
    $role_ids = implode(', ', $roles);
    $delete_sql = "DELETE FROM user_role_mapping WHERE user_id = :user_id AND role_id NOT IN ({$role_ids})";
    $this->db->query($delete_sql, ['user_id' => $user_id]);

    // save new roles
    if(!empty($new_roles)) {

      // insert values
      $values = [];
      foreach($new_roles as $r) {
        $values[] = "({$user_id}, {$r}, {$created_by})";
      }
      $values_sql = implode(', ', $values);

      // run query
      $insert_sql = "INSERT INTO user_role_mapping (`user_id`,`role_id`,`created_by`) VALUES {$values_sql}";
      return $this->db->query($insert_sql);
    }
  }

  public function getRolesAsOptions() {
    $roles = $this->fetchRoles(
      ['status_id'=>Status::ACTIVE_STATUS],
      ['perPage'=>'all','orderBy'=>'role']
    );

    $return = ['options' => [], 'values' => []];
    foreach($roles->rows as $role) {
      $return['options'][] = [
        'value' => $role->role,
        'label' => $role->role
      ];
      $return['values'][] = $role->role;
    }
    return (object) $return;
  }

}
