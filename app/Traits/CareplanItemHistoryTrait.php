<?php
/**
 * Created by PhpStorm.
 * User: gopal
 * Date: 19/3/19
 * Time: 3:00 PM
 */

namespace App\Traits;

use App\Models\CareplanItemHistory;
use App\Models\User;

trait CareplanItemHistoryTrait
{
    public function addToHistory($historyItem,$type,$event)
    {

        if($event == 'created') {
            $historyItem = $historyItem->toArray(); 
            if($historyItem['start_date'] && $historyItem['end_date'])
                $historyItem['message'] = 'added';
            else
                $historyItem['message'] = 'started';
            $this->createHistoryLog($historyItem, $type);
        }

        if($event == 'updated') {
            $historyFlat = false;

            foreach ($historyItem->getDirty() as $attribute => $value) {
                if($this->isHistoryItem($attribute)) {
                    $historyFlat = true;
                }
            }

            if($historyFlat) {
                $historyItem = $historyItem->toArray();
                $historyItem['message'] = 'updated';
                $this->createHistoryLog($historyItem, $type);
            }
        }

        if($event == 'status') {
            $historyItem = $historyItem->toArray();

            if($historyItem['status']) {
                $historyItem['message'] = 'restarted';
            } else {
                $historyItem['message'] = 'discontinued';
            }

            $this->createHistoryLog($historyItem, $type);
        }

    }


    public function isHistoryItem($attribute)
    {
        return !in_array($attribute, $this->ignoreHistoryItems);
    }

    /**
     * @param $historyItem
     * @param $type
     */
    public function createHistoryLog($historyItem, $type): void
    {
        $historyItem['type_name'] = User::find($historyItem['type_id'])->name;

        \App\Models\CareplanItemHistory::create([
            'type' => $type,
            'type_id' => $historyItem['id'],
            'patient_id' => $historyItem['patient_id'],
            'form_data' => $historyItem,
        ]);
    }
}