<?php
require_once __DIR__ . '/../config/session.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out | Sweet Bean Coffee</title>
</head>
<body>
    <script>
        localStorage.removeItem('sweetBeanCart');
        window.location.href = '<?php echo BASE_URL; ?>auth/login.php';
    </script>
</body>
</html>
