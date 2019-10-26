<?php

namespace App\Services;

use App\Entity\Song;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Services that fetches info on Newgrounds songs.
 */
class SongProvider
{
	private $em;
	
	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}
	
    public function fetchSong(int $songID)
    {
		$songObj = $this->em->getRepository(Song::class)->find($songID);
		if ($songObj) {
			return $songObj;
		}
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

		$songObj = new Song();
		$songObj->setId($songID);
		$songObj->setTitle($theSong['2']);
		$songObj->setAuthorName($theSong['4']);
		$songObj->setSize($theSong['5']);
		$songObj->setDownloadUrl($theSong['10']);
		$this->em->persist($songObj);
		$this->em->flush();
	
        return $songObj;
    }
}