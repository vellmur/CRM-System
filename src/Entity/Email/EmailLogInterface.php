<?php

namespace App\Entity\Email;

interface EmailLogInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     */
    public function setId(int $id): void;

    /**
     * @return string|null
     */
    public function getSubject();

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void;

    /**
     * @return mixed
     */
    public function getText();

    /**
     * @param mixed $text
     */
    public function setText($text): void;

    /**
     * @return bool
     */
    public function isDraft(): bool;

    /**
     * @param bool $isDraft
     */
    public function setIsDraft(bool $isDraft): void;

    /**
     * @return bool
     */
    public function isInProcess(): bool;

    /**
     * @param bool $inProcess
     */
    public function setInProcess(bool $inProcess): void;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void;

    /**
     * @return AutomatedEmailInterface
     */
    public function getAutomatedEmail();

    /**
     * @param AutomatedEmailInterface $automatedEmail
     */
    public function setAutomatedEmail(AutomatedEmailInterface $automatedEmail);

    /**
     * @return array
     */
    public function getRecipients();

    /**
     * @param array
     * @return mixed
     */
    public function setRecipients($recipients);

    /**
     * @param RecipientInterface $recipient
     * @return mixed
     */
    public function addRecipient(RecipientInterface $recipient);

    /**
     * @param RecipientInterface $recipient
     * @return mixed
     */
    public function removeRecipient(RecipientInterface $recipient);
}
