<?php

namespace App\Services;

/**
 * Services that fetches info on Newgrounds songs.
 */
class SongProvider
{
    public function fetchSong(int $songID)
    {
        $url = 'http://162.216.16.96/database/getGJSongInfo.php';
        $data = ['songID' => $songID, 'secret' => 'Wmfd2893gb7'];
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if (empty($result) || is_numeric($result))
            return $result = '-2' ? '-2' : '-1';

        $songData = explode('~|~', $result);

        $theSong = [];

        for ($i = 0 ; $i < count($songData) - 1 ; $i += 2)
            $theSong[$songData[$i]] = $songData[$i + 1];

        return $theSong;
    }
}