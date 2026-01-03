<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Clear client-side session storage
        localStorage.removeItem('userSession');
        localStorage.removeItem('sessionToken');
        sessionStorage.removeItem('userSession');
        
        // Redirect to login page
        window.location.href = 'login.php';
    </script>
</body>
</html>