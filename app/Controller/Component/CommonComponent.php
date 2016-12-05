<?php

App::uses('Component', 'Controller');

class CommonComponent extends Component {

    public function getWorkingDays($array) {
        $newArray = array();
        foreach ($array as $a) {
            $newArray[] = $a['day'];
        }
        $days = array("Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun");
        $noDays = array();
        foreach ($days as $k => $n) {
            if (!in_array($n, $newArray)) {
                $noDays[] = $n;
            }
        }
        if ($noDays[(count($noDays) - 1)] == 'Sun') {
            $next = 0;
        } else {
            $next = (array_search($noDays[(count($noDays) - 1)], $days)) + 1;
        }

        if ($noDays[0] == 'Mon') {
            $prev = 6;
        } else {
            $prev = array_search($noDays[0],$days)-1;
        }
        return $days[$next]." - ".$days[$prev];
    }

}
