<?php

namespace App\ApiController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationList;

use App\Entity\Account;
use App\Entity\Authorization;
use App\Entity\Level;
use App\Entity\PeriodicLevel;
use App\Services\GDAuthChecker;
use App\Services\Base64URL;
use App\Services\DifficultyCalculator;
use App\Services\XORCipher;
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
     * @Rest\Get("/admin/periodic", name="api_admin_get_periodic_levels")
     * @Rest\View
     *
     * @Rest\QueryParam(name="type", requirements="0|1")
     */
    public function getPeriodicLevelsTable($type)
    {
        $em = $this->getDoctrine()->getManager();

        $currentPeriodic = $em->getRepository(PeriodicLevel::class)->findCurrentOfType($type);
        $periodics = $em->getRepository(PeriodicLevel::class)->findQueuedOfType($type);

        return [
            'periodic_type' => $type,
            'current' => $currentPeriodic,
            'queued' => $periodics,
        ];
    }

    /**
     * @Rest\Post("/admin/periodic", name="api_admin_append_periodic_level")
     * @Rest\View(StatusCode=201)
     *
     * @Rest\RequestParam(name="level_id", requirements={"rule"="[0-9]+", "error_message"="Invalid level ID"})
     * @Rest\RequestParam(name="type", requirements="0|1")
     */
    public function appendPeriodicLevel($level_id, $type)
    {
        $em = $this->getDoctrine()->getManager();

        $level = $em->getRepository(Level::class)->find($level_id);
        if (!$level)
            throw new InvalidParametersException("Unknown level");

        $latest = $em->getRepository(PeriodicLevel::class)->findLatestOfType($type);

        $dateStart = \DateTimeImmutable::createFromMutable(
            $latest ? $latest->getPeriodEnd() : PeriodicLevel::dateStartForType($type));

        $periodic = new PeriodicLevel();
        $periodic->setLevel($level);
        $periodic->setType($type);
        $periodic->setPeriodStart($dateStart);
        $periodic->setPeriodEnd($dateStart->add(PeriodicLevel::intervalForType($type)));
        $em->persist($periodic);
        $em->flush();

        return $periodic;
    }

    /**
     * @Rest\Delete("/admin/periodic", name="api_admin_delete_priodic_level")
     * @Rest\View
     * 
     * @Rest\QueryParam(name="index", requirements={"rule"="[0-9]+", "error_message"="Invalid index"})
     */
    public function deletePeriodicLevel($index)
    {
        $em = $this->getDoctrine()->getManager();

        $periodic = $em->getRepository(PeriodicLevel::class)->findIfNotPast($index);
        if (!$periodic)
            throw new InvalidParametersException("Unknown index");

        $type = $periodic->getType();

        $periodicsToShift = $em->getRepository(PeriodicLevel::class)->findFromDateOfType($type, $periodic->getPeriodEnd());
        if (!count($periodicsToShift))
            throw new InvalidParametersException(sprintf("Cannot skip current level: no other level is queued", $index));
        
        $em->remove($periodic);

        foreach ($periodicsToShift as $p) {
            $start = \DateTimeImmutable::createFromMutable($p->getPeriodStart());
            $end = \DateTimeImmutable::createFromMutable($p->getPeriodEnd());
            $p->setPeriodStart($start->sub(PeriodicLevel::intervalForType($type)));
            $p->setPeriodEnd($end->sub(PeriodicLevel::intervalForType($type)));
        }

        $em->flush();

        return null;
    }

    /**
     * @Rest\Patch("/admin/apply-rating", name="api_admin_apply_rating")
     * @Rest\View
     *
     * @Rest\RequestParam(name="level_id", requirements={"rule"="[0-9]+", "error_message"="Invalid level ID"})
	 * @Rest\RequestParam(name="stars", nullable=true, default=null, requirements={"rule"="[0-9]|10", "error_message"="Invalid amount of stars"})
	 * @Rest\RequestParam(name="verify_coins", nullable=true, default=null)
	 * @Rest\RequestParam(name="is_epic", nullable=true, default=null)
	 * @Rest\RequestParam(name="featured_score", nullable=true, default=null, requirements={"rule"="[0-9]+", "error_message"="Invalid featured score"})
     */
    public function applyRating(DifficultyCalculator $dc, $level_id, $stars, $verify_coins, $is_epic, $featured_score)
    {
        $em = $this->getDoctrine()->getManager();

        $level = $em->getRepository(Level::class)->find($level_id);
        if (!$level) {
            throw new InvalidParametersException("Unknown level");
		}
		if ($stars || $verify_coins){
			$level->setRewardsGivenAt(new \DateTime());
		}
		if ($stars) {
			$level->setStars($stars);
			$level->setDifficulty($dc->getDifficultyForStars($stars));
		}
        $level->setHasCoinsVerified(!!($verify_coins ?? $level->getHasCoinsVerified()));
		$level->setIsEpic(!!($is_epic ?? $level->getIsEpic()));
		$level->setFeatureScore($featured_score ?? $level->getFeatureScore());
		
        $em->persist($level);
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
	
	/**
	 * @Rest\Get("/public/xor", name="api_xor")
	 * @Rest\View
	 *
	 * @Rest\QueryParam(name="message")
	 * @Rest\QueryParam(name="key")
	 */
	public function testXor(XORCipher $xor, Base64URL $b64, $message, $key)
	{
		$result = $xor->cipher($b64->decode($message), '' . $key);
		return [
			'result' => $result,
		];
	}
}
