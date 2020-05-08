<?php

namespace App\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestVoter implements VoterInterface
{
    private $request;

    /**
     * RequestVoter constructor.
     * @param RequestStack $request
     */
    public function __construct(RequestStack $request) {
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @param ItemInterface $item
     * @return bool|null
     */
    public function matchItem(ItemInterface $item): ?bool
    {
        if ($item->getUri() === $this->request->getRequestUri()) {
            return true;
        }

        return null;
    }
}
