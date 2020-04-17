<?php

namespace App\Entity\Master\Email;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Email\BaseEmailLog;

/**
 * @ORM\Table(name="master__email")
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 */
class Email extends BaseEmailLog
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Master\Email\AutomatedEmail", inversedBy="emailLog")
     * @ORM\JoinColumn(name="automated_id", referencedColumnName="id", nullable=true)
     */
    protected $automatedEmail;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Master\Email\Recipient", mappedBy="emailLog", cascade={"all"}, orphanRemoval=true)
     * @Assert\NotBlank(message="validation.form.required")
     */
    protected $recipients;
}