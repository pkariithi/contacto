<?php

namespace SMVC\Model;

use SMVC\Core\Model;
use SMVC\Model\Status;

class User extends Model {

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'User';
    $this->table = 'users';
    $this->base_url_path = 'users';

    // table columns
    $this->columns = [
      $this->table.'.id',
      $this->table.'.username',
      $this->table.'.email',
      $this->table.'.password',
      $this->table.'.login_count',
      'MAX(sessions.last_seen_at) AS last_seen_at',
      'status.status',
      $this->table.'.comments',
      'creator.username AS created_by',
      $this->table.'.created_at',
      'updator.username AS updated_by',
      $this->table.'.updated_at',
    ];

    // columns to display on UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'username' => ['table'=>$this->table, 'label'=>'Username'],
      'email' => ['table'=>$this->table, 'label'=>'Email'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'login_count' => ['table'=>$this->table, 'label'=>'Login Count'],
      'last_seen_at' => ['table'=>'sessions', 'label'=>'Last Seen'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At']
    ];

    // columns to display on UI
    $this->detail = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'username' => ['table'=>$this->table, 'label'=>'Username'],
      'email' => ['table'=>$this->table, 'label'=>'Email'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'login_count' => ['table'=>$this->table, 'label'=>'Login Count'],
      'last_seen_at' => ['table'=>'sessions', 'label'=>'Last Seen'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At'],
      'comments' => ['table'=>$this->table, 'label'=>'Comments']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      $this->table.'.username',
      $this->table.'.email',
      'status.status',
      'creator.username',
      $this->table.'.created_at',
      'updator.username',
      $this->table.'.updated_at',
      $this->table.'.comments'
    ];

    $this->joins = [
      'inner' => [
        "status ON {$this->table}.status_id = status.id",
        "users creator ON {$this->table}.created_by = creator.id",
        "users updator ON {$this->table}.updated_by = updator.id"
      ],
      'left' => [
        "sessions ON {$this->table}.id = sessions.user_id"
      ]
    ];

    $this->groupby = [
      'users.id'
    ];

    // form to add / edit records
    $this->form['resource'] = [
      'username' => [
        'label'=>'Username',
        'placeholder' => 'The user\'s username',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The username is required'
            ],
            'regex' => [
              'pattern' => '/[^a-zA-Z0-9]/',
              'msg' => 'The username has invalid characters. Allowed characters are only letters and numbers'
            ],
            'notin' => [
              'values' => '{registered_usernames}',
              'msg' => 'The username is already in use'
            ],
          ]
        ]
      ],
      'email' => [
        'label'=>'Email',
        'placeholder' => 'The user\'s email',
        'type' => 'email',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The email is required'
            ],
            'email' => [
              'msg' => 'The email is invalid'
            ],
            'notin' => [
              'values' => '{registered_emails}',
              'msg' => 'The email is already in use'
            ],
          ]
        ]
      ],
      'role' => [
        'label'=>'Role(s)',
        'placeholder' => 'Select the user\'s role(s)',
        'type' => 'checkboxes',
        'options' => '{role_options}',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'A role is required'
            ],
            'in' => [
              'values' => '{role_values}',
              'msg' => 'The role is invalid'
            ]
          ]
        ]
      ],
      'submit' => [
        'new' => ['label' => 'Add User'],
        'edit' => ['label' => 'Save User'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to edit records
    $this->form['edit'] = [
      'username' => [
        'label'=>'Username',
        'placeholder' => 'The user\'s username',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The username is required'
            ],
            'regex' => [
              'pattern' => '/[^a-zA-Z0-9]/',
              'msg' => 'The username has invalid characters. Allowed characters are only letters and numbers'
            ],
            'notin' => [
              'values' => '{registered_usernames}',
              'msg' => 'The username is already in use'
            ],
          ]
        ]
      ],
      'email' => [
        'label'=>'Email',
        'placeholder' => 'The user\'s email',
        'type' => 'email',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The email is required'
            ],
            'email' => [
              'msg' => 'The email is invalid'
            ],
            'notin' => [
              'values' => '{registered_emails}',
              'msg' => 'The email is already in use'
            ],
          ]
        ]
      ],
      'submit' => [
        'edit' => ['label' => 'Save User'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to delete records
    $this->form['delete'] = [
      'submit' => [
        'delete' => ['label' => 'Delete User'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to enable records
    $this->form['enable'] = [
      'submit' => [
        'enable' => ['label' => 'Enable User'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to disable records
    $this->form['disable'] = [
      'submit' => [
        'disable' => ['label' => 'Disable User'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to close records
    $this->form['close'] = [
      'submit' => [
        'close' => ['label' => 'Permanently Close User'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // links and buttons, with their respective permission
    $this->links = [
      'header' => [
        [
          'name' => 'new',
          'label' => 'Add New User',
          'href' => $this->base_url_path.'/new',
          'permissions' => ['Can add users']
        ],
        [
          'name' => 'export',
          'label' => 'Export All Users',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export users']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view users']
        ],
        [
          'name' => 'edit',
          'label' => 'Edit',
          'icon' => 'edit',
          'href' => $this->base_url_path.'/{id}/edit',
          'permissions' => ['Can edit users']
        ],
        [
          'name' => 'delete',
          'label' => 'Delete',
          'icon' => 'delete',
          'href' => $this->base_url_path.'/{id}/delete',
          'permissions' => ['Can delete users']
        ],
        [
          'name' => 'enable',
          'label' => 'Enable',
          'icon' => 'enable',
          'href' => $this->base_url_path.'/{id}/enable',
          'permissions' => ['Can enable users']
        ],
        [
          'name' => 'disable',
          'label' => 'Temporarily Disable User',
          'icon' => 'disable',
          'href' => $this->base_url_path.'/{id}/disable',
          'permissions' => ['Can disable users']
        ]
      ],
      'detail' => [
        'SMVC-detail-buttons-left' => [
          [
            'name' => 'edit',
            'label' => 'Edit',
            'icon' => 'edit',
            'href' => $this->base_url_path.'/{id}/edit',
            'permissions' => ['Can edit users']
          ],
          [
            'name' => 'delete',
            'label' => 'Delete',
            'icon' => 'delete',
            'href' => $this->base_url_path.'/{id}/delete',
            'permissions' => ['Can delete users']
          ],
          [
            'name' => 'enable',
            'label' => 'Enable',
            'icon' => 'enable',
            'href' => $this->base_url_path.'/{id}/enable',
            'permissions' => ['Can enable users']
          ],
          [
            'name' => 'disable',
            'label' => 'Temporarily Disable User',
            'icon' => 'disable',
            'href' => $this->base_url_path.'/{id}/disable',
            'permissions' => ['Can disable users']
          ],
          [
            'name' => 'close',
            'label' => 'Permanently Close User',
            'icon' => 'close-user',
            'href' => $this->base_url_path.'/{id}/close',
            'permissions' => ['Can close users']
          ]
        ],
        'SMVC-detail-buttons-right' => [
          [
            'name' => 'roles',
            'label' => 'Manage Roles',
            'icon' => 'manage-roles',
            'href' => $this->base_url_path.'/{id}/roles',
            'permissions' => ['Can manage user roles']
          ]
        ]
      ]
    ];

  }

  public function fetchUser($vars, $options = []) {
    $user = $this->fetchOne($vars, $options);
    if(!$user) {
      return false;
    }

    $role_model = new Role($this->app);
    $user->roles = $role_model->loadUserRoles($user->id);
    return $user;
  }

  public function fetchUsers($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function newUser($username, $email, $password = null, $role_ids = [], $created_by = 0) {
    $inserted_user_id = $this->insert(
      [
        'username' => mb_strtolower($username),
        'email' => mb_strtolower($email),
        'password' => is_null($password) ? null : password_hash($password, PASSWORD_DEFAULT),
        'status_id' => Status::CREATED_STATUS,
        'created_by' => $created_by,
        'updated_by' => $created_by
      ]
    );

    // update created by and updated by
    if($created_by == 0) {
      $this->updateUser(
        ['created_by'=>$inserted_user_id, 'updated_by'=>$inserted_user_id],
        ['id'=>$inserted_user_id]
      );
    }

    if(empty($role_ids)) {
      return $this->fetchUser(['id' => $inserted_user_id]);
    }

    // insert roles
    $values = [];
    foreach($role_ids as $r) {
      $values[] = "({$inserted_user_id}, {$r}, {$created_by})";
    }
    $values_sql = implode(', ', $values);

    // run query
    $insert_sql = "INSERT INTO user_role_mapping (`user_id`,`role_id`,`created_by`) VALUES {$values_sql}";
    $inserted = $this->db->query($insert_sql);

    if($inserted) {
      return $this->fetchUser(['id' => $inserted_user_id]);
    }
  }

  public function resetPassword($password, $user_id) {
    return $this->updateUser(
      ['password' => password_hash($password, PASSWORD_DEFAULT)],
      ['id'=>$user_id]
    );
  }

  public function updateUser($values, $where, $options = []) {
    return $this->update($values, $where, $options);
  }

  public function deleteUser($where, $options = []) {
    return $this->delete($where, $options);
  }

  public function disableUser($where, $options = []) {
    return $this->disable($where, $options);
  }

  public function enableUser($where, $options = []) {
    return $this->enable($where, $options);
  }

  public function closeUser($where, $options = []) {
    return $this->update(['status_id' => Status::CLOSED_STATUS], $where, $options);
  }

  public function deleteUserRoles($user_id) {
    $sql = "DELETE FROM user_role_mapping WHERE user_id = :user_id";
    return $this->db->query($sql, [
      'user_id' => $user_id
    ]);
  }

  public function loadUserRoles($user_id) {
    $sql = "SELECT DISTINCT id, role FROM roles WHERE id IN (SELECT role_id FROM user_role_mapping WHERE user_id = :user_id)";
    return $this->db->query($sql, ['user_id' => $user_id]);
  }

  public function loadRoleUsers($role_id) {
    $sql = "SELECT id, username, email FROM {$this->table} WHERE id IN (SELECT user_id FROM user_role_mapping WHERE role_id = :role_id)";
    return $this->db->query($sql, ['role_id' => $role_id]);
  }

  public function saveRoleUsers($role_id, $user_id, $users = [], $new_users = []) {

    if(empty($users)) {
      return;
    }

    // delete removed users
    $adms = implode(', ', $users);
    $delete_sql = "DELETE FROM user_role_mapping WHERE role_id = :role_id AND user_id NOT IN ({$adms})";
    $this->db->query($delete_sql, ['role_id' => $role_id]);

    // save new users
    if(!empty($new_users)) {
      $values = [];
      foreach($new_users as $a) {
        $values[] = "({$a}, {$role_id}, {$user_id})";
      }
      $values_sql = implode(', ', $values);

      $insert_sql = "INSERT INTO user_role_mapping (`user_id`,`role_id`,`created_by`) VALUES {$values_sql}";
      return $this->db->query($insert_sql);
    }
  }

  public function checkIfUserExists($fields = []) {
    if(empty($fields)) {
      return false;
    }

    $where = $params = [];
    if(isset($fields['username'])) {
      $where[] = "username = :username";
      $params['username'] = $fields['username'];
    }
    if(isset($fields['email'])) {
      $where[] = "email = :email";
      $params['email'] = $fields['email'];
    }
    return $this->db->query(
      "SELECT id FROM {$this->table} WHERE ".implode(" OR ", $where),
      $params
    );
  }

  public function getRegisteredUsernamesAndEmails() {
    $users = $this->fetchUsers([], ['perPage'=>'all']);
    $return = [];
    foreach($users->rows as $u) {
      $return['usernames'][] = $u->username;
      $return['emails'][] = $u->email;
    }
    return $return;
  }

}
