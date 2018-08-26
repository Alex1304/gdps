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

        return [
            'id' => $songData[1],
            'name' => $songData[3],
            'authorID' => $songData[5],
            'authorName' => $songData[7],
            'size' => $songData[9],
            'download' => $songData[13],
        ];
    }
}