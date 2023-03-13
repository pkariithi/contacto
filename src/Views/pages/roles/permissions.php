<div class="content-page main-section">
  <div class="roles-permissions">
    <form action="roles/<?php echo $vars->role->id; ?>/permissions" method="POST">

      <?php foreach($vars->permissions as $module => $permissions): ?>
      <div>
        <label>
          <input type="checkbox" name="module" id="module_<?php echo strtolower($module); ?>" class="module_checkbox" <?php echo in_array($module, $vars->checked_modules) ? "checked='checked'" : null; ?>>
          <h3><?php echo $module ?></h3>
        </label>
        <ul>
        <?php foreach($permissions as $permission): ?>
          <li>
            <label>
              <?php $checked = in_array($permission->id, $vars->role->permissions) ? ' checked="checked"' : ''; ?>
              <input type="checkbox" name="permission[<?php echo $permission->id; ?>]" id="permission_<?php echo $permission->id; ?>" value="<?php echo $permission->id; ?>" <?php echo $checked; ?> data-pid="<?php echo $permission->id; ?>" class="permission_checkbox">
              <p><?php echo $permission->permission; ?></p>
            </label>
          </li>
        <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>

      <div class="submit">
        <button name="submit" id="submit" value="submit">Save Permissions</button>
      </div>

    </form>
  </div>
</div>

<script type="text/javascript">var permdeps = <?php echo json_encode($vars->permission_dependencies); ?></script>
