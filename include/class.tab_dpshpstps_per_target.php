<?
    class Tab_DpsHpsTps_per_Target extends Tab {
        function Tab_DpsHpsTps_per_Target($name, $char, $start_id, $end_id) {
            global $logdata;
            
            $targets = array();
            for($id=$start_id; $id<=$end_id; $id++) {
                if(preg_match('/^'.$char.'(:.+)?/', $logdata[$id]['source_name'])) {
                    $target_name = $logdata[$id]['target_name'];
                    switch($logdata[$id]['effect_id']) {
                        case HEAL:
                            $targets[$target_name]['healed'] += $logdata[$id]['hitpoints'];
                            break;
                        case DAMAGE:
                            $targets[$target_name]['damage'] += $logdata[$id]['hitpoints'];
                    }
                    $targets[$target_name]['threat'] += $logdata[$id]['threat'];
                    $targets[$target_name]['target_type'] = $logdata[$id]['target_type'];
                }
            }
            
            $data = '';
            $duration = $logdata[$end_id]['timestamp'] - $logdata[$start_id]['timestamp'];
            foreach($targets as $target_name => $target) {
                if($target['target_type'] == 'companion') {
                    $target_name = preg_replace('/'.$char.':/', '', $target_name).' (Companion)';
                }
                if($target['damage']>0 || $target['healed']>0 || $target['threat']>0) {
                    $data .= "<tr>
                            <td>".$target_name."</td>
                            <td>".$target['damage']."</td>
                            <td>".$target['healed']."</td>
                            <td>".$target['threat']."</td>
                            <td>".round($target['damage'] / $duration, 2)."</td>
                            <td>".round($target['healed'] / $duration, 2)."</td>
                            <td>".round($target['threat'] / $duration, 2)."</td>
                        </tr>";
                }
            }

            parent::Tab(
                $name, 
                'DPS/HPS/TPS pro Ziel', 
                array('Ziel', 'Damage', 'Heal', 'Threat', 'DPS', 'HPS', 'TPS'), 
                $data
            );
        }
    }
?>