<?php

namespace App\Entity\Master\Email;

use App\Entity\Email\BaseRecipient;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="master__email_recipient", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="master_email_recipient", columns={"email_id", "user_id"})
 * }))
 * @ORM\Entity(repositoryClass="App\Repository\RecipientRepository")
 */
class Recipient extends BaseRecipient implements UserRecipientInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Master\Email\Email", inversedBy="recipients")
     * @ORM\JoinColumn(name="email_id", referencedColumnName="id", nullable=false)
     */
    protected $emailLog;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="emailRecipients")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;

    }
}