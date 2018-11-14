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

class GJPAuthenticator extends AbstractGuardAuthenticator
{
	private $em;
    private $xor;
    private $b64;

	public function __construct(EntityManagerInterface $em, XORCipher $xor, Base64URL $b64)
	{
		$this->em = $em;
        $this->xor = $xor;
        $this->b64 = $b64;
	}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return $request->request->has('accountID') && $request->request->has('gjp');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        return [
            'accountID' => $request->request->get('accountID'),
            'gjp' => $request->request->get('gjp'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $accountID = $credentials['accountID'];

        if (!is_numeric($accountID)) {
            return;
        }

        $user = $this->em->getRepository(Player::class)->findPlayerWithAccountID($accountID);

        if (!$user)
        	return;

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $gjp = $credentials['gjp'];

        if (!$gjp)
            return false;

        $decodedGJP = $this->xor->cipher($this->b64->decode($gjp), XORCipher::KEY_GJP);

        return password_verify($decodedGJP, $user->getPassword());
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