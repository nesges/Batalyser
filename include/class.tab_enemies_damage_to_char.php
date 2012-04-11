<?
    class Tab_Enemies_Damage_to_Char extends Tab {
        
        function Tab_Enemies_Damage_to_Char($name, $char, $start_id, $end_id, $class='', $hidexps=0) {
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
                $html = "<p>".guil('tankstats_note1');
                if($start_id == $parser->start_id && $end_id == $parser->end_id) {
                    $html .= "<br>".guil('tankstats_note2');
                }
                $html .= "</p>
                    <div class='accordion'>";
                $_html = '';
                $duration = $end_timestamp - $start_timestamp;
                foreach($enemies as $enemy_name => $enemy) {
                    $_html .= "<h4><a href='#'>".$enemy_name." (".$enemy['damage']." ".guil('damage');
                    if(! $hidexps) {
                        $_html .= ", ".round($enemy['damage'] / $duration, 2)." DPS";
                    }
                    $_html .= ")</a></h4>
                            <div>
                                
                                <table class='".($class?$class:'dataTable')."'>
                                    <thead>
                                        <tr>
                                            <th>".guil('ability')."</th>
                                            <th>Use</th>
                                            <th>".guil('damage')."</th>
                                            <th>Dmg/Use</th>";
                                            if(! $hidexps) {
                                                $_html .= "<th>DPS</th>";
                                            }
                                            // enemies generate Thread to chars, but I just don't see any sense in it
                                            //<th>Threat</th>
                                            //<th>Threat/Use</th>
                                            //<th>TPS</th>
                                            $_html .= "<th>Hit (alle)</th>
                                            <th>Hit (-crit)</th>
                                            <th>Crit</th>
                                            <th>Miss</th>
                                            <th>Dodge</th>
                                            <th>Parry</th>
                                            <th>Deflect</th>
                                            <th>Resist</th>
                                            <th>Immune</th>
                                            <th>Hit (all)%</th>
                                            <th>Hit (noncrit) %</th>
                                            <th>Crit %</th>
                                            <th>Miss %</th>
                                            <th>Dodge %</th>
                                            <th>Parry %</th>
                                            <th>Deflect %</th>
                                            <th>Resist %</th>
                                            <th>Immune %</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    foreach($enemies_abilities[$enemy_name] as $ability_name => $ability) {
                        $_html .= "<tr>
                                            <td>".$ability_name."</td>
                                            <td>".$ability['count']."</td>
                                            <td>".$ability['damage']."</td>
                                            <td>".round($ability['damage']/$ability['count'], 2)."</td>";
                                            if(! $hidexps) {
                                                $_html .= "<td>".round($ability['damage']/$duration, 2)."</td>";
                                            }
                                            // enemies generate Thread to chars, but I just don't see any sense in it
                                            // <td>".$ability['threat']."</td>
                                            // <td>".round($ability['threat']/$ability['count'], 2)."</td>
                                            // <td>".round($ability['threat']/$duration, 2)."</td>
                                            $_html .= "<td>".($ability['hit']+$ability['crit'])."</td>
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
                    $_html .= "</tbody>
                                    <tfoot>
                                        <tr>
                                            <td>".guil('sum')."</td>
                                            <td>".$enemy['attack_count']."</td>
                                            <td>".$enemy['damage']."</td>
                                            <td>".round($enemy['damage']/$enemy['attack_count'], 2)."</td>";
                                            if(! $hidexps) {
                                                $_html .= "<td>".round($ability['damage']/$duration, 2)."</td>";
                                            }
                                            // enemies generate Thread to chars, but I just don't see any sense in it
                                            // <td>".$enemy['threat']."</td>
                                            // <td>".round($enemy['threat']/$enemy['attack_count'], 2)."</td>
                                            // <td>".round($enemy['threat']/$duration, 2)."</td>
                                            $_html .= "<td>".($enemy['hit']+$enemy['crit'])."</td>
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
                $html .= "<h4><a href='#gesamt_all_vs_".$char."'>".guil('overall')." (".$overall['damage']." ".guil('damage');
                    if(! $hidexps) {
                        $html .= ", ".round($overall['damage'] / $duration, 2)." DPS";
                    }
                    $html .= ")</a></h4>
                                <div>
                                    <table>
                                        <tr>
                                            <td>
                                                <table>
                                                    <tr><td>".guil('hitall').":     </td><td>".($overall['hit']+$overall['crit'])."</td>      <td>".round(100/$overall['count']*($overall['hit']+$overall['crit']),     2)."%</td></tr>
                                                    <tr><td>".guil('hitnoncrit').": </td><td>".$overall['hit']."</td>    <td>".round(100/$overall['count']*$overall['hit'],     2)."%</td></tr>
                                                    <tr><td>".guil('crit').":       </td><td>".$overall['crit']."</td>     <td>".round(100/$overall['count']*$overall['crit'],    2)."%</td></tr>
                                                    <tr><td>".guil('miss').":       </td><td>".$overall['miss']."</td>     <td>".round(100/$overall['count']*$overall['miss'],    2)."%</td></tr>
                                                    <tr><td>".guil('dodge').":      </td><td>".$overall['dodge']."</td>    <td>".round(100/$overall['count']*$overall['dodge'],   2)."%</td></tr>
                                                    <tr><td>".guil('parry').":      </td><td>".$overall['parry']."</td>    <td>".round(100/$overall['count']*$overall['parry'],   2)."%</td></tr>
                                                    <tr><td>".guil('deflect').":    </td><td>".$overall['deflect']."</td>  <td>".round(100/$overall['count']*$overall['deflect'], 2)."%</td></tr>
                                                    <tr><td>".guil('resist').":     </td><td>".$overall['resist']."</td>   <td>".round(100/$overall['count']*$overall['resist'], 2)."%</td></tr>
                                                    <tr><td>".guil('immune').":     </td><td>".$overall['immune']."</td>   <td>".round(100/$overall['count']*$overall['immune'], 2)."%</td></tr>
                                                </table>
                                            </td>
                                            <td>
                                                <iframe width='450' height='300' frameborder='0' scrolling='no' src='piechart_google.php?";
                    $html .=     guil('hitnoncrit')."=".$overall['hit'];
                    $html .= "&".guil('crit')."=".$overall['crit'];
                    $html .= "&".guil('miss')."=".$overall['miss'];
                    $html .= "&".guil('dodge')."=".$overall['dodge'];
                    $html .= "&".guil('parry')."=".$overall['parry'];
                    $html .= "&".guil('deflect')."=".$overall['deflect'];
                    $html .= "&".guil('resist')."=".$overall['Resist'];
                    $html .= "&".guil('immune')."=".$overall['Immune'];
                    $html .= "&pietitle=".guil('counterhitstatistic')."&pieheight=300&piewidth=450'></iframe>
                                            </td>
                                        </tr>
                                    </table>
                                </div>";
                $html .= $_html."</div>";
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