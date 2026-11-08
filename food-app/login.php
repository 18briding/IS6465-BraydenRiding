<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - FoodieFind</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="auth-container">
    <a href="home.php" class="logo">FoodieFind</a>
    <p style="margin-bottom: 1.5rem; color: var(--text-muted);">Restaurant Reviews & Discovery</p>
    
    <h2>Welcome Back!</h2>
    
    <form action="home.php" method="POST" style="margin-top: 1.5rem;">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
      </div>
      
      <div style="text-align: right; margin-bottom: 1rem;">
        <a href="#" style="font-size: 0.85rem; color: var(--text-muted); text-decoration: none;">Forgot Password?</a>
      </div>

      <button type="submit" class="btn" style="width: 100%;">Login</button>
    </form>
    
    <p style="margin-top: 1.5rem; font-size: 0.9rem;">
      Don't have an account? <a href="#" style="color: var(--primary-color);">Sign Up</a>
    </p>
  </div>
</body>
</html>