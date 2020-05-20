<?php

namespace App\Manager;

use App\Entity\Building\Building;
use App\Entity\Owner\Owner;
use Doctrine\ORM\EntityManagerInterface;

class ImportManager
{
    private $memberManager;

    private $em;

    public function __construct(MemberManager $memberManager, EntityManagerInterface $em)
    {
        $this->memberManager = $memberManager;
        $this->em = $em;
    }

    /**
     * @param Building $building
     * @param $owners
     * @param $status
     * @return int
     */
    public function importOwners(Building $building, $owners, $status)
    {
        $fileEmails = [];

        // Get all emails from file
        foreach ($owners as $owner) {
            $fileEmails[] = trim(strtolower($owner['Email']));
        }

        // Find same emails in database for handle duplicate import by emails checking
        $databaseEmails = $this->memberManager->findEmailsMatch($building, $fileEmails);

        $counter = 0;

        foreach ($owners as $owner)
        {
            // If all needed fields for owner exists and duplicates of emails not found in database
            if ($this->array_keys_exists(['Email', 'First name', 'Last name'], $owner)
                && strlen($owner['Email']) > 4 && strlen($owner['First name']) > 1 && strlen($owner['Last name']) > 1
                && !in_array(trim(strtolower($owner['Email'])), $databaseEmails))
            {
                try {
                    // If owner with same email doesn't exists
                    $newOwner = new Owner();
                    $newOwner->setBuilding($building);
                    $newOwner->setFirstName($owner['First name']);
                    $newOwner->setLastName($owner['Last name']);
                    $newOwner->setEmail($owner['Email']);
                    $newOwner->setPhone($owner['Phone']);
                    $newOwner->setNotes($owner['Additional information']);

                    $this->saveOwner($newOwner);

                    // Save emails in array to prevent duplicates
                    $databaseEmails[] = trim(strtolower($owner['Email']));

                    $counter++;
                } catch (\Exception $e) {
                    die(var_dump($e->getMessage()));
                }
            }
        }

        // Save everything
        $this->em->flush();
        $this->em->clear();

        return $counter;
    }

    /**
     * array_key_exists for multiple keys
     *
     * @param array $keys
     * @param array $arr
     * @return bool
     */
    function array_keys_exists($keys = [], $arr = [])
    {
        return !array_diff_key(array_flip($keys), $arr);
    }

    /**
     * @param $values array
     * @param $length integer
     * @return bool
     */
    public function isLengthValid($values, $length)
    {
        $valid = true;

        if (!is_array($values)) $values = [$values];

        for ($i = 0; $i < count($values); $i++) {
            if (strlen($values[$i]) < $length) {
                $valid = false;

                break;
            }
        }

        return $valid;
    }

    /**
     * @param Owner $owner
     */
    public function saveOwner(Owner $owner)
    {
        $owner->setToken($owner->getFirstName() . $owner->getLastName() . $owner->getEmail());

        // Add all email notifies to a owner, add pickups and update share statuses
        $this->memberManager->activateNotifications($owner);
        $this->em->persist($owner);
    }
}