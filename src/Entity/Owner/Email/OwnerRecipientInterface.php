<?php

namespace App\Entity\Owner\Email;

use App\Entity\Owner\Owner;

interface OwnerRecipientInterface
{
    /**
     * @return Owner
     */
    public function getOwner() : Owner;

    /**
     * @param Owner $owner
     * @return mixed
     */
    public function setOwner(Owner $owner);
}
