<?php
require_once 'encode.php';
require_once 'betacode2greek.php';
require_once 'dbManager.php';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
<title>LexiconGrammaticumGraeco-Iaponicum</title>
</head>
<body>

<h1>Lexicon Grammaticum Graeco-Iaponicum ギリシア語文法用語辞典</h1>

<h2>検索</h2>

<form method="GET" action="search.php">
  <label for="keyword">検索文字列：</label>
  <input id="keyword" type="text" name="keyword" size="15">
  <input type="submit" value="検索">
</form>

<?php
//検索時の処理
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {

  try {
    $db = getDb();

    $stt = $db->prepare('SELECT * FROM greek WHERE vox LIKE :keyword ORDER BY vox ASC');
    $stt->bindValue(':keyword', $_GET['keyword'].'%');

    $stt->execute();//Execute SQL
    $result = $stt->fetchAll(PDO::FETCH_ASSOC);//$resultは検索結果の多次元配列
    print_r($result);



  } catch (PDOException $e) {
    print "Error: {$e->getMessage()}";
  }


}
?>

</body>
</html>
