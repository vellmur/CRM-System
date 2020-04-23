<?php

namespace App\Entity\Master\Email;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Email\AutomatedEmailBase;

/**
 * @ORM\Table(name="master__email_automated")
 * @ORM\Entity()
 */
class AutomatedEmail extends AutomatedEmailBase
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Master\Email\Email", mappedBy="automatedEmail")
     */
    private $emailLog;

    const AUTOMATED_TYPES = [
        1 => 'confirmation',
        2 => 'welcome',
        3 => 'failed',
        4 => 'aborted'
    ];
}