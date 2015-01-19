<?php
   /**
   * @author Max Korlaar
   * Get a GIF of a rotating Minecraft skin. Uses the moreMCavatar class.
   * Requests data from GET and creates a new moreMCavatar instance, which will do the rest of it.
   */
    ini_set('display_errors', 1); // My webserver defaults to false.
    require_once('includes/moreMCavatar.php');
    $avatar = new moreMCavatar();
    if (!isset($_GET['username'])) {
        die("Please supply an username using GET; Example: thisistheurl/?username=[username]");
    }
    if (!isset($_GET['size'])) {
        die("Please supply a size using GET; Example: thisistheurl/?username=[username]&size=[size]");
    }
    $username = $_GET['username'];
    $size     = (int)$_GET['size'];
    if ($size > 25) { // If you have a load of resources on your server or whatever, you may increase this maximum size.
        die("Sizes greater than 25 are not allowed right now.");
    }

    $speed    = 3;
    $rotation = 5;
    if (isset($_GET['speed'])) {
        $speed = (int)$_GET['speed'];
        if ($speed > 400) $speed = 400;
    }
    if (isset($_GET['frames'])) {
        $rotation = (int)$_GET['frames'];
        if ($rotation < -360) $rotation = -5;
        if ($rotation > 360) $rotation = 5;
    }
    if (!isset($_GET['db'])) header('Content-type: image/gif'); // You may see this more in my code, it helps me whenever something's not working correctly.

    $layered = isset($_GET['layered']);

    if (false) {
        // In case something happens and the whole server gets overloaded, display a sample image.
        $img = imagecreatefrompng('img/logo_small.png');
        imagesavealpha($img, true);
        imagepng($img);
        imagedestroy($img);
    } else {
        echo $avatar->getRotatingSkinFromCache($username, $size, $speed, $rotation, false, false, $layered);
    }
    if (isset($_GET['db'])) var_dump($avatar);
