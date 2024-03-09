<?php

namespace App\Helper;

use Exception;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;

class HelperImageResizer
{
    /**
     * Image resizer GD
     *
     * @param string $path
     * @param integer|null $quality
     * @param integer|null $height
     * @param integer|null $width
     * @param string $fileExtension
     *
     * @return void
     */
    public function resize(
        string $path,
        int $quality = null,
        int $height = null,
        int $width = null,
        string $fileExtension
    ): void {
        try {
            $image = null;

            if ($fileExtension == 'png') {
                $image = imagecreatefrompng($path . '.' . $fileExtension);
                imagesavealpha($image, true);
                imagepng($image, $path . '.png', $quality ?? -1);
            }
            if ($fileExtension == 'jpeg' || $fileExtension == 'jpg') {
                $image = imagecreatefromjpeg($path . '.' . $fileExtension);
                imagejpeg($image, $path . '.jpg', $quality ?? -1);
            }

            $this->rezizeImageresolution(
                $path . '.' . $fileExtension,
                $height ?? imagesy($image),
                $width ?? imagesx($image)
            );
        } catch (Exception $e) {
            // ... handle exception if something happens during file upload
        }
    }

    /**
     * Change image resolution Gumlet library
     *
     * @param string $filenameWithExtension
     * @param integer $height
     * @param integer $width
     *
     * @return void
     */
    public function rezizeImageresolution(string $filenameWithExtension, int $height, int $width): void
    {
        try {
            $image = new ImageResize($filenameWithExtension);

            $image->resize($width, $height);
            $image->save($filenameWithExtension);
        } catch (ImageResizeException $e) {
            // ... handle exception if something happens during file upload
        }
    }
}
