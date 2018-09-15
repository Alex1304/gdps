<?php

namespace App\Services;

use App\Entity\Account;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class GDUserProvider implements UserProviderInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository(Account::class)->findOneByUsername($username);

        if ($user)
            return $user;

        throw new UsernameNotFoundException(
            sprintf('User with username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return Account::class === $class;
    }
}