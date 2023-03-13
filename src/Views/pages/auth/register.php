<form action="" method="POST">
  <div class="form-group">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" placeholder="Choose your username" value="<?php echo $vars->post->auth->username ?? null; ?>">
  </div>
  <div class="form-group">
    <label for="email">Email Address:</label>
    <input type="email" name="email" id="email" placeholder="Your email" value="<?php echo $vars->post->auth->email ?? null; ?>">
  </div>
  <div class="form-group">
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" placeholder="Your password">
  </div>
  <div class="form-group">
    <label for="confirmpassword">Confirm Password:</label>
    <input type="password" name="confirmpassword" id="confirmpassword" placeholder="Confirm your password">
  </div>
  <div class="form-footer">
    <button type="submit" name="submit" id="submit" value="register">Register</button>
    <span>
      <a href="/">Login</a>
      <a href="/forgot">Forgot Password</a>
    </span>
  </div>
</form>
