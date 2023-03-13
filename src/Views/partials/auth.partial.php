<div class="wrapper">
  <div class="auth-wrapper">

    <div class="auth-brand">
      <img src="assets/images/full-logo.png"/>
    </div>

    <div class="auth-container">
      <div class="auth-container-header">
        <h2><?php echo $vars->auth->title; ?></h2>
        <p><?php echo $vars->auth->subtitle; ?></p>
      </div>
      <div class="auth-container-content">
        <?php echo empty($vars->flash) ? null : $vars->flash; ?>
        <?php echo $page; ?>
      </div>
    </div>

  </div>
</div>
