<?php
    /**
     * @author Max Korlaar
     */
    require_once('includes/minecraftAvatar.php');
    $avatar = new MCavatar();
    if (!isset($_GET['username'])) {
        die("Please supply an username using GET; Example: thisistheurl/?username=[username]");
    }
    if (!isset($_GET['size'])) {
        die("Please supply a size using GET; Example: thisistheurl/?username=[username]&size=[size]");
    }
    $username = $_GET['username'];
    $size     = (int)$_GET['size'];
    if ($size > 150) { // If you have a lot of resources on your server or whatever, you may increase this maximum size.
        die("Sizes greater than 150 are not allowed right now.");
    }

    header('Content-type: image/png');

    if (false) {
        // In case something happens and the whole server gets overloaded, display a sample image.
        $img = imagecreatefrompng('img/logo_small.png');
        imagesavealpha($img, true);
        imagepng($img);
        imagedestroy($img);
    } else {
        $pathToSkin = $avatar->getFromCache($username, $size);
        $im         = imagecreatefrompng($pathToSkin);
        imageinterlace($im, true);
        imagepng($im);
        imagedestroy($im);
    }