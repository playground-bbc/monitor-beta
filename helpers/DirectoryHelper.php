<?php
namespace app\helpers;

use yii;
use yii\helpers\FileHelper;



/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * DirectoryHelper wrapper for Directories
 *
 */
class DirectoryHelper{



	/**
     * @return $path string or boolean
     * create or return the  valid path for save images
     */
    public static function setFolderPath($folderOptions)
    {
    	// path to folder flat archives
		$s = DIRECTORY_SEPARATOR;

        $path = \Yii::getAlias($folderOptions['path']).$s. $folderOptions['name']. $s;
        
        
        if (!is_dir($path)) {
           $folder = FileHelper::createDirectory($path, $mode = 0775,$recursive = true);
           return ($folder) ? $path : false; 
        }

        return $path;
    }

    /**
     * [removeDirectory delete a directory and his content when delete a alert]
     * @param  [int] $id [id alert]
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
    }


}