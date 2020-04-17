<?php

namespace App\Entity\Customer;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 *
 * @ORM\Table(name="customer__vendor_contact")
 * @ORM\Entity()
 * @UniqueEntity(fields="name", errorPath="name", message="validation.form.unique")
 */
class Contact
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer\Vendor", inversedBy="contacts")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id", nullable=false)
     */
    private $vendor;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $name;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email(message = "validation.form.not_valid_email")
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $email;

    /**
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(name="token", type="string", length=50)
     */
    private $token;

    /**
     * @var boolean
     * @ORM\Column(name="notify_enabled", type="boolean")
     */
    private $notifyEnabled = 1;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param mixed $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = mb_strtoupper($name, "utf-8");;
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
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = substr(sha1(openssl_random_pseudo_bytes(50)) . sha1($token), 0, 50);
    }

    /**
     * @param null $baseUrl
     * @return null|string
     */
    public function getAccessLink($baseUrl = null)
    {
        $url = $baseUrl ? $baseUrl : 'http://' . $_SERVER['HTTP_HOST'];
        $url .= '/membership/vendor/profile/' . $this->getToken();

        return $url;
    }

    /**
     * @return mixed
     */
    public function getNotifyEnabled()
    {
        return $this->notifyEnabled;
    }

    /**
     * @param mixed $notifyEnabled
     */
    public function setNotifyEnabled($notifyEnabled)
    {
        $this->notifyEnabled = $notifyEnabled;
    }

    /**
     * @return \App\Entity\Client
     */
    public function getClient()
    {
        return $this->getVendor()->getClient();
    }
}