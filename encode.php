<?php
//escape function
//default charset: UTF-8
function enc(string $str, string $charset = 'UTF-8'): string {
  return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, $charset);
}
?>
