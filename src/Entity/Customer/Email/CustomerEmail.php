<?php

namespace App\Entity\Customer\Email;

use App\Entity\Client\Client;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Email\BaseEmailLog;

/**
 * @ORM\Table(name="email__log")
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 */
class CustomerEmail extends BaseEmailLog implements ClientEmailInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="emails")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

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
     * @return Client
     */
    public function getClient() : Client
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
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
                'ClientName' => 'Client name',
                'Firstname' => 'First name',
                'Lastname' => 'Last name',
                'Notes' => 'Notes',
                'CustomerEmail' => 'CustomerEmail',
                'Phone' => 'Phone',
                'DeliveryDay' => 'Delivery day',
                'ProfileLink' => 'Profile link',
                'SkipWeek' => 'Skip a week link',
                'CustomizeShare' => 'Customize a share link',
                'RenewLink' => 'Renewal link',
                'FeedbackLinks' => 'Feedback links'
            ],
            'ShareData' => [
                'ShareName' => 'Name',
                'ShareRenewal' => 'Renewal date',
                'ShareStatus' => 'Status',
                'ShareDay' => 'Share day',
                'ShareLocation' => 'Share location',
                'AvailablePlants' => 'Available plants'
            ],
            'BillingAddressData' => [
                'BilType' => 'Address Type',
                'BilStreet' => 'Street',
                'BilApartment' => 'Apartment',
                'BilPostalCode' => 'PostalCode',
                'BilCity' => 'City',
                'BilState' => 'Province/State'
            ],
            'DeliveryAddressData' => [
                'DelType' => 'Address Type',
                'DelStreet' => 'Street',
                'DelApartment' => 'Apartment',
                'DelPostalCode' => 'PostalCode',
                'DelCity' => 'City',
                'DelState' => 'Province/State'
            ]
        ];

        return $macros;
    }
}