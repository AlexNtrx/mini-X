<?php 
$activeTab = (isset($_POST['login_post']) || !empty($success)) ? 'login' : 'signup';
?>
<!doctype html>
<html lang="fi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kirjaudu sisään & Rekisteröidy - Mini X</title>
    <link rel="stylesheet" href="./css/register.css" />
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js"></script>
  </head>
  <body data-active-tab="<?= htmlspecialchars($activeTab) ?>">
    <!-- Background split layer -->
    <div id="back">
      <div class="backLeft">
        <h1>Mini X</h1>
      </div>
      <div class="backRight">
        <h1>Mini X</h1>
      </div>
    </div>

    <!-- Sliding Form Layer -->
    <div id="slideBox">
      <div class="topLayer">
        <!-- Sign Up Form (Left) -->
        <div class="left">
          <div class="content">
            <h2>Sign Up</h2>
            <?php if (!empty($error) && isset($_POST['register_post'])): ?>
              <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form id="form-signup" method="post">
              <div class="form-element form-stack">
                <label for="username-signup">Username</label>
                <input
                  id="username-signup"
                  type="text"
                  name="username"
                  value=""
                  required
                />
              </div>
              <div class="form-element form-stack">
                <label for="password-signup">Password</label>
                <input
                  id="password-signup"
                  type="password"
                  name="password"
                  required
                />
              </div>
              <div class="form-element form-stack">
                <label for="email-signup">Email</label>
                <input
                  id="password-signup"
                  type="email"
                  name="email"
                  required
                />
              </div>
              <div class="form-element form-submit">
                <button id="signUp" class="signup" type="submit" name="register_post">
                  Sign up
                </button>
                <button id="goLeft" class="signup off" type="button">
                  Log In
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Login Form (Right) -->
        <div class="right">
          <div class="content">
            <h2>Login</h2>
            <?php if (!empty($error) && isset($_POST['login_post'])): ?>
              <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form id="form-login" method="post">
              <div class="form-element form-stack">
                <label for="username-login">Username</label>
                <input
                  id="username-login"
                  type="text"
                  name="username"
                  value="<?= isset($_POST['login_post']) ? htmlspecialchars($_POST['username'] ?? '') : '' ?>"
                  required
                />
              </div>
              <div class="form-element form-stack">
                <label for="password-login">Password</label>
                <input
                  id="password-login"
                  type="password"
                  name="password"
                  required
                />
              </div>
              <a href="pages/forgot-password.php">Unohduko salasana?</a>
              <div class="form-element form-submit">
                <button id="logIn" class="login" type="submit" name="login_post">
                  Log In
                </button>
                <button id="goRight" class="login off" type="button">
                  Sign Up
                </button>
                
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="./lomakeet.js"></script>
  </body>
</html>
