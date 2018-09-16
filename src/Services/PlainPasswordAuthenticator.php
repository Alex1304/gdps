<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;

use App\Entity\Authorization;
use App\Entity\Account;
use App\Services\Base64URL;

class PlainPasswordAuthenticator extends AbstractGuardAuthenticator
{
	private $em;
	private $serializer;
	private $b64;

	public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, Base64URL $b64)
	{
		$this->em = $em;
		$this->serializer = $serializer;
		$this->b64 = $b64;
	}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
    	if ($request->attributes->get('_route') !== 'api_token_create')
    		return false;

		$credentials = $this->serializer->deserialize($request->getContent(), 'array', 'json');

		return isset($credentials['username']) && isset($credentials['password']);
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
    	return $userProvider->loadUserByUsername($credentials['username']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return password_verify($credentials['password'], $user->getPassword());
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
            $auth->setToken($this->generateToken($account, $this->b64));

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

	private function generateToken($account, $b64)
	{
		$length = 32;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }

		return str_replace('=', '', $b64->encode(time()) . '.' . $b64->encode($account->getId())) . '.' . $randomString;
	}
}