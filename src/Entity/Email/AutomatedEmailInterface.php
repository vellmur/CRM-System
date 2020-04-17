<?php

namespace App\Entity\Email;

interface AutomatedEmailInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     */
    public function setId(int $id);

    /**
     * @return string
     */
    public function getSubject();

    /**
     * @param string $subject
     */
    public function setSubject(string $subject);

    /**
     * @return mixed
     */
    public function getText();

    /**
     * @param mixed $text
     */
    public function setText($text);

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @param mixed $type
     */
    public function setType($type);
}

