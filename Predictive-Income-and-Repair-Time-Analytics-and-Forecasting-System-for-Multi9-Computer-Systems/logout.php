<?php
session_start();
session_unset();
session_destroy(); // සියලුම session දත්ත මකා දමයි
header("Location: login.php"); // නැවත Login පිටුවට යොමු කරයි
exit();
?>