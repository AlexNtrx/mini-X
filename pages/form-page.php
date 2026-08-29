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
    <link rel="stylesheet" href="./css/forgot-password.css" />
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
              <input type="hidden" name="register_post" value="1" />
              <div class="form-element form-stack">
                <label for="username-signup">Username</label>
                <input
                  id="username-signup"
                  type="text"
                  name="username"
                  value=""
                />
              </div>
              <div class="form-element form-stack">
                <label for="password-signup">Password</label>
                <input
                  id="password-signup"
                  type="password"
                  name="password"
                />
              </div>
              <div class="form-element form-stack">
                <label for="email-signup">Email</label>
                <input
                  id="email-signup"
                  type="email"
                  name="email"
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
              <a href="pages/forgot-password.php" id="forgot-password-link" class="forgot-link">Unohditko salasanan?</a>
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

    <!-- Forgot Password Modal -->
    <div id="forgot-password-modal" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-card">
        <button type="button" class="modal-close" id="modal-close-btn" aria-label="Sulje">&times;</button>
        <div class="modal-header">
          <h2 id="modal-title">Palauta salasana</h2>
          <p class="modal-subtitle">Syötä käyttäjätilisi sähköpostiosoite, niin lähetämme sinulle linkin salasanan palauttamista varten.</p>
        </div>
        
        <div id="modal-alert-container"></div>

        <form id="form-forgot-modal" method="post" action="send-password-reset.php">
          <div class="form-element form-stack">
            <label for="modal-email">Sähköposti</label>
            <input
              type="email"
              name="email"
              id="modal-email"
              placeholder="esim. kayttaja@example.com"
              required
              autocomplete="email"
            />
          </div>
          <div class="modal-actions">
            <button type="button" class="modal-btn-secondary" id="modal-cancel-btn">Peruuta</button>
            <button type="submit" class="modal-btn-primary" id="modal-submit-btn">
              <span class="btn-text">Lähetä linkki</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://unpkg.com/just-validate@4.3.0/dist/just-validate.production.min.js"></script>
    <script src="./lomakeet.js"></script>
    <script src="./validation.js"></script>
  </body>
</html>
