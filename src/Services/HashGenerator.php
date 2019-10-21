<?php

namespace App\Services;

/**
 * Services that generates a hash for various components of the game
 */
class HashGenerator
{
    const SALT_1 = 'xI25fpAapCQg';
    const SALT_2 = 'oC36fpYaPtdg';
	const SALT_3 = 'pC26fpYaQCtg';

    private $b64;
    private $xor;

    public function __construct(Base64URL $b64, XORCipher $xor)
    {
        $this->b64 = $b64;
        $this->xor = $xor;
    }

    /**
     * Generates a hash for the given array of levels
     */
    public function generateForLevelsArray(array $levels)
    {
        $result = '';

        foreach ($levels as $level) {
            $idstring = $level->getId() . '';
            $result .= $idstring[0];
            $result .= $idstring[strlen($idstring) - 1];
            $result .= $level->getStars();
            $result .= $level->getHasCoinsVerified() ? '1' : '0';
        }

        return sha1($result . self::SALT_1);
    }

    /**
     * Generates two hashes for the given level. The first hash is on the level data, the second one on the level info
     */
    public function generateForLevel($level, $periodicID = 0)
	{
        $result = 'aaaaa';
        $len = strlen($level->getData());
        $divided = intval($len / 40);
        $p = 0;

        for($k = 0 ; $k < $len ; $k = $k + $divided) {
            if($p > 39)
                break;

            $result[$p] = $level->getData()[$k]; 
            $p++;
        }

        $info = $level->getCreator()->getId();
        $info .= ',';
        $info .= $level->getStars();
        $info .= ',';
        $info .= $level->getIsDemon() ? '1' : '0';
        $info .= ',';
        $info .= $level->getId();
        $info .= ',';
        $info .= $level->getHasCoinsVerified() ? '1' : '0';
        $info .= ',';
        $info .= $level->getFeatureScore();
        $info .= ',';
        $info .= $level->getPassword();
        $info .= ',';
        $info .= $periodicID;

        return [
            'data' => sha1($result . self::SALT_1),
            'info' => sha1($info . self::SALT_1)
        ];
    }
	
	public function generateForQuests($questString)
	{
		return sha1($questString . self::SALT_2);
	}
	
	public function generateForChests($questString)
	{
		return sha1($questString . self::SALT_3);
	}
}