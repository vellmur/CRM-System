<?php

namespace App\Entity\Customer\Email;

use App\Entity\Building\Building;

interface BuildingEmailInterface
{
    /**
     * @return Building
     */
    public function getBuilding() : Building;

    /**
     * @param Building $building
     * @return mixed
     */
    public function setBuilding(Building $building);

    /**
     * @return string
     */
    public function getReplyEmail();

    /**
     * @param string $replyEmail
     * @return mixed
     */
    public function setReplyEmail(string $replyEmail);

    /**
     * @return string
     */
    public function getReplyName();

    /**
     * @param string $replyName
     * @return mixed
     */
    public function setReplyName(string $replyName);
}
