<?
    class Tab_Char_HpsTps_per_Ability extends Tab {
        
        function Tab_Char_HpsTps_per_Ability($name, $char, $start_id, $end_id, $class='', $hidexps=0) {
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
                            $used_abilities[$ability_name]['id'] = $logdata['ability_id'];
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
                $overall['overheal'] = 0;

                foreach($used_abilities as $ability_name => $ability) {
                    // experimental! 
                    // see crudedragos comments on http://mmo-mechanics.com/swtor/forums/Thread-Batalyser-SWTOR-Combat-Analyser
                    switch($ability['id']) {
                        // Medpacs
                        case "813312187039744" :
                        case "2176191209406464":
                        case "864164599824384" :
                        case "843162209746944" :
                        case "2176199799341056":
                        case "813316482007040" :
                        case "813320776974336" :
                        case "2628395431100416":
                        case "807518276157440" :
                        case "1143320294195200":
                        case "1481411529801728":
                        case "2176212684242944":
                        case "1481402939867136":
                        case "1481407234834432":
                        case "807544045961216" :
                        case "2176216979210240":
                        case "1481398644899840":
                        case "2471474505973760":
                            if($ability['threat']/$ability['heal']>0.5) {
                                // tank
                                $threatcoefficient = 0.75;
                            } else {
                                // healer, dd
                                $threatcoefficient = 0.50;
                            }
                            break;
                        // Healing Resonance
                        case "2785827457335296":
                        case "2882455631560704":
                        case "2772083561988096":
                        case "2785805982498816":
                            $threatcoefficient = 0.50;
                            break;
                        default:
                            switch($parser->logchar2userchar[$char]['class_id']) {
                                case MERCENARY:
                                case COMMANDO:
                                    // healer
                                    $threatcoefficient = 0.45;
                                    break;
                                case SCOUNDREL:
                                case OPERATIVE:
                                    // healer
                                    $threatcoefficient = 0.45;
                                    break;
                                case SAGE:
                                case SORCERER:
                                    // healer
                                    $threatcoefficient = 0.42;
                                    break;
                                case GUARDIAN:
                                case JUGGERNAUT:
                                case SHADOW:
                                case ASSASSIN:
                                case VANGUARD:
                                case POWERTECH:
                                    if($ability['threat']/$ability['heal']>0.5) {
                                        // tank
                                        $threatcoefficient = 0.75;
                                    } else {
                                        // dd
                                        $threatcoefficient = 0.5;
                                    }
                                    break;
                                default:
                                    // class is not set, try to determine it by calculating the actual threatcoeffecientrange
                                    // overheal may be calculated wrong if this fails, and that's very likely
                                    if($ability['threat']/$ability['heal']>0.5) {
                                        // tank
                                        $threatcoefficient = 0.75;
                                    } elseif($ability['threat']/$ability['heal']>0.45) {
                                        // dd
                                        $threatcoefficient = 0.5;
                                    } else {
                                        // healer
                                        $threatcoefficient = 0.45;
                                    }
                            }
                    }
                    if($ability['threat']) {
                        $ability['overheal'] = $ability['heal'] - $ability['threat'] * pow($threatcoefficient, -1);
                        $overall['overheal'] += $ability['overheal'];
                    }
                    if($ability['heal']>0) {
                        $data .= "<tr>
                                <td>".$ability_name."</td>
                                <td>".$ability['count']."</td>
                                <td>".$ability['heal']."</td>";
                        if(!$hidexps) {
                            $data .= "<td>".round($ability['heal'] / $duration, 2)."</td>";
                        }
                        $data .= "<td>".round($ability['heal'] / $ability['count'], 2)."</td>
                                <td>".round($ability['overheal'], 2)."</td>
                                <td>".round((100/$ability['heal'])*$ability['overheal'], 2)."%</td>
                                <td>".$ability['threat']."</td>";
                        if(!$hidexps) {
                            $data .= "<td>".round($ability['threat'] / $duration, 2)."</td>";
                        }
                        $data .= "<td>".round($ability['threat'] / $ability['count'], 2)."</td>
                                <td>".round($ability['threat'] / $ability['heal'], 2)."</td>
                                <td>".$ability['hit']."</td>
                                <td>".$ability['crit']."</td>
                                <td>".round(100/$ability['count']*$ability['hit'], 2)."%</td>
                                <td>".round(100/$ability['count']*$ability['crit'], 2)."%</td>
                            </tr>";
                    }
                }
                
                $html = "<div style='border-top: 1px solid silver'>Gesamt:<table>
                    <tr>
                        <td rowspan='2' valign='top'>
                            <table>
                                <tr><td>".guil('normal').":         </td><td>".$overall['hit']."</td>       <td>(".round(100/$overall['count']*$overall['hit'],     2)."%)</td></tr>
                                <tr><td>".guil('crit').":           </td><td>".$overall['crit']."</td>      <td>(".round(100/$overall['count']*$overall['crit'],    2)."%)</td></tr>
                                <tr><td>Overheal:                   </td><td colspan='2'>".round($overall['overheal'], 2)." (".round(100/$overall['heal'] * $overall['overheal'], 2)."%)</td></tr>";
                if(!$hidexps) {
                    $html .= "<tr><td>HPS:                        </td><td colspan='2'>".round($overall['heal']/$duration, 2)."</td></tr>
                                <tr><td>HPS (".guil('effective')."):</td><td colspan='2'>".round(($overall['heal']-$overall['overheal'])/$duration, 2)."</td></tr>";
                }
                $html .= "</table>
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

            if(!$hidexps) {
                $headers = array(guil('ability'), 'Use', 'Heal', 'HPS', 'Heal/Use', 'Overheal', 'Overheal %', 'Threat', 'TPS', 'Threat/Use', 'Threat/Heal',
                        'Hit', 'Crit', 'Hit %', 'Crit %');
            } else {
                $headers = array(guil('ability'), 'Use', 'Heal', 'Heal/Use', 'Overheal', 'Overheal %', 'Threat', 'Threat/Use', 'Threat/Heal',
                        'Hit', 'Crit', 'Hit %', 'Crit %');
            }

            parent::Tab(
                $name, 
                guil('healperability'), 
                $headers, 
                $data,
                $html,
                $class
            );
            
            $this->tooltips['Overheal'] = 'experimental estimate!';
            $this->tooltips['Overheal %'] = 'experimental estimate!';
        }
    }
?>