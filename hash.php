<?php
$plainPassword = 'Teja@8352';
$hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
echo $hashed;
?>
