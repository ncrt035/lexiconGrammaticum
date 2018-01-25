<?php
require_once 'validator.php';
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
      <option value="exact">完全一致</option>
    </select>
  </p>
  <p>
    <select id="field" name="field">
      <option value="Word">見出し語</option>
      <option value="Latin">ラテン語</option>
      <option value="Expl">語義</option>
    </select>
  </p>

  <p>
  <label for="keyword">検索文字列：</label>
  <input id="keyword" type="text" name="keyword" size="15">
    <input type="hidden" id="page" name="page" value="0">
  <input type="submit" value="検索">
  </p>
</form>

<?php

  const MAX = 10;

  $vld = new checkInput();//入力値検証

  $vld->requiredCheck($_GET['keyword'], '検索文字列');
  $vld->arrayCheck($_GET['option'], '検索オプション', ['start','end','contain','exact']);
  $vld->arrayCheck($_GET['field'], '検索領域', ['Word','Latin','Expl']);
  $vld->pageCheck($_GET['page'], 'ページ数');

  $vld();//invoke


  $option = htmlEnc($_GET['option']);
  $field = htmlEnc($_GET['field']);
  $page = htmlEnc($_GET['page']);
  if ($field === 'Word') {
    $keyword = htmlEnc(betacode2greek($_GET['keyword']));//replace betacode with greek letters
    }else {
      $keyword = htmlEnc($_GET['keyword']);
  }


  try {
    $db = getDb();

    switch ($field) {
      case 'Word':
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Word LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
        break;
      case 'Latin':
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Latin LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
        break;
      case 'Expl':
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Expl LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
        break;
      default:
        $stt = $db->prepare('SELECT * FROM lexicon2 WHERE Word LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
        break;
    }

    switch ($option) {
      case 'start':
        $stt->bindValue(':keyword', $keyword.'%', PDO::PARAM_STR);
        break;
      case 'end':
        $stt->bindValue(':keyword', '%'.$keyword, PDO::PARAM_STR);
        break;
      case 'contain':
        $stt->bindValue(':keyword', '%'.$keyword.'%', PDO::PARAM_STR);
        break;
      case 'exact':
        $stt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
        break;
      default://デフォルトは前方一致
        $stt->bindValue(':keyword', $keyword.'%', PDO::PARAM_STR);
        break;
    }


    $stt->execute();//Execute SQL
    $result = $stt->fetchAll(PDO::FETCH_ASSOC);//$resultは検索結果の多次元配列


    $total = count($result);

      if ($page < ($total / MAX)) {//if $page is sound
        $count = $page * MAX;
      }
      else {
        print 'ページ入力が不正です．';
        die();
      }

?>

<h3>検索結果：<?=$keyword?></h3>

  <ul>
    <?php

      do {//結果のうちMAX個を出力 剰余で出力回数を制御するので条件を後置判定するdo...while文を用いる
        if (empty($result[$count]['Word'])){break;}
    ?>
      <li><b><?=$result[$count]['Word']?></b> <?=$result[$count]['Ew']?>: <i><?=$result[$count]['Latin']?></i> <?=$result[$count]['Expl']?></li>
    <?php
        $count++;
      } while ($count % MAX !== 0);
    ?>
  </ul>
  <?php
    for ($i=0; $i < ($total / MAX) ; $i++) {
  ?>
    <!--リンクをクリックすると次の検索結果を規定件数(=MAX)表示-->
    <!--生成したページ番号をpageという名前のGET情報として渡す-->
    <a href="search.php?option=<?=$option?>&field=<?=$field?>&keyword=<?=$keyword?>&page=<?=$i?>">page<?=$i+1?></a>

  <?php
    }


  } catch (PDOException $e) {
    print "Error: {$e->getMessage()}";
  }


?>

</body>
</html>
