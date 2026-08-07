<?php
session_start();
session_destroy();
header('Location: index_pwa.php?msg=Logged out successfully');
exit;
?>