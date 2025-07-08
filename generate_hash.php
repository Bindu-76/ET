<?php
$password = 'Pooja@123'; // your actual password
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashed;
?>
