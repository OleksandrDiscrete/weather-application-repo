<?php

include_once "../pathHelper.php";

session_start();
session_destroy();

header("Location: " . PathHelper::get_absolute_path("index.php"));
exit();