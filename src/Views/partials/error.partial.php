<div class="wrapper">
  <div class="auth-wrapper">

    <div class="auth-brand">
      <img src="assets/images/safaricom-logo.png"/>
    </div>

    <div class="auth-container">
      <div class="auth-container-header">
        <h2><?php echo $vars->error_page->title; ?></h2>
        <p><?php echo $vars->error_page->body; ?></p>
      </div>
      <?php if($vars->error_page->show_login_link): ?>
      <p class="auth-container-login-link"><a href="/">Back to Login Page</a></p>
      <?php endif; ?>
    </div>

  </div>
</div>
