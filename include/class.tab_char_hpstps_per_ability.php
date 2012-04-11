<?
    class Tab_Char_HpsTps_per_Ability extends Tab {
        
        function Tab_Char_HpsTps_per_Ability($name, $char, $start_id, $end_id, $class='') {
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
                        case HEAL:
                            $used_abilities[$ability_name]['heal'] += $logdata['hitpoints'];
                            $used_abilities[$ability_name]['threat'] += $logdata['threat'];
                            $used_abilities[$ability_name]['hit'] += $logdata['hit'];
                            $used_abilities[$ability_name]['crit'] += $logdata['crit'];
                            $used_abilities[$ability_name]['count']++;
            
                            $overall['heal'] += $logdata['hitpoints'];
                            $overall['threat'] += $logdata['threat'];
                            $overall['hit'] += $logdata['hit'];
                            $overall['crit'] += $logdata['crit'];
                            $overall['count']++;
                    }
                }
            }
            
            $duration = $end_timestamp - $start_timestamp;
            if($overall['count']>0) {
                foreach($used_abilities as $ability_name => $ability) {
                    $data .= "<tr>
                            <td>".$ability_name."</td>
                            <td>".$ability['count']."</td>
                            <td>".$ability['heal']."</td>
                            <td>".round($ability['heal'] / $duration, 2)."</td>
                            <td>".round($ability['heal'] / $ability['count'], 2)."</td>
                            <td>".$ability['threat']."</td>
                            <td>".round($ability['threat'] / $duration, 2)."</td>
                            <td>".round($ability['threat'] / $ability['count'], 2)."</td>
                            <td>".round($ability['threat'] / $ability['heal'], 2)."</td>
                            <td>".$ability['hit']."</td>
                            <td>".$ability['crit']."</td>
                            <td>".round(100/$ability['count']*$ability['hit'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['crit'], 2)."%</td>
                        </tr>";
                }
            
                $html = "<div style='border-top: 1px solid silver'>Gesamt:<table>
                    <tr>
                        <td>
                            <table>
                                <tr><td>".guil('normal').": </td><td>".$overall['hit']."</td>       <td>(".round(100/$overall['count']*$overall['hit'],     2)."%)</td></tr>
                                <tr><td>".guil('crit').":   </td><td>".$overall['crit']."</td>      <td>(".round(100/$overall['count']*$overall['crit'],    2)."%)</td></tr>
                                <tr><td>HPS:                </td><td colspan='2'>".round($overall['heal']/$duration, 2)."</td></tr>
                            </table>
                        </td>
                        <td valign='top'>
                            <iframe width='450' height='300' frameborder='0' scrolling='no' src='piechart_google.php?";
                $html .=  guil('normal')."=".$overall['hit'];
                $html .= "&".guil('crit')."=".$overall['crit'];
                $html .= "&pietitle=".guil('healhitstatistic')."&pieheight=300&piewidth=450'></iframe>
                        </td>
                        <td>
                            <iframe width='450' height='300' frameborder='0' scrolling='no' src='piechart_google.php?";
                foreach($used_abilities as $ability_name => $ability) {
                    $piedata[] = $ability_name."=".$ability['heal'];
                }
                $html .= join('&', $piedata);
                $html .= "&pietitle=".guil('healperability')."&pieheight=300&piewidth=450'></iframe>
                        </td>
                    </tr>
                </table>
                </div>";
            }

            parent::Tab(
                $name, 
                guil('healperability'), 
                array(guil('ability'), 'Use', 'Heal', 'HPS', 'Heal/Use', 'Threat', 'TPS', 'Threat/Use', 'Threat/Heal',
                        'Hit', 'Crit', 'Hit %', 'Crit %'), 
                $data,
                $html,
                $class
            );
        }
    }
?>