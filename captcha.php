<?php
session_start();
$num1 = rand(1,9);
$num2 = rand(1,9);
$_SESSION['captcha'] = $num1 + $num2;
echo "What is $num1 + $num2 ?";
?>
