
require 'config/userinfo_db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: space");
    exit(); // Stop script execution after redirection
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['usernameOrEmail']);
    $password = trim($_POST['password']);
    $rememberMe = isset($_POST['remember_me']); // Check if "Remember Me" is checked

    // Check if the user exists
    $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        die('❌ Query Preparation Failed: ' . $conn->error);
    }

    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Remember Me: Set cookie if selected
            if ($rememberMe) {
                $token = bin2hex(random_bytes(16)); // Generate a secure token
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days

                // Store the token in the database
                $stmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_token_expires = ? WHERE id = ?");
                $stmt->bind_param("ssi", $token, date('Y-m-d H:i:s', $expiry), $user['id']);
                $stmt->execute();

                // Set cookie on the user's browser
                setcookie('remember_me', $token, $expiry, "/", "", true, true);
            }

            header('Location: space');
            exit();
        } else {
            echo "<script>alert('❌ Incorrect password.'); window.location.href='login';</script>";
            exit();
        }
    } else {
        echo "<script>alert('❌ Username or email not found.'); window.location.href='login';</script>";
        exit();
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    /* General Body Styles */
    body {
        margin: 0;
        font-family: 'Arial', sans-serif;
        background: #2c2f33;
        color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 90vh;
    }

    /* Login Container */
    #login-container {
        background: #36393f;
        padding: 40px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        width: 400px;
        text-align: center;
    }

    /* Heading */
    #login-container h2 {
        color: #7289da;
        font-size: 1.8em;
        margin-bottom: 20px;
    }

    /* Form Styles */
    #login-container form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    #login-container form input[type="text"],
    #login-container form input[type="password"] {
        padding: 12px;
        border-radius: 5px;
        border: 1px solid #424549;
        background: #2c2f33;
        color: #ffffff;
        font-size: 14px;
    }

    #login-container form input:focus {
        border-color: #7289da;
        background: #23272a;
    }

    /* Submit Button */
    #login-container button {
        background: #7289da;
        color: #ffffff;
        border: none;
        padding: 12px;
        font-size: 14px;
        font-weight: bold;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    #login-container button:hover {
        background: #5b6eae;
    }

    /* Links */
    #login-container p {
        margin-top: 15px;
        font-size: 14px;
    }

    #login-container p a {
        color: #7289da;
        text-decoration: none;
        font-weight: bold;
    }

    #login-container p a:hover {
        text-decoration: underline;
    }

    /* Forgot Password Link */
    .forgot-password {
        margin-top: 10px;
        font-size: 14px;
    }

    .forgot-password a {
        color: #ffcc00;
        text-decoration: none;
        font-weight: bold;
    }

    .forgot-password a:hover {
        text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 480px) {
        #login-container {
            width: 90%;
            padding: 20px;
        }
    }
        .gsi-material-button {
      -moz-user-select: none;
      -webkit-user-select: none;
      -ms-user-select: none;
      -webkit-appearance: none;
      background-color: WHITE;
      background-image: none;
      border: 1px solid #747775;
      -webkit-border-radius: 20px;
      border-radius: 20px;
      -webkit-box-sizing: border-box;
      box-sizing: border-box;
      color: #1f1f1f;
      cursor: pointer;
      font-family: 'Roboto', arial, sans-serif;
      font-size: 14px;
      height: 40px;
      letter-spacing: 0.25px;
      outline: none;
      overflow: hidden;
      padding: 0 12px;
      position: relative;
      text-align: center;
      -webkit-transition: background-color .218s, border-color .218s, box-shadow .218s;
      transition: background-color .218s, border-color .218s, box-shadow .218s;
      vertical-align: middle;
      white-space: nowrap;
      width: auto;
      max-width: 400px;
      min-width: min-content;
    }

    .gsi-material-button .gsi-material-button-icon {
      height: 20px;
      margin-right: 12px;
      min-width: 20px;
      width: 20px;
    }

    .gsi-material-button .gsi-material-button-content-wrapper {
      -webkit-align-items: center;
      align-items: center;
      display: flex;
      -webkit-flex-direction: row;
      flex-direction: row;
      -webkit-flex-wrap: nowrap;
      flex-wrap: nowrap;
      height: 100%;
      justify-content: space-between;
      position: relative;
      width: 100%;
    }

    .gsi-material-button .gsi-material-button-contents {
      -webkit-flex-grow: 1;
      flex-grow: 1;
      font-family: 'Roboto', arial, sans-serif;
      font-weight: 500;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: top;
    }

    .gsi-material-button .gsi-material-button-state {
      -webkit-transition: opacity .218s;
      transition: opacity .218s;
      bottom: 0;
      left: 0;
      opacity: 0;
      position: absolute;
      right: 0;
      top: 0;
    }

    .gsi-material-button:disabled {
      cursor: default;
      background-color: #ffffff61;
      border-color: #1f1f1f1f;
    }

    .gsi-material-button:disabled .gsi-material-button-contents {
      opacity: 38%;
    }

    .gsi-material-button:disabled .gsi-material-button-icon {
      opacity: 38%;
    }

    .gsi-material-button:not(:disabled):active .gsi-material-button-state, 
    .gsi-material-button:not(:disabled):focus .gsi-material-button-state {
      background-color: #303030;
      opacity: 12%;
    }

    .gsi-material-button:not(:disabled):hover {
      -webkit-box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .30), 0 1px 3px 1px rgba(60, 64, 67, .15);
      box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .30), 0 1px 3px 1px rgba(60, 64, 67, .15);
    }

    .gsi-material-button:not(:disabled):hover .gsi-material-button-state {
      background-color: #303030;
      opacity: 8%;
    }
  </style>
</head>
<body>
  <div id="login-container">
    <h2>Login</h2>
    <form action="login.php" method="POST">
      <label>Username or Email:</label>
      <input type="text" name="usernameOrEmail" required>

      <label>Password:</label>
      <input type="password" name="password" required>

      <button type="submit">Login</button>

      <label>
          <input type="checkbox" name="remember_me"> Remember Me
      </label>

    </form>

    <button id="continueWithGoogle" class="gsi-material-button">
      <div class="gsi-material-button-state"></div>
      <div class="gsi-material-button-content-wrapper">
        <div class="gsi-material-button-icon">
          <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
            <path fill="none" d="M0 0h48v48H0z"></path>
          </svg>
        </div>
        <span class="gsi-material-button-contents">Continue with Google</span>
        <span style="display: none;">Continue with Google</span>
      </div>
    </button>

    
    <p>Don't have an account? <a href="signup">Sign Up</a></p>
    <p class="forgot-password"><a href="forgot_password.html">Forgot Password?</a></p>
  </div>
  <script>
    document.getElementById("continueWithGoogle").onclick = function () {
        location.href = "<?= SITE_URL ?>account/login.php?google_login=1";
    };
  </script>
</body>
</html>

