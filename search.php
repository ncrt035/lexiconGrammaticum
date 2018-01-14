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
  <p>
    <select id="field" name="field">
      <option value="Word">見出し語</option>
      <option value="Latin">ラテン語</option>
      <option value="Expl">語義</option>
    </select>
  </p>

  <label for="keyword">検索文字列：</label>
  <input id="keyword" type="text" name="keyword" size="15">
  <input type="submit" value="検索">
</form>

<?php

const MAX = 10;


//検索時の処理
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {

  $option = $_GET['option'];
  $field = $_GET['field'];
  if ($field === 'Word') {
    $keyword = betacode2greek($_GET['keyword']);//replace betacode with greek letters
  }else {
    $keyword = $_GET['keyword'];
  }

  print($field);
    print($keyword);

  try {
    $db = getDb();

    switch ($field) {
      case 'Word':
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Word LIKE :keyword AND Expl != "?" ORDER BY Word ASC');
        break;
      case 'Latin':
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Latin LIKE :keyword AND Expl != "?" ORDER BY Word ASC');
        break;
      case 'Expl':
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Expl LIKE :keyword AND Expl != "?" ORDER BY Word ASC');
        break;
      default:
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Word LIKE :keyword AND Expl != "?" ORDER BY Word ASC');
        break;
    }

    switch ($option) {
      case 'start':
        $stt->bindValue(':keyword', $keyword.'%');
        //$stt->bindValue(':field', $field);
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
        <li><b><?=Enc($result[$count]['Word'])?></b> <?=$result[$count]['Ew']?>: <i><?=$result[$count]['Latin']?></i> <?=$result[$count]['Expl']?></li>
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
      <a href="search.php?option=<?=$option?>&field=<?=$field?>&keyword=<?=$keyword?>&page=<?=$i?>">page<?=$i?></a>

      <?php
    }


  } catch (PDOException $e) {
    print "Error: {$e->getMessage()}";
  }


}
?>

</body>
</html>
