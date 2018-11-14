<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Account;

class UnregisteredAuthenticator extends AbstractGuardAuthenticator
{
	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return $request->request->has('uuid') || $request->request->has('udid');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        return [
            'uuid' => $request->request->get('uuid'),
            'udid' => $request->request->get('udid'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if ($credentials['uuid'])
            $player = $this->em->getRepository(Player::class)->find($credentials['uuid']);
        elseif ($credentials['udid']) {
            $player = $this->em->getRepository(Player::class)->findUnregisteredByDeviceID($credentials['udid']);
            if ($player === null) {
                $player = new Player();
                $player->setDeviceID($r->request->get('udid'));
                $this->getEntityManager()->persist($player);
            }
        }

        if (!$player)
            return;

        return $player;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response('-1');
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('-1');
    }

    public function supportsRememberMe()
    {
        return false;
    }
}