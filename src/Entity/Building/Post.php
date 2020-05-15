<?php

namespace App\Entity\Building;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Blog\BasePost;

/**
 * @ORM\Table(name="building__posts")
 * @ORM\Entity()
 */
class Post extends BasePost
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Building\Building", inversedBy="posts")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $building;

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }
}
