<?php

namespace App\ApiController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationList;

use App\Entity\Account;
use App\Entity\Authorization;
use App\Services\GDAuthChecker;
use App\Services\Base64URL;
use App\Services\StrictValidator;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\InvalidParametersException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class RestApiController extends FOSRestController
{
    /**
     * @Rest\Post("/token", name="api_token_create")
     */
    public function createToken()
    {
        // Everything is handled in PlainPasswordAuthenticator service
    }

    /**
     * @Rest\Delete("/token", name="api_token_destroy")
     * @Rest\View
     */
    public function destroyToken(Security $s)
    {
        $em = $this->getDoctrine()->getManager();

        $auth = $em->getRepository(Authorization::class)->forUser($s->getUser()->getId());

        $em->remove($auth);
        $em->flush();

        return null;
    }

    /**
     * @Rest\Put("/me/credentials", name="api_update_credentials")
     * @Rest\View
     *
     * @Rest\RequestParam(name="username", nullable=true, default=null)
     * @Rest\RequestParam(name="password", nullable=true, default=null)
     * @Rest\RequestParam(name="email", nullable=true, default=null)
     */
    public function updateCredentials(Security $s, StrictValidator $v, $username, $password, $email)
    {
        $user = $s->getUser();

        $user->setUsername($username ?? $user->getUsername());
        $user->setPassword($password ?? $user->getPassword());
        $user->setEmail($email ?? $user->getEmail());

        $v->validate($user);

        $this->getDoctrine()->getManager()->flush();

        return $user;
    }
}
