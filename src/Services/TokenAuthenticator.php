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

use App\Entity\Authorization;

class TokenAuthenticator extends AbstractGuardAuthenticator
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
    	if ($request->attributes->get('_route') === 'api_token_create')
    		return false;

        return $request->headers->has('X-Auth-Token');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $routesRequiringPasswordConfirmation = [
            'api_update_credentials',
        ];

        if (in_array($request->attributes->get('_route'), $routesRequiringPasswordConfirmation)) {
            if (!$request->request->has('current_password'))
                throw new CustomUserMessageAuthenticationException("Password confirmation is required");

            $password = $request->request->get('current_password');
        }
        else
            $password = null;

        return [
            'token' => $request->headers->get('X-Auth-Token'),
            'password' => $password,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiKey = $credentials['token'];

        if ($apiKey === null) {
            return;
        }

        $auth = $this->em->getRepository(Authorization::class)->findOneByToken($apiKey);

        if (!$auth)
        	throw new CustomUserMessageAuthenticationException("Invalid token");

        return $auth->getUser();
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($credentials['password'] === null)
            return true;

        if (!password_verify($credentials['password'], $user->getPassword()))
            throw new CustomUserMessageAuthenticationException("Current password is incorrect");

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
        	'code' => Response::HTTP_FORBIDDEN,
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
        	'code' => Response::HTTP_UNAUTHORIZED,
            'message' => 'Authentication Required'
        );
 
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}