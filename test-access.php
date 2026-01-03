<?php
echo "PHP is working! File path: " . __FILE__;
echo "<br>Directory: " . __DIR__;
echo "<br>login.html exists: " . (file_exists(__DIR__ . '/login.html') ? 'YES' : 'NO');
echo "<br>signup.html exists: " . (file_exists(__DIR__ . '/signup.html') ? 'YES' : 'NO');
phpinfo();
