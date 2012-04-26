<?
    class Tab_Char_DpsTps_per_Ability extends Tab {
        
        function Tab_Char_DpsTps_per_Ability($name, $char, $start_id, $end_id, $class='', $hidexps=0) {
            global $parser;

            $data = '';
            $html = '';
            
            $used_abilities = array();
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
                if(preg_match('/^'.$char.'(:(.+))?/', $logdata['source_name'], $matches)) {
                    $ability_name = $logdata['ability_name'];
                    if($logdata['source_type'] == 'companion') {
                        $ability_name = $matches[2].': '.$ability_name;
                    }
                    switch($logdata['effect_id']) {
                        case DAMAGE:
                            $used_abilities[$ability_name]['damage'] += $logdata['hitpoints'];
                            $used_abilities[$ability_name]['threat'] += $logdata['threat'];
                            $used_abilities[$ability_name]['hit'] += $logdata['hit'];
                            $used_abilities[$ability_name]['crit'] += $logdata['crit'];
                            $used_abilities[$ability_name]['miss'] += $logdata['miss'];
                            $used_abilities[$ability_name]['dodge'] += $logdata['dodge'];
                            $used_abilities[$ability_name]['parry'] += $logdata['parry'];
                            $used_abilities[$ability_name]['deflect'] += $logdata['deflect'];
                            $used_abilities[$ability_name]['immune'] += $logdata['immune'];
                            $used_abilities[$ability_name]['resist'] += $logdata['resist'];
                            $used_abilities[$ability_name]['count']++;
            
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
                            $overall['count']++;
                    }
                }
            }
            
            $duration = $end_timestamp - $start_timestamp;
            if($overall['count']>0 && $duration) {
                foreach($used_abilities as $ability_name => $ability) {
                    if($ability['damage']>0) {
                        $data .= "<tr>
                                <td>".$ability_name."</td>
                                <td>".$ability['count']."</td>
                                <td>".$ability['damage']."</td>";
                        if(!$hidexps) {
                            $data .= "<td>".round($ability['damage'] / $duration, 2)."</td>";
                        }
                        $data .= "<td>".round($ability['damage'] / $ability['count'], 2)."</td>
                                <td>".$ability['threat']."</td>";
                        if(!$hidexps) {
                            $data .= "<td>".round($ability['threat'] / $duration, 2)."</td>";
                        }
                        $data .= "<td>".round($ability['threat'] / $ability['count'], 2)."</td>
                                <td>".round($ability['threat'] / $ability['damage'], 2)."</td>
                                <td>".($ability['hit']+$ability['crit'])."</td>
                                <td>".$ability['hit']."</td>
                                <td>".$ability['crit']."</td>
                                <td>".$ability['miss']."</td>
                                <td>".$ability['dodge']."</td>
                                <td>".$ability['parry']."</td>
                                <td>".$ability['deflect']."</td>
                                <td>".$ability['resist']."</td>
                                <td>".$ability['immune']."</td>
                                <td>".round(100/$ability['count']*($ability['hit']+$ability['crit']), 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['hit'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['crit'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['miss'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['dodge'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['parry'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['deflect'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['resist'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['immune'], 2)."%</td>
                            </tr>";
                    }
                }
            
                $html = "<div style='border-top: 1px solid silver'>Gesamt:<table>
                    <tr>
                        <td>
                            <table>
                                <tr><td>".guil('hitall').":     </td><td>".($overall['hit']+$overall['crit'])."</td>      <td>".round(100/$overall['count']*($overall['hit']+$overall['crit']),     2)."%</td></tr>
                                <tr><td>".guil('hitnoncrit').": </td><td>".$overall['hit']."</td>    <td>".round(100/$overall['count']*$overall['hit'],     2)."%</td></tr>
                                <tr><td>".guil('crit').":       </td><td>".$overall['crit']."</td>     <td>".round(100/$overall['count']*$overall['crit'],    2)."%</td></tr>
                                <tr><td>".guil('miss')."        </td><td>".$overall['miss']."</td>     <td>".round(100/$overall['count']*$overall['miss'],    2)."%</td></tr>
                                <tr><td>".guil('dodge').":      </td><td>".$overall['dodge']."</td>    <td>".round(100/$overall['count']*$overall['dodge'],   2)."%</td></tr>
                                <tr><td>".guil('parry').":      </td><td>".$overall['parry']."</td>    <td>".round(100/$overall['count']*$overall['parry'],   2)."%</td></tr>
                                <tr><td>".guil('deflect').":    </td><td>".$overall['deflect']."</td>  <td>".round(100/$overall['count']*$overall['deflect'], 2)."%</td></tr>
                                <tr><td>".guil('resist').":     </td><td>".$overall['resist']."</td>   <td>".round(100/$overall['count']*$overall['resist'], 2)."%</td></tr>
                                <tr><td>".guil('immune').":     </td><td>".$overall['immune']."</td>   <td>".round(100/$overall['count']*$overall['immune'], 2)."%</td></tr>
                            </table>
                        </td>
                        <td>";
                $params  =     guil('hitnoncrit')."=".$overall['hit'];
                $params .= "&".guil('crit')."=".$overall['crit'];
                $params .= "&".guil('miss')."=".$overall['miss'];
                $params .= "&".guil('dodge')."=".$overall['dodge'];
                $params .= "&".guil('parry')."=".$overall['parry'];
                $params .= "&".guil('deflect')."=".$overall['deflect'];
                $params .= "&".guil('resist')."=".$overall['resist'];
                $params .= "&".guil('immune')."=".$overall['immune'];
                $html .= "<iframe width='450' height='300' frameborder='0' scrolling='no' src='piechart_google.php?".$params."&pietitle=".guil('hitstatistic')."&pieheight=300&piewidth=450'></iframe><br>
                            <center><button class='button_open_dialog_misc' href='piechart_google.php?".$params."&pietitle=".guil('hitstatistic')."&pieheight=600&piewidth=800'>".guil('enlarge_grafic')."</button></center>
                        </td>
                        <td>
                            ";
                foreach($used_abilities as $ability_name => $ability) {
                    $piedata[] = $ability_name."=".$ability['damage'];
                }
                $params = join('&', $piedata);
                $html .= "<iframe width='450' height='300' frameborder='0' scrolling='no' src='piechart_google.php?".$params."&pietitle=".guil('damageperability')."&pieheight=300&piewidth=450'></iframe>
                            <center><button class='button_open_dialog_misc' href='piechart_google.php?".$params."&pietitle=".guil('damageperability')."&pieheight=600&piewidth=800'>".guil('enlarge_grafic')."</button></center>
                        </td>
                    </tr>
                </table>
                </div>";
            }
            
            if(!$hidexps) {
                $headers = array(guil('ability'), 'Use', 'Damage', 'DPS', 'Damage/Use', 'Threat', 'TPS', 'Threat/Use', 'Threat/DMG',
                        'Hit (alle)', 'Hit (noncrit)', 'Crit', 'Miss', 'Dodge', 'Parry', 'Deflect', 'Resist', 'Immune',
                        'Hit (alle) %', 'Hit (noncrit) %', 'Crit %', 'Miss %', 'Dodge %', 'Parry %', 'Deflect %', 'Resist %', 'Immune %');
            } else {
                $headers = array(guil('ability'), 'Use', 'Damage', 'Damage/Use', 'Threat', 'Threat/Use', 'Threat/DMG',
                        'Hit (alle)', 'Hit (noncrit)', 'Crit', 'Miss', 'Dodge', 'Parry', 'Deflect', 'Resist', 'Immune',
                        'Hit (alle) %', 'Hit (noncrit) %', 'Crit %', 'Miss %', 'Dodge %', 'Parry %', 'Deflect %', 'Resist %', 'Immune %');
            }
            
            parent::Tab(
                $name, 
                guil('damageperability'), 
                $headers, 
                $data,
                $html,
                $class
            );
        }
    }
?>
