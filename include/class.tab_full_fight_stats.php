<?
    class Tab_Full_Fight_Stats extends Tab {
        function Tab_Full_Fight_Stats($name, $char, $start_id, $end_id, $eventtext=1) {
            global $parser, $benchmark;
            
            $data = '';
            $html = '';
            $hitpoint_gain_overall=0;
            $threat_gain_overall=0;
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
                    
                    switch($logdata['effect_id']) {
                        case DAMAGE:
                            if($logdata['target_name'] == $char) {
                                $damage_received = $logdata['hitpoints'];
                                $hitpoint_gain_overall += $logdata['hitpoints'];
                            }
                            if($logdata['source_name'] == $char) {
                                $damage_done = $logdata['hitpoints'];
                                $threat_gain += $logdata['threat'];
                                $threat_gain_overall += $logdata['threat'];
                            }
                            break;
                        case HEAL:
                            if($logdata['target_name'] == $char) {
                                $heal_received = $logdata['hitpoints'];
                                $hitpoint_gain_overall -= $logdata['hitpoints'];
                            }
                            if($logdata['source_name'] == $char) {
                                $heal_done = $logdata['hitpoints'];
                                $threat_gain += $logdata['threat'];
                                $threat_gain_overall += $logdata['threat'];
                            }
                            break;
                    }
                    // supress output of 0-values
                    $damage_done=($damage_done==0?"":$damage_done);
                    $heal_done=($heal_done==0?"":$heal_done);
                    $damage_received=($damage_received==0?"":$damage_received);
                    $heal_received=($heal_received==0?"":$heal_received);
                    $hitpoint_gain_overall=($hitpoint_gain_overall==0?"":$hitpoint_gain_overall);
                    $threat_gain=($threat_gain==0?"":$threat_gain);
                    $threat_gain_overall=($threat_gain_overall==0?"":$threat_gain_overall);
                    
                    $data .= "<tr>
                                <td>".$id."</td>
                                <td>".date('H:i:s', $logdata['timestamp'])."</td>
                                <td>".$logdata['source_name']."</td>
                                <td>".$logdata['target_name']."</td>
                                <td>".$logdata['ability_name']."</td>
                                <td>".$logdata['effect_name']."</td>
                                <td>".$logdata['damage_type']."</td>
                                <td>".$damage_done."</td>
                                <td>".$heal_done."</td>
                                <td>".$threat_gain."</td>
                                <td>".$damage_received."</td>
                                <td>".$heal_received."</td>
                                <td>".$hitpoint_gain_overall."</td>
                                <td>".$threat_gain_overall."</td>
                            </tr>";
                }
            }
            $html = "<p><img src='?op=pixelmap&min_id=".$start_id."&max_id=".$end_id."&section=damage-heal_overall&secondary_sections[]=heal&char=".$char."&conditions_lvalue[]=target_name&conditions_rvalue[]=".$char."&eventtext=".$eventtext."' alt='Damageverlauf (in)'></p>
                     <p><img src='?op=pixelmap&min_id=".$start_id."&max_id=".$end_id."&section=damage-heal_overall&secondary_sections[]=damage&char=".$char."&conditions_lvalue[]=target_name&conditions_rvalue[]=".$char."&eventtext=".$eventtext."' alt='Damageverlauf (in)'></p>";


            parent::Tab(
                $name, 
                'Kampfverlauf', 
                array('ID', 'Zeit', 'Quelle', 'Ziel', 'Fähigkeit', 'Effekt', 'Schadensart', 'DMG (out)', 'Heal(out)', 'Threat', 'DMG (in)', 'Heal (in)', '-HP Verlauf', 'Threat Verlauf'), 
                $data,
                $html,
                'dataTableScrolling'
            );
        }
    }
?>