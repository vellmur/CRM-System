<?php

namespace App\Entity\Owner\Email;

use App\Entity\Owner\Owner;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Email\BaseRecipient;

/**
 * @ORM\Table(name="email__recipient",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="owner_email_recipient", columns={"log_id", "owner_id"})}))
 * @ORM\Entity(repositoryClass="App\Repository\EmailRecipientRepository")
 */
class EmailRecipient extends BaseRecipient implements OwnerRecipientInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Owner\Email\OwnerEmail", inversedBy="recipients")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id", nullable=false)
     */
    protected $emailLog;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Owner\Owner", inversedBy="emails")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true)
     */
    private $owner;

    /**
     * @return Owner
     */
    public function getOwner() : Owner
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner(Owner $owner)
    {
        if (is_array($owner)) $owner = $owner[0];

        $this->owner = $owner;
    }
}