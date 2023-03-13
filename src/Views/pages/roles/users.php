<div class="content-page main-section">
  <div class="role-users">
    <form action="roles/<?php echo $vars->role->id; ?>/users" method="POST">

      <div>
        <ul>
        <?php foreach($vars->users as $user): ?>
          <li>
            <label>
              <?php $checked = in_array($user->id, $vars->role->users) ? ' checked="checked"' : ''; ?>
              <input type="checkbox" name="user[<?php echo $user->id; ?>]" id="user_<?php echo $user->id; ?>" value="<?php echo $user->id; ?>" <?php echo $checked; ?> data-pid="<?php echo $user->id; ?>" class="user_checkbox">
              <p><?php echo '<strong>'.$user->username.'</strong> - '.$user->email; ?></p>
            </label>
          </li>
        <?php endforeach; ?>
        </ul>
      </div>

      <div class="submit">
        <button name="submit" id="submit" value="submit">Save Users</button>
      </div>

    </form>
  </div>
</div>
