<?
    class Tab_Full_Fight_Stats extends Tab {
        function Tab_Full_Fight_Stats($name, $char, $start_id, $end_id, $eventtext=1, $class='') {
            global $parser, $benchmark;
            
            $data = '';
            $html = '';
            $hitpoint_gain_overall=0;
            $threat_gain_overall=0;
            $resource_gain_overall=0;
            $last_fetch = $start_id;
            for($id=$start_id; $id <= $end_id; $id++) {
                if($id >= $last_fetch + PARSER_MAX_FETCH) {
                    $last_fetch +=PARSER_MAX_FETCH;
                    unset($parser->loglines);
                    unset($parser->logdata);
                    $parser->read_loglines($id, $id+PARSER_MAX_FETCH);
                    $parser->gather_logdata();
                }
                $logdata = $parser->logdata[$id];

                if($logdata) {
                    $damage_received=0;
                    $heal_received=0;
                    $damage_done=0;
                    $heal_done=0;
                    $threat_gain=0;
                    $resource='';
                    $resource_spend = 0;
                    $resource_restored = 0;
                    
                    switch($parser->logchar2userchar[$char]['class_id']) {
                        case KNIGHT:
                        case GUARDIAN:
                        case SENTINEL:
                            $resource = 'focus';
                            break;
                        case WARRIOR:
                        case JUGGERNAUT:
                        case MARAUDER:
                            $resource = 'rage';
                            break;
                        case CONSULAR:
                        case INQUISITOR:
                        case SAGE:
                        case SORCERER:
                        case SHADOW:
                        case ASSASSIN:
                            $resource = 'force';
                            break;
                        case TROOPER:
                        case COMMANDO:
                        case VANGUARD:
                            $resource = 'ammo';
                            break;
                        case BOUNTYHUNTER:
                        case MERCENARY:
                        case POWERTECH:
                            $resource = 'heat';
                            break;
                        case SMUGGLER:
                        case IMPERIALAGENT:
                        case GUNSLINGER:
                        case SNIPER:
                        case SCOUNDREL:
                        case OPERATIVE:
                            $resource = 'energy';
                            break;
                    }

                    
                    switch($logdata['effect_type_id']) {
                        case SPEND:
                            switch($logdata['effect_id']) {
                                case HITPOINT:
                                    $damage_received = $logdata['hitpoints'];
                                    break;
                                case FORCE:
                                case ENERGY:
                                case RAGE:
                                case AMMO:
                                case FOCUS:
                                case HEAT:
                                    $resource_spend = $logdata['hitpoints'];
                                    break;
                            }
                            break;
                        case RESTORE:
                            switch($logdata['effect_id']) {
                                case HITPOINT:
                                    $heal_received = $logdata['hitpoints'];
                                    break;
                                case FORCE:
                                case ENERGY:
                                case RAGE:
                                case AMMO:
                                case FOCUS:
                                case HEAT:
                                    $resource_restored = $logdata['hitpoints'];
                                    break;
                            }
                            break;
                    }
                    $resource_gain = $resource_restored - $resource_spend;
                    $resource_gain_overall += $resource_gain;
                    
                    switch($logdata['effect_id']) {
                        case DAMAGE:
                            if($logdata['target_name'] == $char) {
                                $damage_received = $logdata['hitpoints'];
                            }
                            if(preg_match('/^'.$char.'(:.+)?/', $logdata['source_name'])) {
                                // char + companion
                                $damage_done = $logdata['hitpoints'];
                                
                            } 
                            if($logdata['source_name'] == $char) {
                                // char only
                                $threat_gain_overall += $logdata['threat'];
                            }
                            break;
                        case HEAL:
                            if($logdata['target_name'] == $char) {
                                $heal_received = $logdata['hitpoints'];
                            }
                            if(preg_match('/^'.$char.'(:.+)?/', $logdata['source_name'])) {
                                // char + companion
                                $heal_done = $logdata['hitpoints'];
                                $threat_gain += $logdata['threat'];
                            } 
                            if($logdata['source_name'] == $char) {
                                // char only
                                $threat_gain_overall += $logdata['threat'];
                            }
                            break;
                    }
                    $hitpoint_gain_overall += $damage_received;
                    $hitpoint_gain_overall -= $heal_received;
                    
                    // supress output of 0-values
                    $damage_done=($damage_done==0?"":$damage_done);
                    $heal_done=($heal_done==0?"":$heal_done);
                    $damage_received=($damage_received==0?"":$damage_received);
                    $heal_received=($heal_received==0?"":$heal_received);
                    $hitpoint_gain_overall=($hitpoint_gain_overall==0?"":$hitpoint_gain_overall);
                    $threat_gain=($threat_gain==0?"":$threat_gain);
                    $threat_gain_overall=($threat_gain_overall==0?"":$threat_gain_overall);
                    $resource_gain=($resource_gain==0?"":$resource_gain);
                    $resource_gain_overall=($resource_gain_overall==0?"":$resource_gain_overall);
                    
                    $row_style='';
                    if(preg_match('/^'.$char.':.+/', $logdata['source_name'])) {
                        // companion
                        if($logdata['effect_id']==DAMAGE) {
                            $row_style = 'style="background-color:#ccccff"';
                        } else {
                            $row_style = 'style="background-color:#ddddff"';
                        }
                    } elseif($logdata['source_name'] == $char) {
                        // char
                        if($logdata['effect_id']==DAMAGE) {
                            $row_style = 'style="background-color:#ccffcc"';
                        } elseif($logdata['effect_id']==DEATH) {
                            // ...kills something
                            $row_style = 'style="background-color:#00ff00"';
                        } else {
                            $row_style = 'style="background-color:#ddffdd"';
                        }
                    } elseif($logdata['source_type'] == 'player') {
                        // other player
                        if($logdata['effect_id']==DAMAGE) {
                            $row_style = 'style="background-color:#ffffcc"';
                        } else {
                            $row_style = 'style="background-color:#ffffdd"';
                        }
                    } else {
                        // npc
                        if($logdata['effect_id']==DAMAGE) {
                            $row_style = 'style="background-color:#ffcccc"';
                        } elseif($logdata['effect_id']==DEATH) {
                            // ...kills player
                            $row_style = 'style="background-color:#ff0000"';
                        } else {
                            $row_style = 'style="background-color:#ffdddd"';
                        }
                    }
                    
                    $data .= "<tr ".$row_style.">
                                <td>".$id."</td>
                                <td>".date('H:i:s', $logdata['timestamp'])."</td>
                                <td>".$logdata['source_name']."</td>
                                <td>".$logdata['target_name']."</td>
                                <td>".$logdata['ability_name']."</td>
                                <td>".$logdata['effect_name']."</td>
                                <td>".$logdata['damage_type']."</td>";
                    if($resource) {
                        $data .= "<td>".$resource_gain."</td>";
                        //$data .= "<td>".$resource_gain_overall."</td>";
                    }
                    $data .= "<td>".$damage_done."</td>
                                <td>".$heal_done."</td>
                                <td>".$threat_gain."</td>
                                <td>".$damage_received."</td>
                                <td>".$heal_received."</td>
                                <td>".$hitpoint_gain_overall."</td>
                                <td>".$threat_gain_overall."</td>";
                    $data .= "</tr>";
                }
            }
            $html = "<p>".guil('fullfightstats_note_companion')."</p>";

            $headers = array('ID', guil('time'), guil('source'), guil('target'), guil('ability'), guil('effect'), guil('damagetype'));
            if($resource) {
                $headers[] = ucfirst($resource);
                //$headers[] = ucfirst($resource.' '.guil('progress'));
            }
            $headers = array_merge($headers, array('DMG (out)', 'Heal(out)', 'Threat', 'DMG (in)', 'Heal (in)', '-HP '.guil('progress'), 'Threat '.guil('progress')));
            
            parent::Tab(
                $name, 
                guil('fightprogress'), 
                $headers,
                $data,
                $html,
                ($class?$class:'dataTableFullFightStats')
            );
        }
    }
?>