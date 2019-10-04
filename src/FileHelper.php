<?php
namespace yii2cmf\helpers;

use Yii;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class FileHelper
{

    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);
        //$bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }


    public static function getImageWidthAndHeight($filePath)
    {
        if (file_exists($filePath)) {
            list($with, $height) = getimagesize($filePath);
            return $with.'x'.$height;
        }
        return null;
    }

    public static function getImageWidthHeight($filePath)
    {
        if (file_exists($filePath)) {
            list($width, $height) = getimagesize($filePath);
            return ['width' => $width, 'height' => $height];
        }
        return null;
    }

    public static function getImageHeight($filePath)
    {
        if (file_exists($filePath)) {
            list($width, $height) = getimagesize($filePath);
            return $height;
        }
        return null;
    }

    public static function getLastValueFromString($filePath, $delimiter = '/')
    {
        $m = explode('/',$filePath);
        return $m[array_key_last($m)];
    }

    /**
     * from modules/basic/uploads/2019/05/file.jpg
     * to modules/basic/uploads/2019/05
     * @param $guid
     * @return bool|string
     */
    public static function getPath($guid)
    {
        return substr($guid, 0, strrpos($guid, '/'));
    }

    /**
     * Get only extension
     * @param $filenameWithExt
     * @return bool|string
     */
    public static function getExtension($filenameWithExt)
    {
        return substr($filenameWithExt,strpos($filenameWithExt,'.')+1);
    }

    /**
     * Get only filename without extension
     * @param $filenameWithExt
     * @return bool|string
     */
    public static function getFilename($filenameWithExt)
    {
        return substr($filenameWithExt,0, strpos($filenameWithExt,'.'));
    }

    public static function getFileNameWithExtension($guid)
    {
        $guidArray = explode('/', $guid);
        $lastKey = array_key_last($guidArray);
        return $guidArray[$lastKey];
    }

    public static function getRelativeFilePath($file)
    {
        return str_replace('modules/'.Yii::$app->id.'/uploads/','',$file);
    }

    public static function getMimeType($filename)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mime_type;
    }

    public static function getImage($guid)
    {
        $filenameWithExt = FileHelper::getFileNameWithExtension($guid);
        $filename = FileHelper::getFilename($filenameWithExt);
        $ext =  FileHelper::getExtension($filenameWithExt);

        $path = FileHelper::getPath($guid);

        $p = $path.'/'.$filename.'-'.md5($filename).'.'.$ext;
        if (file_exists($p)) {
            return Yii::getAlias("@web/{$p}");
        } else {
            return Yii::getAlias("@web/{$guid}");
        }
    }

    public static function compareImageHeight($guid, $height)
    {
        if (file_exists($guid)) {

            $dim = self::getImageWidthHeight($guid);
            if ($dim['height'] >= $height) {
                return $height;
            } else {
                return $dim['height'];
            }
        }
        return 0;
    }

    public static function fileArchiving($path, $filename, $ext, $guid)
    {
        $archive = $path.'/'.$filename.'-'.md5($filename).'.'.$ext;

        if (!file_exists($archive)) {
            copy($guid,$archive);
            return true;
        }
        return false;
    }

    public static function getFileSize($guid)
    {
        if (file_exists($guid)) {
            return self::formatBytes(filesize($guid));
        }
        return 0;
    }

    public static function getThumbnailImage($guid)
    {
        return self::getAttachment($guid, 'thumbnail');
    }

    public static function getMediumImage($guid)
    {
        return self::getAttachment($guid, 'medium');
    }

    public static function getMediumLargeImage($guid)
    {
        return self::getAttachment($guid, 'medium_large');
    }

    public static function getLargeImage($guid)
    {
        return self::getAttachment($guid, 'large');
    }

    public static function createThumbnailImage($guid)
    {
        $dim = ['width' => '150', 'height' => '150'];
        if (file_exists($guid)) {
            $dim = self::saveThumbnailImage($guid);
        }
        return $dim;
    }

    public static function createMediumImage($guid)
    {
        $dim = ['width' => '300', 'height' => '300'];
        if (file_exists($guid)) {
            $dim = self::saveMediumImage($guid);
        }
        return $dim;
    }

    public static function createMediumLargeImage($guid)
    {
        $dim = ['width' => '768', 'height' => '0'];
        if (file_exists($guid)) {
            $dim = self::saveMediumLargeImage($guid);
        }
        return $dim;
    }

    public static function createLargeImage($guid)
    {
        $dim = ['width' => '1024', 'height' => '1024'];
        if (file_exists($guid)) {
            $dim = self::saveLargeImage($guid);
        }
        return $dim;
    }

    public static function getFilenameWithoutExtension($guid)
    {
        return self::getFilename(self::getFileNameWithExtension($guid));
    }
    public static function getFileExtension($guid)
    {
        return self::getExtension(self::getFileNameWithExtension($guid));
    }

    public static function unlink($path):bool
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }


    /**
     * @param $guid
     * @param bool $square
     * @return array
     */
    private static function saveThumbnailImage($guid, $optionWidth, $optionHeight, $square = true): array
    {
        $imagine = new Imagine();
        $size = new Box($optionWidth, $optionHeight);
        $mode = $square ? ImageInterface::THUMBNAIL_OUTBOUND : ImageInterface::THUMBNAIL_INSET;
        $imagine->open($guid)
            ->thumbnail($size, $mode)
            ->save(self::getFileNameWithWidthAndHeight($guid, $optionWidth, $optionHeight));

        return ['width' => $optionWidth, 'height' => $optionHeight];
    }

    private static function saveMediumImage($guid, $optionWidth)
    {
        $height = self::resizeWithPreserveAspectRatio($guid, $optionWidth,95);

        return ['width' => $optionWidth, 'height' => $height];
    }

    private static function saveLargeImage($guid, $optionWidth)
    {
        $height = self::resizeWithPreserveAspectRatio($guid, $optionWidth,95);
        return ['width' => $optionWidth, 'height' => $height];
    }

    private static function saveMediumLargeImage($guid, $optionWidth)
    {
        $height = self::resizeWithPreserveAspectRatio($guid, $optionWidth,95);
        return ['width' => $optionWidth, 'height' => $height];
    }

    private static function getFileNameWithWidthAndHeight($guid, $width, $height)
    {
        return self::getPath($guid) . '/' . self::getFilenameWithoutExtension($guid) . '-' . $width . 'x' . $height . '.' . self::getFileExtension($guid);
    }

    /**
     * @param $imageNameFullPath
     * @param $guid
     * @param $width
     * @param int $quality
     * @return ImageInterface
     */
    private static function resizeWithPreserveAspectRatio($guid, $width, $quality = 90)
    {

        $imagine = new Imagine();
        $image = $imagine->open($guid);
        $size = $image->getSize();
        $ratio = $size->getWidth()/$size->getHeight();
        $height = round($width/$ratio);

        $resizedImageNameFullPath = self::getFileNameWithWidthAndHeight($guid, $width, $height);

        $image
            ->resize(new Box($width, $height))
            ->save($resizedImageNameFullPath, ['quality' => $quality]);

        return $height;
    }

    private static function getAttachment($guid, $type, $attachmentMeta)
    {
        $attachmentMeta = json_decode($attachmentMeta);
        $thumbnailFile = $attachmentMeta->sizes->{$type}->file;
        return self::getPath($guid).'/'.$thumbnailFile;
    }

    public static function getImageMeta($id, $guid, $size)
    {
        if ($size == 'full') {
            return self::getImage($guid);
        } elseif ($size == 'large') {
            return self::getLargeImage($guid);
        } elseif($size == 'medium-large'){
            return self::getMediumLargeImage($guid);
        } elseif ($size == 'medium') {
            return self::getMediumImage($guid);
        } elseif ($size == 'thumbnail') {
            return self::getThumbnailImage($guid);
        }
        return null;
    }
}
