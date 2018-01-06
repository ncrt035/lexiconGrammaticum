# Lexicon Grammaticum Graeco-Iaponicum

## Praefatio
[これ](https://www.stromateis.info/zib/gramm_term.html)を増補するのが当初の目的．

## Vocabula
語彙の選択範囲はDickey, E.(2007), *Ancient Greek Scholarship*, Oxford University Pressの拾っているものを最小限として，

- Bécares Botas, V.(1985), *Diccionario de terminología gramatical griega*, Salamanca: Ediciones Universidad de Salamanca.
- Montanari, F.(2013, 3a ed.), *Vocabolario della lingua greca*, Torino: Loescher.

あたりを適宜採り入れたい．

### branches

- searchOption: 検索オプション（前方一致・後方一致その他）や検索フィールド選択などの機能を実装するためのブランチ
- printResult: 結果出力関係の諸機能を実装・調整するためのブランチ
- editWL: wordList.mdを編集する用のブランチ

### table
`id INT PRIMARY KEY AUTO_INCREMENT`
`word VARCHAR(31) NOT NULL` 
`ew VARCHAR(15) DEFAULT NULL`
`latin VARCHAR(31) DEFAULT NULL`
`expl VARCHAR(1023) NOT NULL`

### CSS
`<span class="" lang="el">`
