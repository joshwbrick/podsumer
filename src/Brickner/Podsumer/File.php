<?php declare(strict_types = 1);

namespace Brickner\Podsumer;

use \Exception;
use \PDO;

class File
{

    protected Main $main;

    function __construct(Main $main)
    {
        $this->main = $main;
    }

    public function cacheUrl(string $url): string
    {
        $url_hash = $this->hashUrl($url);
        $cached = $this->cacheForHash($url_hash);

        if (empty($cached)) {
            $file_contents = self::downloadUrl($url);
            $this->main->getState()->cacheNewFile($url, $file_contents);
        }

        return $url_hash;
    }

    public function hashUrl(string $url)
    {
        return md5($url);
    }

    public function cacheForHash(string $url_hash): array
    {
        return $this->main->getState()->getFileByUrlHash($url_hash) ?? [];
    }

    public static function downloadUrl($url, $user = null, $pass = null): string
    {
        $curl = curl_init();

        curl_setopt($curl, \CURLOPT_URL, $url);
        curl_setopt($curl, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, \CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, \CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($curl, \CURLOPT_MAXREDIRS, 10);

        if (!empty($user) && !empty($pass)) {
            curl_setopt($curl,\CURLOPT_USERPWD, "$user:$pass");
            curl_setopt($curl, \CURLOPT_HTTPAUTH, \CURLAUTH_ANY);
        }

        $url_contents = curl_exec($curl);
        if (false === $url_contents) {
            throw new Exception('Cannot download url: ' . curl_error($curl));
        }

        return $url_contents;
    }
}
