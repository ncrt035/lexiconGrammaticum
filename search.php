<?php
require_once 'validator.php';
require_once 'betacode2greek.php';
require_once 'cfg/dbManager.php';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta content="Lexicon Grammaticum Graeco-Iaponicum ギリシア語文法用語辞典" property="og:description">
  <meta content="https://lggi.stromateis.info/search.php" property="og:url">
  <meta content="Lexicon Grammaticum Graeco-Iaponicum" property="og:site_name">
  <meta content="Search | Lexicon Grammaticum Graeco-Iaponicum" property="og:title">
  <meta content="https://lggi.stromateis.info/img/card01.jpg" property="og:image">
  <meta content="summary" property="twitter:card">
<title>Search | Lexicon Grammaticum Graeco-Iaponicum</title>
<link href="main.css" rel="stylesheet" media="all">
</head>
<body>

  <div id="title">
    <h1>Lexicon Grammaticum Graeco-Iaponicum ギリシア語文法用語辞典</h1>
    <div id="menu">
      <a class="tab" href="search.php">Search</a>
      <a class="tab" href="abbrev.html">Abbreviations</a>
      <a class="tab" href="about.html">About</a>

      <div class="cl"></div>
    </div>
  </div>

<DIV id="wrap">

<h2>検索（Search）</h2>

<form method="GET" action="search.php">
  <p>
    <select id="opt" name="opt">
      <option value="start">前方一致（words starting with）</option>
      <option value="end">後方一致（words ending with）</option>
      <option value="contain">部分一致（words containing）</option>
      <option value="exact">完全一致（the exact word）</option>
    </select>

    <select id="fld" name="fld">
      <option value="Word">見出し語（in lemma）</option>
      <option value="Latin">ラテン語（in latin）</option>
      <option value="Expl">語義（in explanation）</option>
    </select>
  </p>

  <p>
  <label for="kw">検索文字列（keyword）：</label>
  <input id="kw" type="text" name="kw" size="15">
  <input type="hidden" id="page" name="page" value="0">
  <input type="submit" value="検索">
  </p>
</form>

<p>
  ギリシア語はBeta Codeでの入力も可能．<br>
  You can also enter the Greek with Latin letters using Beta Code.
</p>

<?php

  const MAX = 10;

  $vld = new checkInput();//入力値検証

  $vld->requiredCheck($_GET['kw'], '検索文字列', 'keyword');
  $vld->arrayCheck($_GET['opt'], '検索オプション', 'option', ['start','end','contain','exact']);
  $vld->arrayCheck($_GET['fld'], '検索領域', 'field', ['Word','Latin','Expl']);
  $vld->pageCheck($_GET['page'], 'ページ数', 'page');

  $vld();//invoke


  $option = htmlEnc($_GET['opt']);
  $field = htmlEnc($_GET['fld']);
  $page = htmlEnc($_GET['page']);
  if ($field === 'Word') {
    $keyword = htmlEnc(betacode2greek($_GET['kw']));//replace betacode with greek letters
    }else {
      $keyword = htmlEnc($_GET['kw']);
  }


  try {
    $db = getDb();

    switch ($field) {
      case 'Word':
        $stt = $db->prepare('SELECT * FROM lexicon WHERE Word LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
        break;
      case 'Latin':
        $stt = $db->prepare('SELECT * FROM lexicon WHERE Latin LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
        break;
      case 'Expl':
        $stt = $db->prepare('SELECT * FROM lexicon WHERE Expl LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
        break;
      default:
        $stt = $db->prepare('SELECT * FROM lexicon WHERE Word LIKE :keyword AND Expl NOT IN ("?", "") ORDER BY Word ASC');
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

    if ($page <= ($total / MAX)) {//check whether $page is sound or not
        $count = $page * MAX;
    }else {
        print 'ページ数が不正です．';
        die();
    }

?>

<h3>検索結果：<?=$keyword?> <?=$total?>件ヒット</h3>

  <ul>
    <?php
    //結果のうちMAX個を出力 剰余で出力回数を制御するので条件を後置判定するdo...while文を用いる
      do {
        if (empty($result[$count]['Word'])){break;}
    ?>

      <li><span class="lemma" lang="el"><?=$result[$count]['Word']?></span> <span class="gr" lang="el"><?=$result[$count]['Ew']?></span>: <i><?=$result[$count]['Latin']?></i> <?=$result[$count]['Expl']?></li>

    <?php
        $count++;
      } while ($count % MAX !== 0);
    ?>
  </ul>

  <hr>

  <div class="pages">
  <?php
  //リンクをクリックすると次の検索結果を規定件数(=MAX)表示
  //生成したページ番号をpageという名前のGET情報として渡す
    for ($i=0; $i < ($total / MAX) ; $i++) {
  ?>
    <a href="search.php?opt=<?=$option?>&fld=<?=$field?>&kw=<?=$keyword?>&page=<?=$i?>"><?=$i+1?></a>

  <?php
    }
    ?>
  </div>
    <?php


  } catch (PDOException $e) {
    print "Error: {$e->getMessage()}";
  }


?>

</DIV>
</body>
</html>
