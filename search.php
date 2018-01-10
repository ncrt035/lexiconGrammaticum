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
<link href="main.css" rel="stylesheet" media="all">
</head>
<body>

<h1>Lexicon Grammaticum Graeco-Iaponicum ギリシア語文法用語辞典</h1>

<h2>検索</h2>

<form method="GET" action="search.php">
  <p>
    <select id="option" name="option">
      <option value="start">前方一致</option>
      <option value="end">後方一致</option>
      <option value="contain">部分一致</option>
    </select>
  </p>

  <label for="keyword">検索文字列：</label>
  <input id="keyword" type="text" name="keyword" size="15">
  <input type="submit" value="検索">
</form>

<?php

const MAX = 5;


//検索時の処理
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {

  $keyword = betacode2greek($_GET['keyword']);//replace betacode with greek letters
  $option = $_GET['option'];

  try {
    $db = getDb();

    $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Word LIKE :keyword ORDER BY word ASC');

    switch ($option) {
      case 'start':
        $stt->bindValue(':keyword', $keyword.'%');
        break;
      case 'end':
        $stt->bindValue(':keyword', '%'.$keyword);
        break;
      case 'contain':
        $stt->bindValue(':keyword', '%'.$keyword.'%');
        break;
      default://デフォルトは前方一致
        $stt->bindValue(':keyword', $keyword.'%');
        break;
    }
    $stt->execute();//Execute SQL
    $result = $stt->fetchAll(PDO::FETCH_ASSOC);//$resultは検索結果の多次元配列

    print_r($result);

    ?>
      <ul>
        <?php
        if (isset($_GET['page'])) {
          $count = $_GET['page'] * MAX;
        }
        else {
          $count = 0;
        }

        do {//結果のうちMAX個を出力 剰余で出力回数を制御するので条件を後置判定するdo...while文を用いる
          if (empty($result[$count]['Word'])){break;}
          ?>
        <li><b><?=Enc($result[$count]['Word'])?></b>: <i><?=$result[$count]['Latin']?></i> <?=$result[$count]['Expl']?></li>
        <?php
          $count++;
        } while ($count % MAX !== 0);
        ?>
      </ul>
    <?php
    for ($i=0; $i < (count($result) / MAX) ; $i++) {
      ?>
      <!--nextリンクをクリックすると次の検索結果を規定件数表示-->
      <!--生成したページ番号をpaginaという名前のGET情報として渡す-->
      <a href="search.php?option=<?=$option?>&keyword=<?=$keyword?>&page=<?=$i?>">page<?=$i?></a>

      <?php
    }


  } catch (PDOException $e) {
    print "Error: {$e->getMessage()}";
  }


}
?>

</body>
</html>
