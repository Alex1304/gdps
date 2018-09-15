<?php

namespace App\ApiController;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use App\Entity\Account;
use App\Entity\Authorization;
use App\Services\GDAuthChecker;
use App\Services\Base64URL;
use App\Exceptions\UnauthorizedException;

class RestApiController extends FOSRestController
{
	/**
	 * @Rest\Post(
	 *     path="/login"
	 * )
	 * @Rest\View
	 */
	public function login(Request $r, GDAuthChecker $gdac, Base64URL $b64)
	{
		$em = $this->getDoctrine()->getManager();

		$credentials = $this->get('jms_serializer')->deserialize($r->getContent(), 'array', 'json');

		if (!$credentials['username'] || !$credentials['password'])
			throw new UnauthorizedException('Username or password not provided');

		$account = $em->getRepository(Account::class)->findOneByUsername($credentials['username']);

		if (!$account || !$gdac->checkPlain($account, $credentials['password']))
			throw new UnauthorizedException('Wrong username or password');

		$auth = $em->getRepository(Authorization::class)->forUser($account->getId());

		if (!$auth) {
			$auth = new Authorization();
			$auth->setUser($account);
			$auth->setToken($this->generateToken($account, $b64));

			$em->persist($auth);
			$em->flush();
		}

		return $auth;
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

		return $b64->encode(time()) . '.' . $b64->encode($account->getId()) . '.' . $randomString;
	}
}
