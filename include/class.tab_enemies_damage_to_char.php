<?
    class Tab_Enemies_Damage_to_Char extends Tab {
        
        function Tab_Enemies_Damage_to_Char($name, $char, $start_id, $end_id) {
            global $parser;
            
            $enemies = array();
            $enemies_abilities = array();
            $overall = array();
            $last_fetch = $start_id;
            for($id=$start_id; $id <= $end_id; $id++) {
                if($id >= $last_fetch + PARSER_MAX_FETCH) {
                    $last_fetch +=PARSER_MAX_FETCH;
                    unset($parser->loglines);
                    unset($parser->logdata);
                    $parser->read_loglines($id, $id+PARSER_MAX_FETCH);
                    $parser->gather_logdata($id, $id+PARSER_MAX_FETCH);
                }
                $logdata = $parser->logdata[$id];
                
                if($id == $start_id) {
                    $start_timestamp = $logdata['timestamp'];
                } elseif ($id == $end_id) {
                    $end_timestamp = $logdata['timestamp'];
                }

                if($logdata['target_name'] == $char) {
                    $enemy_name = $logdata['source_name'];
                    $ability_name = $logdata['ability_name'];
                    switch($logdata['effect_id']) {
                        case DAMAGE:
                            $enemies[$enemy_name]['attack_count']++;
                            $enemies[$enemy_name]['damage'] += $logdata['hitpoints'];
                            $enemies[$enemy_name]['threat'] += $logdata['threat'];
                            $overall['damage'] += $logdata['hitpoints'];
                            $overall['threat'] += $logdata['threat'];
            
                            $enemies_abilities[$enemy_name][$ability_name]['count']++;
                            $enemies_abilities[$enemy_name][$ability_name]['damage'] += $logdata['hitpoints'];
                            $enemies_abilities[$enemy_name][$ability_name]['threat'] += $logdata['threat'];
                            break;
                    }
                }
            }
            
            if(count($enemies) > 0) {
                $html = "<div class='accordion'>";
                $duration = $end_timestamp - $start_timestamp;
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