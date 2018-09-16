<?php

namespace App\ApiController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
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
     *     path="/token",
     *     name="api_token_create"
     * )
     */
    public function createToken()
    {
        // Everything is handled in PlainPasswordAuthenticator service
    }

    /**
     * @Rest\Delete(
     *     path="/token",
     *     name="api_token_destroy"
     * )
     * 
     * @Rest\View
     */
    public function destroyToken(Security $security)
    {
        $em = $this->getDoctrine()->getManager();

        $auth = $em->getRepository(Authorization::class)->forUser($security->getUser()->getId());

        $em->remove($auth);
        $em->flush();

        return null;
    }
}
