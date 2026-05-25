<?php
namespace WeatherMaster\Auth;

include_once "../helpers/pathHelper.php";

use WeatherMaster\Helpers\PathHelper;

session_start();
session_destroy();

header("Location: " . PathHelper::getAbsolutePath("index.php"));
exit();