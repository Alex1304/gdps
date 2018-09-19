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
     * @Rest\Put("/me/username", name="api_change_username")
     * @Rest\View
     *
     * @ParamConverter("data", converter="fos_rest.request_body")
     */
    public function changeUsername(Security $s, Account $data)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $s->getUser();

        $user->setUsername($data->getUsername());

        $em->persist($user);

        try {
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new InvalidParametersException("This username is already taken");
        }

        return $user;
    }

    /**
     * @Rest\Put("/me/password", name="api_change_password")
     * @Rest\View
     */
    public function changePassword(Request $r, Security $s)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $s->getUser();

        $data = $this->get('jms_serializer')->deserialize($r->getContent(), 'array', 'json');

        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
