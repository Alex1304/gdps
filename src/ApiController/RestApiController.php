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
use App\Entity\LevelReport;
use App\Entity\PeriodicLevel;
use App\Entity\Player;
use App\Entity\LevelSuggestion;
use App\Services\EmailNotifier;
use App\Services\GDAuthChecker;
use App\Services\Base64URL;
use App\Services\DifficultyCalculator;
use App\Services\PlayerManager;
use App\Services\XORCipher;
use App\Services\StrictValidator;
use App\Services\TokenGenerator;
use App\Exceptions\InvalidParametersException;
use App\Exceptions\AccessDeniedException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use \ReCaptcha\ReCaptcha;

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

        $auth = $em->getRepository(Authorization::class)->forUser($s->getUser()->getAccount()->getId(), Authorization::SCOPE_LOGIN);

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
            throw new InvalidParametersException("Cannot skip current level: no other level is queued");
        
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
    public function applyRating(DifficultyCalculator $dc, PlayerManager $pm, $level_id, $stars, $verify_coins, $is_epic, $featured_score)
    {
        $em = $this->getDoctrine()->getManager();

        $level = $em->getRepository(Level::class)->find($level_id);
        if (!$level) {
            throw new InvalidParametersException("Unknown level");
		}
		
		if ($stars !== null) {
			$level->setStars($stars);
			$level->setDifficulty($stars ? $dc->getDifficultyForStars($stars) : $dc->getDifficultyForVotes($level));
			$level->setIsAuto($stars == 1);
			$level->setIsDemon($stars == 10);
		}
		if (($stars !== null && $stars != $level->getStars()) || ($verify_coins !== null && !$verify_coins == $level->getHasCoinsVerified())) {
			$level->setRewardsGivenAt($stars > 0 || $verify_coins ? new \DateTime() : null);
		}
        $level->setHasCoinsVerified(!!($verify_coins ?? $level->getHasCoinsVerified()));
		$level->setIsEpic(!!($is_epic ?? $level->getIsEpic()));
		$level->setFeatureScore($featured_score ?? $level->getFeatureScore());
		
		// Delete all associated mod sends and auto update cp of creator
		foreach ($em->getRepository(LevelSuggestion::class)->findSuggestionsForLevel($level) as $s) {
			$em->remove($s);
		}
		$pm->updateCreatorPoints($level->getCreator());
		
        $em->flush();

        return null;
    }
	
	/**
     * @Rest\Get("/admin/mod-list", name="api_admin_get_mod_list")
     * @Rest\View
     */
	public function getModList()
	{
        $em = $this->getDoctrine()->getManager();
		
		$mods = $em->getRepository(Player::class)->findModList();
		return [
			'count' => count($mods),
			'data' => $mods,
		];
	}
	
	/**
     * @Rest\Patch("/admin/mod-list", name="api_admin_change_user_roles")
     * @Rest\View
	 *
	 * @Rest\RequestParam(name="player_name")
	 * @Rest\RequestParam(name="roles_to_add")
	 * @Rest\RequestParam(name="roles_to_remove")
     */
	public function changeUserRoles($player_name, $roles_to_add, $roles_to_remove)
	{
		$em = $this->getDoctrine()->getManager();
		
		$account = $em->getRepository(Account::class)->findOneByUsername($player_name);
		if (!$account) {
			throw new InvalidParametersException('Unknown player');
		}
		$player = $account->getPlayer();
		$player->removeRoles($roles_to_remove);
		$player->addRoles($roles_to_add);
		$em->persist($player);
		$em->flush();
		
		return null;
	}
	
	/**
     * @Rest\Get("/admin/mod-suggestions", name="api_admin_get_mod_suggestions")
     * @Rest\View
	 *
	 * @Rest\QueryParam(name="min_stars", nullable=true, default=1, requirements={"rule"="[1-9]|10", "error_message"="Invalid min stars"})
	 * @Rest\QueryParam(name="max_stars", nullable=true, default=10, requirements={"rule"="[1-9]|10", "error_message"="Invalid max stars"})
	 * @Rest\QueryParam(name="max_song_uses", nullable=true, default=0, requirements={"rule"="[0-9]+", "error_message"="Invalid max song uses"})
	 * @Rest\QueryParam(name="sort_mode", nullable=true, default=0, requirements={"rule"="-?[0-2]", "error_message"="Invalid sort mode"})
     */
	public function getModSuggestions($min_stars, $max_stars, $max_song_uses, $sort_mode)
	{
		if ($min_stars > $max_stars) {
			throw new InvalidParametersException('min_stars cannot be greater than max_stars');
		}
		$em = $this->getDoctrine()->getManager();
		
		$modSends = $em->getRepository(LevelSuggestion::class)->findSuggestionsByCriteria($min_stars, $max_stars, $max_song_uses, $sort_mode);
		
		return [
			'count' => count($modSends),
			'data' => $modSends,
		];
	}
	
	/**
	 * @Rest\Delete("/admin/mod-suggestions", name="api_admin_remove_mod_suggestion")
     * @Rest\View
	 *
     * @Rest\QueryParam(name="level_id", requirements={"rule"="[0-9]+", "error_message"="Invalid level ID"})
	 */
	public function removeModSuggestion($level_id)
	{
		$em = $this->getDoctrine()->getManager();
		$suggestions = $em->getRepository(LevelSuggestion::class)->findSuggestionsForLevel($level_id);
		foreach ($suggestions as $s) {
			$em->remove($s);
		}
		$em->flush();
		
		return null;
	}
	
	/**
     * @Rest\Get("/admin/level-reports", name="api_admin_level_reports")
     * @Rest\View
	 *
	 * @Rest\QueryParam(name="sort_mode", nullable=true, default=0, requirements={"rule"="-?[0-2]", "error_message"="Invalid sort mode"})
     */
	public function getLevelReports($sort_mode)
	{
		$em = $this->getDoctrine()->getManager();
		
		$reports = $em->getRepository(LevelReport::class)->findReportsGroupedByLevel($sort_mode);
		
		return [
			'count' => count($reports),
			'data' => $reports,
		];
	}
	
	/**
	 * @Rest\Delete("/admin/level-reports", name="api_admin_remove_level_report")
     * @Rest\View
	 *
     * @Rest\QueryParam(name="level_id", requirements={"rule"="[0-9]+", "error_message"="Invalid level ID"})
	 */
	public function removeLevelReport($level_id)
	{
		$em = $this->getDoctrine()->getManager();
		$reports = $em->getRepository(LevelReport::class)->findReportsForLevel($level_id);
		foreach ($reports as $r) {
			$em->remove($r);
		}
		$em->flush();
		
		return null;
	}
	
	/**
	 * @Rest\Delete("/admin/levels", name="api_admin_delete_level")
     * @Rest\View
	 *
     * @Rest\QueryParam(name="level_id", requirements={"rule"="[0-9]+", "error_message"="Invalid level ID"})
	 */
	public function deleteLevel(PlayerManager $pm, $level_id)
	{
		$em = $this->getDoctrine()->getManager();
		$level = $em->getRepository(Level::class)->find($level_id);
		if (!$level) {
			throw new InvalidParametersException('Unknown level');
		}
		$creator = $level->getCreator();
		$em->remove($level);
		$em->flush();
		
		$pm->updateCreatorPoints($creator);
		
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
    public function updateCredentials(Security $s, StrictValidator $v, TokenGenerator $tokenGen, EmailNotifier $en, $username, $password, $email)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $s->getUser()->getAccount();
        $auth = $em->getRepository(Authorization::class)->forUser($user->getId(), Authorization::SCOPE_LOGIN);
		
		$requireEmailReverification = $email && $email != $user->getEmail();

        $user->setUsername($username ?? $user->getUsername());
        $user->setPassword($password ?? $user->getPassword());
        $user->setEmail($email ?? $user->getEmail());
		$auth = $auth ?? new Authorization();
		$auth->setUser($user);
		$auth->setToken($tokenGen->generate($user->getPlayer()));
		$auth->setScope($requireEmailReverification ? Authorization::SCOPE_ACCOUNT_VERIFY : Authorization::SCOPE_LOGIN);

        $v->validate($user);
		
		if ($requireEmailReverification) {
			$user->setIsVerified(false);
			$en->sendAccountVerificationEmail($auth);
		}

        $em->flush();
		
        return [
            'user' => $requireEmailReverification ? null : $user,
            'token' => $requireEmailReverification ? null : $auth->getToken(),
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
        $auth = $em->getRepository(Authorization::class)->forUser($user->getId(), Authorization::SCOPE_LOGIN) ?? new Authorization();

        $user->setPassword($password);
        $auth->setToken($tokenGen->generate($user->getPlayer()));
        $auth->setUser($user);
		$auth->setScope(Authorization::SCOPE_LOGIN);
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
    public function forgotPassword(EmailNotifier $en, TokenGenerator $tokenGen, $email)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(Account::class)->findOneByEmail($email);

        if (!$user)
            throw new InvalidParametersException("Unknown email");

        $auth = $em->getRepository(Authorization::class)->forUser($user->getId(), Authorization::SCOPE_PASSWORD_RESET) ?? new Authorization();
        $auth->setToken($tokenGen->generate($user->getPlayer()));
        $auth->setUser($user);
		$auth->setScope(Authorization::SCOPE_PASSWORD_RESET);
        $em->persist($auth);
        $em->flush();

        $en->sendPasswordResetEmail($auth);

        return null;
    }

    /**
     * @Rest\Post("/public/verify", name="api_verify_account")
     * @Rest\View
	 *
	 * @Rest\RequestParam(name="token")
	 * @Rest\RequestParam(name="captcha_response")
     */
    public function verifyAccount($token, $captcha_response)
    {
		$recaptcha = new ReCaptcha(getenv('GOOGLE_RECAPTCHA_SITE_KEY'));
		$recaptchaResult = $recaptcha->verify($captcha_response, $_SERVER['REMOTE_ADDR']);
		
		if (!$recaptchaResult->isSuccess()) {
			throw new InvalidParametersException("Invalid captcha");
		}
		
        $em = $this->getDoctrine()->getManager();
		
        $auth = $em->getRepository(Authorization::class)->findOneBy([
			'token' => $token,
			'scope' => Authorization::SCOPE_ACCOUNT_VERIFY,
		]);
		if (!$auth) {
            throw new InvalidParametersException("Invalid token");
		}
		
		$user = $auth->getUser();
		$user->setIsVerified(true);
		$em->remove($auth);
		$em->flush();
		
		return null;
    }
	
	/**
     * @Rest\Post("/public/resend-verification", name="api_resend_verification")
     * @Rest\View
	 *
	 * @Rest\RequestParam(name="username")
	 * @Rest\RequestParam(name="email", nullable=true, default=null)
     */
    public function resendVerification(StrictValidator $v, EmailNotifier $en, TokenGenerator $tokenGen, $username, $email)
    {
		$em = $this->getDoctrine()->getManager();
		
        $account = $em->getRepository(Account::class)->findOneByUsername($username);
		if (!$account) {
            throw new InvalidParametersException("Username " . $username . " does not exist");
		}
		if ($account->getIsVerified()) {
			throw new AccessDeniedException("This account is already verified");
		}
		if ($email) {
			$account->setEmail($email);
			$v->validate($account);
		}
		
		$auth = $em->getRepository(Authorization::class)->forUser($account, Authorization::SCOPE_ACCOUNT_VERIFY) ?? new Authorization();
		$auth->setUser($account);
		$auth->setToken($tokenGen->generate($account));
		$auth->setScope(Authorization::SCOPE_ACCOUNT_VERIFY);
		$em->flush();
		
		$en->sendAccountVerificationEmail($auth);
		
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
