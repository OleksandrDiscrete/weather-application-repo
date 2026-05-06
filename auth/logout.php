<?php

include_once "../pathHelper.php";

session_start();
session_destroy();

header("Location: " . PathHelper::getAbsolutePath("index.php"));
exit();