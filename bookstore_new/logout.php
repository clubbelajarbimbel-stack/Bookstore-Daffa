<?php
// logout.php - forget the login, go back to home
require_once 'includes/config.php';
session_unset();
session_destroy();
go('index.php');
