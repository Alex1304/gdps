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
use App\Services\TokenGenerator;
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

        $auth = $em->getRepository(Authorization::class)->forUser($s->getUser()->getAccount()->getId());

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
    public function updateCredentials(Security $s, StrictValidator $v, TokenGenerator $tokenGen, $username, $password, $email)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $s->getUser()->getAccount();
        $auth = $em->getRepository(Authorization::class)->forUser($user->getId()) ?? new Authorization();

        $user->setUsername($username ?? $user->getUsername());
        $user->setPassword($password ?? $user->getPassword());
        $user->setEmail($email ?? $user->getEmail());
        $auth->setToken($tokenGen->generate($user->getPlayer()));
        $auth->setUser($user);

        $v->validate($user);
        $em->flush();

        return [
            'user' => $user,
            'token' => $auth->getToken(),
        ];
    }
    
    /**
     * @Rest\Put("/me/password", name="api_change_password")
     * @Rest\View
     *
     * @Rest\RequestParam(name="password")
     */
    public function changePassword(Security $s, TokenGenerator $tokenGen, $password)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $s->getUser()->getAccount();
        $auth = $em->getRepository(Authorization::class)->forUser($user->getId()) ?? new Authorization();

        $user->setPassword($password);
        $auth->setToken($tokenGen->generate($user->getPlayer()));
        $auth->setUser($user);

        $em->persist($auth);
        $em->flush();

        return [
            'user' => $user,
            'token' => $auth->getToken(),
        ];
    }

    /**
     * @Rest\Post("/public/forgot-password", name="api_forgot_password")
     * @Rest\View
     *
     * @Rest\RequestParam(name="email")
     */
    public function forgotPassword(\Swift_Mailer $mailer, TokenGenerator $tokenGen, $email)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(Account::class)->findOneByEmail($email);

        if (!$user)
            throw new InvalidParametersException("Unknown email");

        $auth = $em->getRepository(Authorization::class)->forUser($user->getId()) ?? new Authorization();
        $auth->setToken($tokenGen->generate($user->getPlayer()));
        $auth->setUser($user);
        $em->persist($auth);
        $em->flush();

        $link = getenv('DASHBOARD_ROOT_URL') . '/recover-password?token=' . $auth->getToken();

        $message = (new \Swift_Message('Reset password'))
            ->setTo($email)
            ->setBody("Hello,\n\nYour username is: " . $user->getUsername() . ".\nTo reset your password, follow this link: " . $link, 'text/plain');

        $mailer->send($message);

        return null;
    }
}
