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
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;

use App\Entity\Authorization;
use App\Entity\Account;
use App\Services\Base64URL;
use App\Services\TokenGenerator;

class PlainPasswordAuthenticator extends AbstractGuardAuthenticator
{
	private $em;
	private $serializer;
	private $b64;
    private $tokenGen;

	public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, Base64URL $b64, TokenGenerator $tokenGen)
	{
		$this->em = $em;
		$this->serializer = $serializer;
		$this->b64 = $b64;
        $this->tokenGen = $tokenGen;
	}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
    	if ($request->attributes->get('_route') !== 'api_token_create' &&
            $request->attributes->get('_route') !== 'account_login')
    		return false;

        if ($request->headers->get('Content-Type') === 'application/x-www-form-urlencoded') {
            return $request->request->has('userName') && $request->request->has('password');
        } else {
            $credentials = $this->serializer->deserialize($request->getContent(), 'array', 'json');
            return isset($credentials['username']) && isset($credentials['password']);
        }
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
		return $this->serializer->deserialize($request->getContent(), 'array', 'json');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
    	try {
    		return $userProvider->loadUserByUsername($credentials['username']);
    	} catch (UsernameNotFoundException $e) {
    		throw new CustomUserMessageAuthenticationException($e->getMessage());
    	}
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if (!password_verify($credentials['password'], $user->getPassword()))
        	throw new CustomUserMessageAuthenticationException('Incorrect password');

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    	$account = $token->getUser();
    	$em = $this->em;

    	if ($request->headers->get('Content-Type') === 'application/x-www-form-urlencoded')
    		return new Response($account->getPlayer()->getId() . ',' . $account->getId());

        $auth = $this->em->getRepository(Authorization::class)->forUser($token->getUser()->getId());
        $status = Response::HTTP_OK;

        if (!$auth) {
        	$status = Response::HTTP_CREATED;
            $auth = new Authorization();
            $auth->setUser($account);
            $auth->setToken($this->tokenGen->generate($account, $this->b64));

            $em->persist($auth);
            $em->flush();
        }

        $response = new Response($this->serializer->serialize($auth, 'json'), $status);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
    	if ($request->headers->get('Content-Type') === 'application/x-www-form-urlencoded')
    		return new Response('-1');

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
    	if ($request->headers->get('Content-Type') === 'application/x-www-form-urlencoded')
    		return new Response('-1');

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