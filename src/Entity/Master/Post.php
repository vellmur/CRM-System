<?php

namespace App\Entity\Master;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Blog\BasePost;

/**
 * @ORM\Table(name="master__posts")
 * @ORM\Entity()
 */
class Post extends BasePost {

}
