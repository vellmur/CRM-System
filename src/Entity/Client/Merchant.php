<?php

namespace App\Entity\Client;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payment
 *
 * @ORM\Table(name="client__merchant", uniqueConstraints={@ORM\UniqueConstraint(name="merchant_unique", columns={"client_id", "merchant"})}))
 * @ORM\Entity()
 * @UniqueEntity(fields={"client", "merchant"}, errorPath="merchant", message="validation.form.merchant_unique")
 */
class Merchant
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Client\Client", inversedBy="merchants")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false)
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant", type="string", length=1, nullable=false)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $merchant;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_key", type="string", nullable=false)
     * @Assert\NotBlank(message="validation.form.required")
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_pin", type="string", length=4, nullable=true)
     * @Assert\Length(min=4, max=4, exactMessage="validation.form.exactly_length")
     */
    private $pin;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     */
    private $currency;

    /**
     * @var boolean
     * @ORM\Column(name="is_sandbox", type="boolean")
     */
    private $isSandbox = false;

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
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getMerchant()
    {
        return $this->merchant;
    }

    /**
     * @param $merchant string
     */
    public function setMerchant($merchant)
    {
        $this->merchant = $merchant;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @param string $pin
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
    }

    /**
     * @return bool
     */
    public function isSandbox()
    {
        return $this->isSandbox;
    }

    /**
     * @param bool $isSandbox
     */
    public function setIsSandbox(bool $isSandbox)
    {
        $this->isSandbox = $isSandbox;
    }
}