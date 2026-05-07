<?php
// logout.php
require_once dirname(__DIR__) . '/lib/db.php';
session_destroy();
header("Location: index.php");
exit;
