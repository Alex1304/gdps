<?php

namespace App\Services;

use App\Entity\Account;
use App\Entity\Player;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class GDUserProvider implements UserProviderInterface
{
    private $em;
    private $udid;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setUdid($udid)
    {
        $this->udid = $udid;
    }

    public function loadUserByUsername($username)
    {
        $em = $this->em;
        $account = $em->getRepository(Account::class)->findOneByUsername($username);

        if ($account) {
            $player = $em->getRepository(Player::class)->findUnregisteredByDeviceID($this->udid);
            if (!$account->getPlayer()) {
				if (!$player) {
					$player = new Player();
					$player->setName('Player');
					$player->setDeviceID($this->udid);
					$player->setStars(0);
					$player->setDemons(0);
					$player->setDiamonds(0);
					$player->setIcon(0);
					$player->setColor1(0);
					$player->setColor2(0);
					$player->setIconType(0);
					$player->setCoins(0);
					$player->setUserCoins(0);
					$player->setSpecial(0);
					$player->setAccIcon(0);
					$player->setAccShip(0);
					$player->setAccBall(0);
					$player->setAccUFO(0);
					$player->setAccWave(0);
					$player->setAccRobot(0);
					$player->setAccGlow(0);
					$player->setAccSpider(0);
					$player->setAccExplosion(0);
					$player->setStatsLastUpdatedAt(new \DateTime());
					$player->setLastQuestId(0);
					$player->setNextQuestsAt(new \DateTime());
					$player->setCreatorPoints(0);
				}
                $player->setAccount($account);
                $em->persist($player);
                $em->flush();
            } else {
                // If the account already has an associated player, the other player instance with the same deviceID 
				// will be destroyed, unless he has some levels uploaded.
                if ($player && !count($player->getLevels())) {
                    $em->remove($player);
                    $em->flush();
                }
            }

            return $account->getPlayer();
        }

        throw new UsernameNotFoundException(
            sprintf('User with username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return Player::class === $class;
    }
}