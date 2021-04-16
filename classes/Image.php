<?php


class Image
{
    public static function create()
    {
        $im = imagecreatetruecolor(120, 20);
        $text_color = imagecolorallocate($im, 255, 255, 255);
        imagestring($im, 1, 5, 5,  'test image ' . rand(), $text_color);
        imagejpeg($im);
        imagedestroy($im);
    }
}