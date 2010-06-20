○設定項目一覧
  ・path => ファイル保存場所へのパス
            ※書き込み権限があれば、存在しないパスは自動的に作成されます。
              例）
                'image1' => array('path'=>'/image/aaa/bbb')
                bbbのディレクトリがない状態で
                /webroot/aaaに書き込み権限があればbbbディレクトリが作成されbbbに画像が配置されます。
                ※極論webrootに書き込み権限があれば、ディレクトリは自分で作成する必要はないですがｗ

  ・resize => 画像のリサイズ設定
              ※横サイズを基準に縦サイズをリサイズします。
                例）
                  'image1' => array('path'=>'/image/aaa/bbb', 'resize' => 640)

  ・delete => アップロードしたファイルの削除処理を行うかのフラグ
              true : 削除する(デフォルト)
              false : 削除しない
                例）編集画面で「delete_カラム名」のチェックボックスを設けるとファイル削除を行います。(※DBにカラムは必要ありません。)
                  // image1の値をnullにして、ファイルを削除
                  'image1' => array('path'=>'/image/aaa/bbb', 'delete' => true)
                  // image1の値をnullにするが、ファイルの削除を行わない
                  'image1' => array('path'=>'/image/aaa/bbb', 'delete' => false)

                  // viewファイル
                  $this->Form->input('image', array('type'=>'file'));
                  $this->Form->input('delete_image', array('type'=>'checkbox'));


○DBに保存される項目
  ・webroot以下のパスが保存されるのでimageヘルパーや、linkヘルパーに直接渡すことができます。
    例）画像を表示
      $this->Html->image($this->data['Hoge']['image']);
    例）リンクを表示
      $this->Html->image('添付ファイル', $this->data['Hoge']['file']);
