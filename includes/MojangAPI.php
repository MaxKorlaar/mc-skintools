<?php

    /**
     * Class MojangAPI
     *
     * @author  Max Korlaar
     * @license MIT
     */
    class MojangAPI
    {
        private $sessionURL;
        private $timeout;

        /**
         * @return \MojangAPI
         */
        function __construct()
        {
            $this->sessionURL = 'https://sessionserver.mojang.com/session/minecraft/profile/';
            $this->timeout    = 3;
        }

        /**
         * @param $url
         *
         * @return array
         */
        private function request($url)
        {

            /*
             * Set cURL properties
             */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            $curlOut = curl_exec($ch);

            if ($curlOut === false) {
                return ['success' => false, 'statusCode' => null, 'error' => curl_error($ch)];
            }
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status != '200') {
                return ['success' => false, 'statusCode' => $status, 'error' => null];
            }
            return ['success' => true, 'data' => $curlOut];
        }

        /**
         * @param $uuid
         *
         * @return array
         */
        public function getProfile($uuid)
        {
            $cacheHandler = new cacheHandler(7200);
            $cacheFile    = $cacheHandler->getFromCache($uuid);
            if ($cacheFile !== false) {
                return ['success' => true, 'data' => $cacheFile];
            }
            $request = $this->request($this->sessionURL . $uuid);
            if ($request['success'] === false) return $request;
            $jsonArray    = json_decode($request['data'], true);
            $texturesJSON = $jsonArray['properties'][0];
            $textures     = json_decode(base64_decode($texturesJSON['value']), true);
            if (isset($textures['textures']['SKIN'])) {
                $skinArray = $textures['textures']['SKIN'];
                if (isset($skinArray['metadata']['model'])) {
                    $isSteve = $skinArray['metadata']['model'] !== 'slim';
                } else {
                    $isSteve = true;
                }
                $skinURL = $skinArray['url'];
            } else { // https://github.com/mapcrafter/mapcrafter-playermarkers/blob/master/playermarkers/player.php#L8-L19
                $skinURL = null;
                for ($i = 0; $i < 4; $i++) {
                    $sub[$i] = intval("0x" . substr($uuid, $i * 8, 8) + 0, 16);
                }
                if ((bool)((($sub[0] ^ $sub[1]) ^ ($sub[2] ^ $sub[3])) % 2) === true) {
                    $isSteve = false;
                } else {
                    $isSteve = true;
                }
            }
            $profileData = ['skinURL' => $skinURL, 'isSteve' => $isSteve];
            $cacheHandler->setFileContents($uuid, $profileData);
            return ['success' => true, 'data' => $profileData];
        }

        /**
         * @return int
         */
        public function getTimeout()
        {
            return $this->timeout;
        }

        /**
         * @param int $timeout
         */
        public function setTimeout($timeout)
        {
            $this->timeout = $timeout;
        }

    }

    /**
     * Class cacheHandler
     *
     * @author  Max Korlaar
     * @license MIT
     */
    class cacheHandler
    {
        private $cacheLocation;
        private $cacheTime = 600;

        /**
         * @param int $cacheTime
         * @param     $cacheLocation
         */
        function __construct($cacheTime = 600, $cacheLocation = null)
        {

            if ($cacheLocation === null) $cacheLocation = $_SERVER['DOCUMENT_ROOT'] . '/cache/MojangAPI/';

            if (!file_exists($cacheLocation)) {
                mkdir($cacheLocation, 0777, true);
            }
            $this->cacheLocation = $cacheLocation;
            $this->cacheTime     = $cacheTime;
        }

        /**
         * @param      $filename
         * @param bool $force
         *
         * @return bool
         */
        function getFromCache($filename, $force = false)
        {
            $filename = $this->cacheLocation . urlencode(strtolower($filename)) . '.json';

            if (!file_exists($filename)) {
                return false;
            }

            $file = fopen($filename, 'r+');
            if (filesize($filename) > 0) {
                $content = fread($file, filesize($filename));
            } else {
                fclose($file);
                return false;
            }
            fclose($file);

            $json = json_decode($content, true);

            if (isset($json['timestamp'])) {

                if ($force === false && ((time() - (int)$json['timestamp']) > $this->cacheTime)) {
                    return false;
                } else {
                    if (isset($json['data'])) return $json['data'];
                }

            }
            return false;

        }

        /**
         * @param $filename
         * @param $data
         *
         * @return bool
         */
        function setFileContents($filename, $data)
        {
            $filename = $this->cacheLocation . urlencode(strtolower($filename)) . '.json';
            $file     = fopen($filename, 'w+');

            $jsonArray = [
                'timestamp' => time(),
                'data'      => $data
            ];
            $json      = json_encode($jsonArray);

            fwrite($file, $json);
            fclose($file);
            return true;
        }
    }

?>