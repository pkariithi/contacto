<div class="content-page main-section">
  <div class="group-contacts">
    <form action="groups/<?php echo $vars->group->id; ?>/contacts" method="POST">

      <div>
        <ul>
        <?php foreach($vars->contacts as $contact): ?>
          <li>
            <label>
              <?php $checked = in_array($contact->id, $vars->group->contacts) ? ' checked="checked"' : ''; ?>
              <input type="checkbox" name="contact[<?php echo $contact->id; ?>]" id="contact_<?php echo $contact->id; ?>" value="<?php echo $contact->id; ?>" <?php echo $checked; ?> data-pid="<?php echo $contact->id; ?>" class="contact_checkbox">
              <span><strong><?php echo $contact->surname.' '.$contact->other_names; ?></strong></span>
            </label>
          </li>
        <?php endforeach; ?>
        </ul>
      </div>

      <div class="submit">
        <button name="submit" id="submit" value="submit">Save contacts</button>
      </div>

    </form>
  </div>
</div>
