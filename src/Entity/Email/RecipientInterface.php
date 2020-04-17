<?php

namespace App\Entity\Email;

interface RecipientInterface
{
    /**
     * @return null|int
     */
    public function getId();

    /**
     * @param int $id
     * @return mixed
     */
    public function setId(int $id);

    /**
     * @return null|string
     */
    public function getEmailAddress();

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress(string $emailAddress);

    /**
     * @return mixed
     */
    public function isDelivered() : bool;

    /**
     * @param bool $isDelivered
     */
    public function setIsDelivered(bool $isDelivered) : void;

    /**
     * @return bool
     */
    public function isOpened() : bool;

    /**
     * @param bool $isOpened
     */
    public function setIsOpened(bool $isOpened) : void;

    /**
     * @return bool
     */
    public function isClicked() : bool;

    /**
     * @param bool $isClicked
     */
    public function setIsClicked(bool $isClicked) : void;

    /**
     * @return bool
     */
    public function isBounced(): bool;

    /**
     * @param bool $isBounced
     */
    public function setIsBounced(bool $isBounced) : void;

    /**
     * @param EmailLogInterface $emailLog
     */
    public function setEmailLog(EmailLogInterface $emailLog);

    /**
     * @return EmailLogInterface
     */
    public function getEmailLog() : EmailLogInterface;
}

