<?php

function htmlEnc(string $str, string $charset = 'UTF-8'): string {
  return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, $charset);
}

class checkInput {

  private $_errors;

  public function __construct(string $encoding = 'UTF-8'){
    $_errors = [];//initiation of $_errors
    mb_internal_encoding($encoding);
    $this->encodingCheck($_POST);
    $this->encodingCheck($_GET);
    $this->encodingCheck($_COOKIE);
    $this->nullCheck($_POST);
    $this->nullCheck($_GET);
    $this->nullCheck($_COOKIE);
  }

  private function encodingCheck(array $data){
    foreach ($data as $key => $value) {
      if (!mb_check_encoding($value)) {
        $this->_errors[] = "{$key}の文字エンコードが不正です．Encoding of {$key} is incorrect.";
      }
    }
  }

  private function nullCheck(array $data){
    foreach ($data as $key => $value) {
      if (preg_match('/\0/', $value)) {
        $this->_errors[] = "{$key}は不正な文字を含んでいます．{$key} has incorrect character.";
      }
    }
  }

  public function requiredCheck(string $value, string $name, string $eng){
    if (trim($value) === '') {
      $this->_errors[] = "{$name}を入力してください．enter correct {$eng}.";
    }
  }

  public function arrayCheck(string $value, string $name, string $eng, array $data){
  if (!in_array($value, $data)) {
      $msg = implode(',', $data);
      $this->_errors[] = "{$name}の入力が不正です．{$eng} is incorrect.";
    }
  }

  public function pageCheck(string $value, string $name, string $eng) {
    if (trim($value) !== '') {
      if (!ctype_digit($value)) {
        $this->_errors[] = "{$name}が不正です．{$eng} is incorrect.";
      }
    }
  }

  public function __invoke(){
    if (count($this->_errors) > 0) {
      print '<ul>';
      foreach ($this->_errors as $err) {
        print "<li>{$err}</li>";
      }
      print '</ul>';
      die();
    }
  }
}
?>
