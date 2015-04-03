<?php
    require_once('MojangAPI.php');

    /**
     * Class MCavatar
     * Modified source code made into class
     *
     * @author      Max Korlaar
     * @description Class to make it easier to get Minecraft skins.
     * @license     MIT
     *              Some code was borrowed from an old opensource github project, which was not
     *              working very well.
     *              TODO Find old URL
     *              Class made by Max Korlaar.

     */
    class MCavatar
    {
        public $name;
        public $skinurl;
        public $size;
        public $imagepath;
        public $cacheInfo;
        public $publicurl;
        public $helm = true;
        public $fetchError = null;

        /**
         * Defines url
         */
        function __construct()
        {
            $this->skinurl   = 'http://skins.minecraft.net/MinecraftSkins/';
            $this->imagepath = $_SERVER['DOCUMENT_ROOT'] . '/status-sig/img/';
        }

        /**
         * @param      $username
         * @param bool $save
         *
         * @return resource|string
         */
        private function getSkin($username, $save = false)
        {
            if (strlen($username) === 32) {
                $api  = new MojangAPI();
                $data = $api->getProfile($username);
                if ($data['success'] === true) {
                    $skinData = $data['data'];
                    if ($skinData['skinURL'] === null) {
                        $imgURL = $skinData['isSteve'] ? 'https://minecraft.net/images/steve.png' : 'https://minecraft.net/images/alex.png';
                    } else {
                        $imgURL = $skinData['skinURL'];
                    }
                    $src = imagecreatefrompng($imgURL);
                    if (!$src) {
                        $src              = imagecreatefrompng("http://www.minecraft.net/skin/char.png");
                        $save             = false;
                        $this->fetchError = true;
                    }
                } else {
                    $src              = imagecreatefrompng("http://www.minecraft.net/skin/char.png");
                    $this->fetchError = true;
                    $save             = false;
                }
            } else {
                $src = @imagecreatefrompng("http://skins.minecraft.net/MinecraftSkins/{$username}.png");
                if (!$src) {
                    $src              = imagecreatefrompng("http://www.minecraft.net/skin/char.png");
                    $this->fetchError = true;
                    $save             = false;
                }
            }
            imageAlphaBlending($src, true);
            imageSaveAlpha($src, true);
            if ($save) {
                $imagepath = $this->imagepath . 'full_skin/' . strtolower($username) . '.png';
                if (!file_exists($this->imagepath . 'full_skin/')) {
                    mkdir($this->imagepath . 'full_skin/', 0777, true);
                }
                imagepng($src, $imagepath);
                return $imagepath;
            } else {
                return $src;
            }
        }

        /**
         * @param      $username
         * @param bool $save
         *
         * @return string
         */
        public function getSkinFromCache($username, $save = false)
        {
            $imagepath       = $this->imagepath . 'full_skin/' . strtolower($username) . '.png';
            $this->publicurl = '/img/full_skin/' . strtolower($username) . '.png';

            if (file_exists($imagepath)) {
                if (filemtime($imagepath) < strtotime('-2 week')) {
                    $this->cacheInfo = 'full skin expired, redownloading';
                    unlink($imagepath);
                    return $this->getSkin($username, true);
                } else {
                    return $imagepath;
                }
            } else {
                $this->cacheInfo = 'full skin image not yet downloaded';
                return $this->getSkin($username, true);
            }
        }

        /**
         * @param      $username
         * @param int  $size
         * @param bool $helm
         *
         * @usage getFromCache('MegaMaxsterful');
         * @return string
         */
        function getFromCache($username, $size = 100, $helm = true)
        {
            if ($helm) {
                $imagepath       = $this->imagepath . $size . 'px/' . strtolower($username) . '.png';
                $this->publicurl = '/img/' . $size . 'px/' . strtolower($username) . '.png';
            } else {
                $imagepath       = $this->imagepath . $size . 'px-no-helm/' . strtolower($username) . '.png';
                $this->publicurl = '/img/' . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }
            $this->name = $username;
            $this->size = $size;
            $this->helm = $helm;

            if (file_exists($imagepath)) {
                if (filemtime($imagepath) < strtotime('-2 week')) {
                    $this->cacheInfo = 'expired, redownloading';
                    unlink($imagepath);
                    return $this->getImage($username, $size, $helm);
                } else {
                    $this->cacheInfo = 'not expired';
                    return $imagepath;
                }
            } else {
                $this->cacheInfo = 'image not yet downloaded';

                return $this->getImage($username, $size, $helm);
            }
        }

        /**
         * @param      $username
         * @param int  $size
         * Always use getFromCache, because that's way more resource friendly etc. You get the message :)
         * @param bool $helm
         * @param bool $save
         *
*@return string
         */
        function getImage($username, $size = 100, $helm = true, $save = true)
        {
            $this->name = $username;
            $this->size = $size;
            if ($helm) {
                $this->publicurl = '/img/' . $size . 'px/' . strtolower($username) . '.png';
            } else {
                $this->publicurl = '/img/' . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }

            if (strlen($username) === 32) {
                $api  = new MojangAPI();
                $data = $api->getProfile($username);
                if ($data['success'] === true) {
                    $skinData = $data['data'];
                    if ($skinData['skinURL'] === null) {
                        $imgURL = $skinData['isSteve'] ? 'https://minecraft.net/images/steve.png' : 'https://minecraft.net/images/alex.png';
                    } else {
                        $imgURL = $skinData['skinURL'];
                    }
                    $src = imagecreatefrompng($imgURL);
                    if (!$src) {
                        $src              = imagecreatefrompng("http://www.minecraft.net/skin/char.png");
                        $this->fetchError = true;
                        $save             = false;
                    }
                } else {
                    $src              = imagecreatefrompng("http://www.minecraft.net/skin/char.png");
                    $this->fetchError = true;
                    $save             = false;
                }
            } else {
                $src = @imagecreatefrompng("http://skins.minecraft.net/MinecraftSkins/{$username}.png");
                if (!$src) {
                    $src              = imagecreatefrompng("http://www.minecraft.net/skin/char.png");
                    $this->fetchError = true;
                    $save             = false;
                }
            }

            $dest = imagecreatetruecolor(8, 8);
            imagecopy($dest, $src, 0, 0, 8, 8, 8, 8);
            if ($helm) {
                $bg_color = imagecolorat($src, 0, 0);
                $no_helm  = true;
                for ($i = 1; $i <= 8; $i++) {
                    for ($j = 1; $j <= 4; $j++) {
                        if (imagecolorat($src, 39 + $i, 7 + $j) != $bg_color) {
                            $no_helm = false;
                        }
                    }

                    if (!$no_helm) {
                        break;
                    }
                }
                if (!$no_helm) {
                    imagecopy($dest, $src, 0, 0, 40, 8, 8, 8);
                }
            }
            $final = imagecreatetruecolor($size, $size);
            imagecopyresized($final, $dest, 0, 0, 0, 0, $size, $size, 8, 8);
            if ($helm) {
                if (!file_exists($this->imagepath . $size . 'px/')) {
                    mkdir($this->imagepath . $size . 'px/', 0777, true);
                }
                $imagepath = $this->imagepath . $size . 'px/' . strtolower($username) . '.png';
            } else {
                if (!file_exists($this->imagepath . $size . 'px-no-helm/')) {
                    mkdir($this->imagepath . $size . 'px-no-helm/', 0777, true);
                }
                $imagepath = $this->imagepath . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }

            if ($save) imagepng($final, $imagepath);
            return $imagepath;
        }

        /**
         * @return mixed
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param mixed $name
         */
        public function setName($name)
        {
            $this->name = $name;
        }
    }

?>
