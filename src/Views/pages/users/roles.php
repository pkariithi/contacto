<div class="content-page main-section">
  <div class="users-roles">
    <form action="users/<?php echo $vars->user->id; ?>/roles" method="POST">

      <div>
        <ul>
        <?php foreach($vars->roles as $role): ?>
          <li>
            <label>
              <?php $checked = in_array($role->id, $vars->user->roles) ? ' checked="checked"' : ''; ?>
              <input type="checkbox" name="role[<?php echo $role->id; ?>]" id="role_<?php echo $role->id; ?>" value="<?php echo $role->id; ?>" <?php echo $checked; ?> data-pid="<?php echo $role->id; ?>" class="role_checkbox">
              <span><strong><?php echo $role->role.'</strong> - '.$role->description; ?></span>
            </label>
          </li>
        <?php endforeach; ?>
        </ul>
      </div>

      <div class="submit">
        <button name="submit" id="submit" value="submit">Save Roles</button>
      </div>

    </form>
  </div>
</div>
