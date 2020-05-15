<?php

namespace App\Entity\Customer\Email;

use App\Entity\Building\Building;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Email\BaseEmailLog;

/**
 * @ORM\Table(name="email__log")
 * @ORM\Entity(repositoryClass="App\Repository\MemberEmailRepository")
 */
class CustomerEmail extends BaseEmailLog implements BuildingEmailInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Building", inversedBy="emails")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id", nullable=false)
     */
    private $building;

    /**
     * @ORM\Column(name="reply_email", type="string", length=50, nullable=false)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $replyEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="reply_name", type="string", length=255)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $replyName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Email\AutoEmail", inversedBy="emailLog")
     * @ORM\JoinColumn(name="automated_id", referencedColumnName="id", nullable=true)
     */
    protected $automatedEmail;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Email\EmailRecipient", mappedBy="emailLog", cascade={"all"}, orphanRemoval=true)
     * @Assert\NotBlank(message="validation.form.required")
     */
    protected $recipients;

    /**
     * @return Building
     */
    public function getBuilding() : Building
    {
        return $this->building;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding(Building $building)
    {
        $this->building = $building;
    }

    /**
     * @return string
     */
    public function getReplyEmail()
    {
        return $this->replyEmail;
    }

    /**
     * @param mixed $replyEmail
     */
    public function setReplyEmail(string $replyEmail)
    {
        $this->replyEmail = $replyEmail;
    }

    /**
     * @return string
     */
    public function getReplyName()
    {
        return $this->replyName;
    }

    /**
     * @param string $replyName
     * @return mixed|void
     */
    public function setReplyName(string $replyName)
    {
        $this->replyName = $replyName;
    }

    /**
     * @return array
     */
    public static function getMacros()
    {
        $macros = [
            'MemberData' => [
                'BuildingName' => 'Building name',
                'Firstname' => 'First name',
                'Lastname' => 'Last name',
                'Notes' => 'Notes',
                'CustomerEmail' => 'CustomerEmail',
                'Phone' => 'Phone',
                'ProfileLink' => 'Profile link'
            ]
        ];

        return $macros;
    }
}