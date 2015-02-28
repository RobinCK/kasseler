<?php
if (!defined("KASSELERCMS")) die("Access is limited");
header("X-UA-Compatible: IE=9");
header("Last-Modified: ".gmdate("D, d M Y H:i:s", strtotime("-1 day"))." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: ".gmdate('D, d M Y H:i:s', time()+10)." GMT");
header("Pragma: no-cache");
?>