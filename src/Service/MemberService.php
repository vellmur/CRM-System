<?php

namespace App\Service;

class MemberService
{
    /**
     * @param $shares
     * @param $sharesNum
     * @param $renewalNum
     * @param $statusAmount
     * @param $newInWeekNum
     * @param $newInMonthNum
     * @return mixed
     */
    public function countSharesData($shares, $sharesNum, $statusAmount, $renewalNum, $newInWeekNum, $newInMonthNum)
    {
        $shareStatusNum = [];

        $total['name'] = 'TOTAL';
        $total['total'] = 0;
        $total['amount'] = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];
        $total['renewal'] = 0;
        $total['weekNum'] = 0;
        $total['monthNum'] = 0;

        foreach ($statusAmount as $key => $status) {
            // Change database result to array Share Name => Status => Num of members with this status
            if (!array_key_exists($status['name'], $shareStatusNum)) {
                $shareStatusNum[$status['name']]['num'] = [$status['status'] => $status['amount']];
            } else {
                $shareStatusNum[$status['name']]['num'] += [$status['status'] => $status['amount']];
            }

            $total['amount'][$status['status']] += $status['amount'];
        }

        $sharesNum = $this->createAssociativeArray($sharesNum);
        $renewalNum = $this->createAssociativeArray($renewalNum);
        $newInWeekNum = $this->createAssociativeArray($newInWeekNum);
        $newInMonthNum = $this->createAssociativeArray($newInMonthNum);

        // Create array for summary table share => data (count members)
        foreach ($shares as $key => $share)
        {
            $share = $this->pushIntoShare($share, $sharesNum, $share['name'], 'total');
            $share = $this->pushIntoShare($share, $newInWeekNum, $share['name'], 'weekNum');
            $share = $this->pushIntoShare($share, $renewalNum, $share['name'], 'renewal');
            $share = $this->pushIntoShare($share, $newInMonthNum, $share['name'], 'monthNum');
            $share = $this->pushIntoShare($share, $shareStatusNum, $share['name'], 'amount');
            $shares[$key] = $share;

            $total['total'] += $share['total'];
            $total['weekNum'] += $share['weekNum'];
            $total['renewal'] += $share['renewal'];
            $total['monthNum'] += $share['monthNum'];
        }

        array_push($shares, $total);

        return $shares;
    }


    /**
     * @param $oldArray
     * @return array
     */
    private function createAssociativeArray($oldArray)
    {
        $newArray = [];

        foreach ($oldArray as $value) {
            if (!array_key_exists($value['name'], $newArray)) {
                $newArray[$value['name']] = $value;
            } else {
                $newArray[$value['name']] += $value;
            }

            unset($newArray[$value['name']]['name']);
        }

        return $newArray;
    }


    /**
     * @param $share
     * @param $from
     * @param $shareName
     * @param $field
     * @return mixed
     */
    private function pushIntoShare($share, $from, $shareName, $field)
    {
        if (array_key_exists($shareName, $from)) {
            $share[$field] = $from[$shareName]['num'];
        } else {
            $share[$field] = 0;
        }

        return $share;
    }

}