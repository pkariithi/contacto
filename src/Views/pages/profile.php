<div class="content-page main-section">
  <div class="profile-page">

    <div class="profile-page-block profile-page-initials">
      <div class="profile-page-initials-avatar">
        <span class="initials"><?php echo $vars->profile->initials; ?></span>
      </div>
      <div class="profile-page-initials-details">
        <h3><?php echo $vars->profile->username; ?></h3>
        <h4><?php echo $vars->profile->email; ?></h4>
      </div>
    </div>

    <div class="profile-page-block profile-page-list profile-page-details">
      <h3>Details:</h3>
      <ul>
        <li><strong>Status:</strong> <?php echo $vars->profile->status; ?></li>
        <li><strong>Created at:</strong> <?php echo $vars->profile->created_at; ?></li>
        <li><strong>Created by:</strong> <?php echo $vars->profile->created_by; ?></li>
        <li><strong>Number of logins:</strong> <?php echo $vars->profile->login_count; ?></li>
        <li><strong>Last seen at:</strong> <?php echo $vars->profile->last_seen_at; ?></li>
      </ul>
    </div>

    <div class="profile-page-block profile-page-list profile-page-roles">
      <h3>Roles:</h3>
      <ul>
        <?php foreach($vars->profile->roles as $role): ?>
          <li><?php echo $role->role; ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>
</div>
