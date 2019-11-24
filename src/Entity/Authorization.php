<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthorizationRepository")
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="token_uq", columns={"token"})
 *     }
 * )
 *
 * @Serializer\ExclusionPolicy("ALL")
 */
class Authorization
{
	const SCOPE_LOGIN = 0;
	const SCOPE_ACCOUNT_VERIFY = 1;
	const SCOPE_PASSWORD_RESET = 2;
	
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Serializer\Expose
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, options={"collation":"utf8_unicode_ci"})
     *
     * @Serializer\Expose
     */
    private $token;

    /**
     * @ORM\Column(type="integer")
     */
    private $scope;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Account
    {
        return $this->user;
    }

    public function setUser(Account $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getScope(): ?int
    {
        return $this->scope;
    }

    public function setScope(int $scope): self
    {
        $this->scope = $scope;

        return $this;
    }
}
