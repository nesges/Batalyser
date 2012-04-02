<?
    class Tab_Enemies_Damage_to_Char extends Tab {
        
        function Tab_Enemies_Damage_to_Char($name, $char, $start_id, $end_id) {
            global $logdata;
            
            $enemies = array();
            $enemies_abilities = array();
            $overall = array();
            for($id=$start_id; $id<=$end_id; $id++) {
                if($logdata[$id]['target_name'] == $char) {
                    $enemy_name = $logdata[$id]['source_name'];
                    $ability_name = $logdata[$id]['ability_name'];
                    switch($logdata[$id]['effect_id']) {
                        case DAMAGE:
                            $enemies[$enemy_name]['attack_count']++;
                            $enemies[$enemy_name]['damage'] += $logdata[$id]['hitpoints'];
                            $enemies[$enemy_name]['threat'] += $logdata[$id]['threat'];
                            $overall['damage'] += $logdata[$id]['hitpoints'];
                            $overall['threat'] += $logdata[$id]['threat'];
            
                            $enemies_abilities[$enemy_name][$ability_name]['count']++;
                            $enemies_abilities[$enemy_name][$ability_name]['damage'] += $logdata[$id]['hitpoints'];
                            $enemies_abilities[$enemy_name][$ability_name]['threat'] += $logdata[$id]['threat'];
                            break;
                    }
                }
            }
            
            if(count($enemies) > 0) {
                $html = "<div class='accordion'>";
                $duration = $logdata[$end_id]['timestamp'] - $logdata[$start_id]['timestamp'];
                foreach($enemies as $enemy_name => $enemy) {
                    $html .= "<h4><a href='#'>".$enemy_name." (".$enemy['damage']." Damage, ".round($enemy['damage'] / $duration, 2)." DPS)</a></h4>
                            <div>
                                <table class='dataTable'>
                                    <thead>
                                        <tr>
                                            <th>Fähigkeit</th>
                                            <th>Anwendungen</th>
                                            <th>Damage</th>
                                            <th>Damage/Use</th>
                                            <th>DPS</th>
                                            <th>Threat</th>
                                            <th>Threat/Use</th>
                                            <th>TPS</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    foreach($enemies_abilities[$enemy_name] as $ability_name => $ability) {
                        $html .= "<tr>
                                            <td>".$ability_name."</td>
                                            <td>".$ability['count']."</td>
                                            <td>".$ability['damage']."</td>
                                            <td>".round($ability['damage']/$ability['count'], 2)."</td>
                                            <td>".round($ability['damage']/$duration, 2)."</td>
                                            <td>".$ability['threat']."</td>
                                            <td>".round($ability['threat']/$ability['count'], 2)."</td>
                                            <td>".round($ability['threat']/$duration, 2)."</td>
                                        </tr>";
                    }
                    $html .= "</tbody>
                                    <tfoot>
                                        <tr>
                                            <td>Summe</td>
                                            <td>".$enemy['attack_count']."</td>
                                            <td>".$enemy['damage']."</td>
                                            <td>".round($enemy['damage']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['damage']/$duration, 2)."</td>
                                            <td>".$enemy['threat']."</td>
                                            <td>".round($enemy['threat']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['threat']/$duration, 2)."</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>";
                }
                $html .= "</div>";
            }
            
            parent::Tab(
                $name, 
                '* vs '.$char, 
                '', 
                '',
                $html
            );
        }
    }
?>