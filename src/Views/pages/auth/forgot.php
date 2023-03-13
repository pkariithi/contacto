<form action="" method="POST">
  <div class="form-group">
    <label for="email">Email Address:</label>
    <input type="text" name="email" id="email" placeholder="Your email address" value="<?php echo $vars->post->auth->email ?? null; ?>">
  </div>
  <div class="form-footer">
    <button type="submit" name="submit" id="submit" value="forgot">Reset Password</button>
    <span>
      <a href="/login">Login</a>
      <a href="/register">Register</a>
    </span>
  </div>
</form>
