<?php
session_start();
session_destroy();
header("Location: bienvenidos.html");
exit;
?>