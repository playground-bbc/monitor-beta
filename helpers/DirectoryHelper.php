<?php
namespace app\helpers;

use yii;
use yii\helpers\FileHelper;



/**
 *  DirectoryHelper wrapper for Directories
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */
class DirectoryHelper{

	/**
     * create or return the  valid path for save images
     * @param array $folderOptions
     * @return string $path string or boolean
     */
    public static function setFolderPath($folderOptions)
    {
    	// path to folder flat archives
		$s = DIRECTORY_SEPARATOR;

        $path = \Yii::getAlias($folderOptions['path']).$s. $folderOptions['name']. $s;
        
        
        if (!is_dir($path)) {
           $folder = FileHelper::createDirectory($path, $mode = 0777,$recursive = true);
           return ($folder) ? $path : false; 
        }

        return $path;
    }

    /**
     * [removeDirectory delete a directory and his content when delete a alert]
     * @param  int $id [id alert]
     * @param  string $resourceName [name of resource]
     * @return void
     */
    public static function removeDirectory($id,$resourceName="")
    {
        // separator to folder flat archives
        $s = DIRECTORY_SEPARATOR;
        $path = \Yii::getAlias('@data')."{$s}{$id}{$s}";
        if ($resourceName != "") {
            $path = \Yii::getAlias('@data')."{$s}{$id}{$s}{$resourceName}{$s}";
        }
        
        if (is_dir($path)) {
            FileHelper::removeDirectory($path);
        }
        // delete folder pdf
        $path = \Yii::getAlias('@pdf')."{$s}{$id}{$s}";
        if (is_dir($path)) {
            FileHelper::removeDirectory($path);
        }
        // delete folder export to file excel
        $path = \Yii::getAlias("@runtime{$s}export{$s}{$id}{$s}");
        if (is_dir($path)) {
            FileHelper::removeDirectory($path);
        }
    }
}