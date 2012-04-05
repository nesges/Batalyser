<?
    class Tab_Enemies_Damage_to_Char extends Tab {
        
        function Tab_Enemies_Damage_to_Char($name, $char, $start_id, $end_id, $class='') {
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
                            $enemies[$enemy_name]['hit'] += $logdata['hit'];
                            $enemies[$enemy_name]['crit'] += $logdata['crit'];
                            $enemies[$enemy_name]['miss'] += $logdata['miss'];
                            $enemies[$enemy_name]['dodge'] += $logdata['dodge'];
                            $enemies[$enemy_name]['parry'] += $logdata['parry'];
                            $enemies[$enemy_name]['deflect'] += $logdata['deflect'];
                            $enemies[$enemy_name]['immune'] += $logdata['immune'];
                            $enemies[$enemy_name]['resist'] += $logdata['resist'];
                            
                            $overall['count']++;
                            $overall['damage'] += $logdata['hitpoints'];
                            $overall['threat'] += $logdata['threat'];
                            $overall['hit'] += $logdata['hit'];
                            $overall['crit'] += $logdata['crit'];
                            $overall['miss'] += $logdata['miss'];
                            $overall['dodge'] += $logdata['dodge'];
                            $overall['parry'] += $logdata['parry'];
                            $overall['deflect'] += $logdata['deflect'];
                            $overall['immune'] += $logdata['immune'];
                            $overall['resist'] += $logdata['resist'];
            
                            $enemies_abilities[$enemy_name][$ability_name]['count']++;
                            $enemies_abilities[$enemy_name][$ability_name]['damage'] += $logdata['hitpoints'];
                            $enemies_abilities[$enemy_name][$ability_name]['threat'] += $logdata['threat'];
                            $enemies_abilities[$enemy_name][$ability_name]['hit'] += $logdata['hit'];
                            $enemies_abilities[$enemy_name][$ability_name]['crit'] += $logdata['crit'];
                            $enemies_abilities[$enemy_name][$ability_name]['miss'] += $logdata['miss'];
                            $enemies_abilities[$enemy_name][$ability_name]['dodge'] += $logdata['dodge'];
                            $enemies_abilities[$enemy_name][$ability_name]['parry'] += $logdata['parry'];
                            $enemies_abilities[$enemy_name][$ability_name]['deflect'] += $logdata['deflect'];
                            $enemies_abilities[$enemy_name][$ability_name]['immune'] += $logdata['immune'];
                            $enemies_abilities[$enemy_name][$ability_name]['resist'] += $logdata['resist'];
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
                                <table class='".($class?$class:'dataTable')."'>
                                    <thead>
                                        <tr>
                                            <th>Fähigkeit</th>
                                            <th>Use</th>
                                            <th>Damage</th>
                                            <th>Dmg/Use</th>
                                            <th>DPS</th>
                                            <th>Threat</th>
                                            <th>Threat/Use</th>
                                            <th>TPS</th>
                                            <th>Hit (alle)</th>
                                            <th>Hit (-crit)</th>
                                            <th>Crit</th>
                                            <th>Miss</th>
                                            <th>Dodge</th>
                                            <th>Parry</th>
                                            <th>Deflect</th>
                                            <th>Resist</th>
                                            <th>Immun</th>
                                            <th>Hit (alle) %</th>
                                            <th>Hit (noncrit) %</th>
                                            <th>Crit %</th>
                                            <th>Miss %</th>
                                            <th>Dodge %</th>
                                            <th>Parry %</th>
                                            <th>Deflect %</th>
                                            <th>Resist %</th>
                                            <th>Immun %</th>
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
                                            <td>".($ability['hit']+$ability['crit'])."</td>
                                            <td>".$ability['hit']."</td>
                                            <td>".$ability['crit']."</td>
                                            <td>".$ability['miss']."</td>
                                            <td>".$ability['dodge']."</td>
                                            <td>".$ability['parry']."</td>
                                            <td>".$ability['deflect']."</td>
                                            <td>".$ability['resist']."</td>
                                            <td>".$ability['immune']."</td>
                                            <td>".round(($ability['hit']+$ability['crit'])/$ability['count'], 2)."</td>
                                            <td>".round($ability['hit']/$ability['count'], 2)."</td>
                                            <td>".round($ability['crit']/$ability['count'], 2)."</td>
                                            <td>".round($ability['miss']/$ability['count'], 2)."</td>
                                            <td>".round($ability['dodge']/$ability['count'], 2)."</td>
                                            <td>".round($ability['parry']/$ability['count'], 2)."</td>
                                            <td>".round($ability['deflect']/$ability['count'], 2)."</td>
                                            <td>".round($ability['resist']/$ability['count'], 2)."</td>
                                            <td>".round($ability['immune']/$ability['count'], 2)."</td>
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
                                            <td>".($enemy['hit']+$enemy['crit'])."</td>
                                            <td>".$enemy['hit']."</td>
                                            <td>".$enemy['crit']."</td>
                                            <td>".$enemy['miss']."</td>
                                            <td>".$enemy['dodge']."</td>
                                            <td>".$enemy['parry']."</td>
                                            <td>".$enemy['deflect']."</td>
                                            <td>".$enemy['resist']."</td>
                                            <td>".$enemy['immune']."</td>
                                            <td>".round(($enemy['hit']+$enemy['crit'])/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['hit']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['crit']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['miss']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['dodge']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['parry']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['deflect']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['resist']/$enemy['attack_count'], 2)."</td>
                                            <td>".round($enemy['immune']/$enemy['attack_count'], 2)."</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                
                            </div>";
                }
                $html .= "<h4><a href='#gesamt_all_vs_".$char."'>Gesamt (".$overall['damage']." Damage, ".round($overall['damage'] / $duration, 2)." DPS)</a></h4>
                                <div>
                                    <table>
                                        <tr>
                                            <td rowspan='10' colspan='2'>
                                            <img src='?op=piechart"
                                                ."&labels[0]=Hit"
                                                ."&labels[1]=Crit"
                                                ."&labels[2]=Miss"
                                                ."&labels[3]=Dodge"
                                                ."&labels[4]=Parry"
                                                ."&labels[5]=Deflect"
                                                ."&labels[6]=Resist"
                                                ."&labels[7]=Immune"
                                                ."&values[0]=".$overall['hit']
                                                ."&values[1]=".$overall['crit']
                                                ."&values[2]=".$overall['miss']
                                                ."&values[3]=".$overall['dodge']
                                                ."&values[4]=".$overall['parry']
                                                ."&values[5]=".$overall['deflect']
                                                ."&values[6]=".$overall['resist']
                                                ."&values[7]=".$overall['immune']
                                            ."' alt='Hit/Crit/Miss/Dodge..'>
                                            </td>
                                        </tr>
                                        <tr><td>Treffer (hit+crit): </td><td>".($overall['hit']+$overall['crit'])."</td>      <td>".round(100/$overall['count']*($overall['hit']+$overall['crit']),     2)."%</td></tr>
                                        <tr><td>Treffer (noncrit):</td><td>".$overall['hit']."</td>    <td>".round(100/$overall['count']*$overall['hit'],     2)."%</td></tr>
                                        <tr><td>Kritisch:       </td><td>".$overall['crit']."</td>     <td>".round(100/$overall['count']*$overall['crit'],    2)."%</td></tr>
                                        <tr><td>Verfehlt:       </td><td>".$overall['miss']."</td>     <td>".round(100/$overall['count']*$overall['miss'],    2)."%</td></tr>
                                        <tr><td>Ausgewichen:    </td><td>".$overall['dodge']."</td>    <td>".round(100/$overall['count']*$overall['dodge'],   2)."%</td></tr>
                                        <tr><td>Parriert:       </td><td>".$overall['parry']."</td>    <td>".round(100/$overall['count']*$overall['parry'],   2)."%</td></tr>
                                        <tr><td>Schild:         </td><td>".$overall['deflect']."</td>  <td>".round(100/$overall['count']*$overall['deflect'], 2)."%</td></tr>
                                        <tr><td>Resist:         </td><td>".$overall['resist']."</td>   <td>".round(100/$overall['count']*$overall['resist'], 2)."%</td></tr>
                                        <tr><td>Immun:          </td><td>".$overall['immune']."</td>   <td>".round(100/$overall['count']*$overall['immune'], 2)."%</td></tr>
                                    </table>
                                </div>
                            </div>";
            }
            
            parent::Tab(
                $name, 
                'Tankstats / * vs '.$char, 
                '', 
                '',
                $html,
                $class
            );
        }
    }
?>