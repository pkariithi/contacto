<div class="content-page main-section">
  <div class="contact-groups">
    <form action="contacts/<?php echo $vars->contact->id; ?>/groups" method="POST">

      <div>
        <ul>
        <?php foreach($vars->groups as $group): ?>
          <li>
            <label>
              <?php $checked = in_array($group->id, $vars->contact->groups) ? ' checked="checked"' : ''; ?>
              <input type="checkbox" name="group[<?php echo $group->id; ?>]" id="group_<?php echo $group->id; ?>" value="<?php echo $group->id; ?>" <?php echo $checked; ?> data-pid="<?php echo $group->id; ?>" class="group_checkbox">
              <span><strong><?php echo $group->name; ?></strong></span>
            </label>
          </li>
        <?php endforeach; ?>
        </ul>
      </div>

      <div class="submit">
        <button name="submit" id="submit" value="submit">Save Groups</button>
      </div>

    </form>
  </div>
</div>
