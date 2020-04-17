<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TestimonialMail
 *
 * @ORM\Table(name="email__testimonial_recipient")
 * @ORM\Entity()
 */
class TestimonialRecipient
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Customer", inversedBy="testimonialRecipients", cascade={"persist"})
     * @ORM\JoinColumn(name="affiliate_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $affiliate;

    /**
     * @ORM\Column(name="email", type="string", length=50, nullable=true)
     * @Assert\Email(message = "validation.form.not_valid_email")
     */
    private $email;

    /**
     * @ORM\Column(name="firstname", type="string", length=25)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=25)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $lastname;

    /**
     * @ORM\Column(name="message", type="text", length=2500, nullable=true)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $message;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * @param mixed $affiliate
     */
    public function setAffiliate($affiliate)
    {
        $this->affiliate = $affiliate;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}