<?php

/**
 * BeehiveBehavior
 */
/**
 * BeehiveBehavior code license:
 *
 * @copyright Copyright (C) 2010 saku All rights reserved.
 * @since CakePHP(tm) v 1.2
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class BeehiveBehavior extends ModelBehavior { 
    const VERSION = '0.1';
    var $settings = array();
    
    // ディレクトリを作成した場合のパーミッション
    const DIR_PERMISSION = 0777;
    
    function setup(&$model, $config = array()) { 
        $this->settings = $config;
        
        ini_set('memory_limit',-1);
    }
    
	function beforeSave(&$model){
        
        // アップロード開始
        $this->_fileUpload($model);
        
        return true;
    }
    
    
	/**
	 * ファイルアップロードを実行する
	 * 
	 * @access private
	 * @author sakuragawa
	 */
    private function _fileUpload(&$model){
        if(!isset($model->beehiveList) || !is_array($model->beehiveList)){
            return true;
        }
        $data = $model->data;
        $modelName = $model->name;
        
        foreach($model->beehiveList as $col=>$setting)
        {
            if(!isset($data[$modelName][$col])){
                // カラム名がない
                continue;
            }
            
            if(!isset($setting['path'])){
                // 移動パスは必須項目
                die("beehiveListにpathを設定してください");
            }
            
            // パスを加工する
            $setting['path'] = $this->_formatPath($setting['path']);
            
            // 削除処理
            if(isset($data[$modelName]['delete_' . $col]) && ($data[$modelName]['delete_' . $col] == true)){
                // 削除チェックがチェックされていればNULLをいれる
                // カラム名：delete_カラム名
                $model->data[$modelName][$col] = null;
                continue;
            }
            
            // ファイルアップロード情報
            $fileData = $data[$modelName][$col];
            if($fileData['error'] != UPLOAD_ERR_OK){
                // アップロードされていない、又は、エラーが発生しているので何もしない
                
                if(empty($model->data[$modelName]['id'])){
                    // 新規登録時
                    $model->data[$modelName][$col] = null;
                }else{
                    // 編集時
                    unset($model->data[$modelName][$col]);
                }
                continue;
            }
            
            // ディレクトリのチェック
            $this->_checkDirectory($setting['path']);
            
            // ファイルをtmpから保存先へ移動する
            $pathInfo = array();
            $ret = $this->_moveFile($fileData, $setting, $pathInfo);
            if($ret === false){
                // エラー発生
                continue;
            }
            
            // 画像かの判別
            $type = @exif_imagetype($pathInfo['fullPath']);
            if($type !== false){
                // 画像である
                if(isset($setting['resize'])){
                    // 画像をリサイズする
                    $this->_imageResize($pathInfo['fullPath'], $setting['resize']);
                }
            }
            
            // 保存用の値を設定(webroot以降のパス)
            $model->data[$modelName][$col] = '/' . $pathInfo['basePath'];
        }
    }
    
    
	/**
	 * 移動先のディレクトリをチェックする
	 * 存在しなければ作成する
	 * 
	 * @access private
	 * @author sakuragawa
	 */
    private function _checkDirectory($path){
        $basePath = WWW_ROOT . $path;
        
        if(!is_dir($basePath)){
            // ディレクトリが存在しない
            $ret = @mkdir($basePath, BeehiveBehavior::DIR_PERMISSION, true);
            if($ret === false){
                die('ファイル配置用ディレクトリの作成に失敗しました。<br />権限等を確認してください。');
            }
            // umaskの方がいいのかな？
            chmod($basePath, BeehiveBehavior::DIR_PERMISSION);
        }
        
        return true;
    }
    
    
	/**
	 * ファイルをtmpから保存先へ移動する
	 * ファイル名はユニークにする
	 * 
	 * @access private
	 * @author sakuragawa
	 */
    private function _moveFile($fileData, $setting, &$pathInfo){
        $pathInfo = null;
        
        // tmpからの移動
        $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $fileName = sprintf("%s.%s", md5(uniqid(rand(), true)), $ext);
        $basePath = $setting['path'] . $fileName;
        $movePath = WWW_ROOT . $basePath;
        $ret = move_uploaded_file($fileData['tmp_name'], $movePath);
        if($ret === false){
            // エラー発生
            return false;
        }
        
        // webroot以降のパス
        $pathInfo['basePath'] = $basePath;
        // ファイル名
        $pathInfo['fileName'] = $fileName;
        // フルパス
        $pathInfo['fullPath'] = $movePath;
     
        
        return true;
    }
    
    /**
     * 画像をリサイズする.(Xサイズを基準)
     *
     * @access public
     * @author sakuragawa
     * @author kawano
     * @param
     * @return
     */
    function _imageResize($imagePath, $baseSize) {
        if (file_exists($imagePath) === false) {
            return false;
        }

        $imagetype = exif_imagetype($imagePath);
        if ( $imagetype === false ) {
            return false;
        }

        // 画像読み込み
        $image = false;

        switch ($imagetype) {
            case IMAGETYPE_GIF:
                $image = ImageCreateFromGIF($imagePath);
                break;
            case IMAGETYPE_JPEG:
                $image = ImageCreateFromJPEG($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = ImageCreateFromPNG($imagePath);
                break;
            default :
                return false;
        }

        if (!$image) {
            // 画像の読み込み失敗
            return false;
        }

        // 画像の縦横サイズを取得
        $sizeX = ImageSX($image);
        $sizeY = ImageSY($image);
        // リサイズ後のサイズ
        $reSizeX = 0;
        $reSizeY = 0;

        if ($baseSize == $sizeX) {
            // 基準サイズ(リサイズ必要なし)
            ImageDestroy($image);
            return true;
        }

        // 元画像と基準サイズとの差
        $diffSizeX = $sizeX - $baseSize;
        //$diffSizeY = $sizeY - IMG_Y;
        // リサイズの倍率
        $mag = 1;

        // リサイズ後のサイズを計算
        $mag = $baseSize / $sizeX;
        $reSizeX = $baseSize;
        $reSizeY = $sizeY * $mag;

        // サイズ変更後の画像データを生成
        $outImage = ImageCreateTrueColor($reSizeX, $reSizeY);
        if (!$outImage) {
            // リサイズ後の画像作成失敗
            return false;
        }

        // 画像リサイズ
        $ret = imagecopyresampled($outImage, $image, 0, 0, 0, 0, $reSizeX, $reSizeY, $sizeX, $sizeY);

        if ($ret === false) {
            // リサイズ失敗
            return false;
        }

        ImageDestroy($image);

        // 画像保存
        ImageJPEG($outImage, $imagePath);
        ImageDestroy($outImage);

        return true;
    }
    
    
	/**
	 * 使いやすいようにパスを加工する
	 * ※先頭は「/」なし
	 * ※終端は「/」あり
	 * 
	 * @access public
	 * @author sakuragawa
	 */
    private function _formatPath($path){
        // 前後の「/」を切り捨てて、「/」をつける
        $str = trim($path, "/");
        $str .= '/';
        
        return $str;
    }
}
?>