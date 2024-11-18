<?php
    function generateSimpleUniqueID() {
        static $usedIDs = [];
        
        do {
            $id = mt_rand(0, 10000);
        } while (in_array($id, $usedIDs));
        
        $usedIDs[] = $id;
        return $id;
    }
