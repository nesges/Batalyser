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
                            // experimental! 
                            // see crudedragos comments on http://mmo-mechanics.com/swtor/forums/Thread-Batalyser-SWTOR-Combat-Analyser
                            // $char_class is nonexistent yet, factor should be between 1-0.42 and 1-0.45 depending on charclass
                            switch($char_class) {
                                default:
                                    $classfactor = 0.58;
                            }
                            $used_abilities[$ability_name]['overheal'] = $logdata['hitpoints'] - ($logdata['hitpoints'] - $logdata['threat'] * $classfactor);
                            
                            $overall['heal'] += $logdata['hitpoints'];
                            $overall['threat'] += $logdata['threat'];
                            $overall['hit'] += $logdata['hit'];
                            $overall['crit'] += $logdata['crit'];
                            $overall['count']++;
                            $overall['overheal'] += $used_abilities[$ability_name]['overheal'];
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
                            <td>".round($ability['overheal'], 2)."</td>
                            <td>".round(100/$ability['heal']*$ability['overheal'], 2)."%</td>
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
                        <td rowspan='2' valign='top'>
                            <table>
                                <tr><td>".guil('normal').":         </td><td>".$overall['hit']."</td>       <td>(".round(100/$overall['count']*$overall['hit'],     2)."%)</td></tr>
                                <tr><td>".guil('crit').":           </td><td>".$overall['crit']."</td>      <td>(".round(100/$overall['count']*$overall['crit'],    2)."%)</td></tr>
                                <tr><td>HPS:                        </td><td colspan='2'>".round($overall['heal']/$duration, 2)."</td></tr>
                                <tr><td>Overheal:                   </td><td colspan='2'>".round($overall['overheal'], 2)." (".round(100/$overall['heal'] * $overall['overheal'], 2)."%)</td></tr>
                                <tr><td>HPS (".guil('effective')."):</td><td colspan='2'>".round(($overall['heal']-$overall['overheal'])/$duration, 2)."</td></tr>
                            </table>
                            (Overheal and effective HPS are experimental estimates)
                        </td>
                        <td valign='top'>";
                $params =  guil('normal')."=".$overall['hit'];
                $params .= "&".guil('crit')."=".$overall['crit'];
                $html .= "<iframe width='450' height='250' frameborder='0' scrolling='no' src='piechart_google.php?".$params."&pietitle=".guil('healhitstatistic')."&pieheight=300&piewidth=450'></iframe>
                            <center><button class='button_open_dialog_misc' href='piechart_google.php?".$params."&pietitle=".guil('healhitstatistic')."&pieheight=600&piewidth=800'>".guil('enlarge_grafic')."</button></center>
                        </td>
                        <td>";
                foreach($used_abilities as $ability_name => $ability) {
                    $piedata[] = $ability_name."=".$ability['heal'];
                }
                $params = join('&', $piedata);
                $html .= "<iframe width='450' height='250' frameborder='0' scrolling='no' src='piechart_google.php?".$params."&pietitle=".guil('healperability')."&pieheight=300&piewidth=450'></iframe>
                            <center><button class='button_open_dialog_misc' href='piechart_google.php?".$params."&pietitle=".guil('healperability')."&pieheight=600&piewidth=800'>".guil('enlarge_grafic')."</button></center>
                        </td>
                    </tr>
                    <tr>
                        <td>";
                $params = "Effective=".($overall['heal']-$overall['overheal'])."&Overheal=".$overall['overheal'];
                $html .= "<iframe width='450' height='250' frameborder='0' scrolling='no' src='piechart_google.php?".$params."&pietitle=".guil('overhealstatistic')."&pieheight=300&piewidth=450'></iframe>
                            <center><button class='button_open_dialog_misc' href='piechart_google.php?".$params."&pietitle=".guil('overhealstatistic')."&pieheight=600&piewidth=800'>".guil('enlarge_grafic')."</button></center>
                        </td>
                    </tr>
                </table>
                </div>";
            }

            parent::Tab(
                $name, 
                guil('healperability'), 
                array(guil('ability'), 'Use', 'Heal', 'HPS', 'Heal/Use', 'Overheal', 'Overheal %', 'Threat', 'TPS', 'Threat/Use', 'Threat/Heal',
                        'Hit', 'Crit', 'Hit %', 'Crit %'), 
                $data,
                $html,
                $class
            );
            
            $this->tooltips['Overheal'] = 'experimental estimate!';
            $this->tooltips['Overheal %'] = 'experimental estimate!';
        }
    }
?>