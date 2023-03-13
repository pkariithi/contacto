<?php

namespace SMVC\Core;

use SMVC\Model\Role;
use SMVC\Model\Permission;

class Rbac {

  protected $app;
  public $roles;
  public $permissions;

  public function __construct($app) {
    $this->app = $app;
  }

  public function loadPermissions($user_id) {

    // load roles
    $role_model = new Role($this->app);
    $roles = $role_model->loadUserRoles($user_id);

    $ids = $names = [];
    foreach($roles as $role) {
      $ids[] = $role->id;
      $names[] = $role->role;
    }

    $this->roles = (object) [
      'roles' => $roles,
      'ids' => $ids,
      'names' => $names
    ];

    // load permissions
    $permission_model = new Permission($this->app);
    $permissions = $permission_model->loadUserPermissions($user_id);

    $ids = $names = [];
    foreach($permissions as $permission) {
      $ids[] = $permission->id;
      $names[] = $permission->permission;
    }

    $this->permissions = (object) [
      'permissions' => $permissions,
      'ids' => $ids,
      'names' => $names
    ];
  }

  public function hasPermission($permissions) {
    if(!isset($this->permissions->names)) {
      return false;
    }

    if(!is_array($permissions)) {
      $permissions = [$permissions];
    }
    return !empty(array_intersect($this->permissions->names, $permissions));
  }

}
