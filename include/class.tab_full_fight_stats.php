<?
    class Tab_Full_Fight_Stats extends Tab {
        function Tab_Full_Fight_Stats($name, $char, $start_id, $end_id, $eventtext=1) {
            global $logdata;
            
            $data = '';
            $html = '';
            $hitpoint_gain_overall=0;
            $threat_gain_overall=0;
            for($id=$start_id; $id <= $end_id; $id++) {
                $damage_received=0;
                $heal_received=0;
                $damage_done=0;
                $heal_done=0;
                $threat_gain=0;

                switch($logdata[$id]['effect_id']) {
                    case DAMAGE:
                        if($logdata[$id]['target_name'] == $char) {
                            $damage_received = $logdata[$id]['hitpoints'];
                            $hitpoint_gain_overall += $logdata[$id]['hitpoints'];
                        }
                        if($logdata[$id]['source_name'] == $char) {
                            $damage_done = $logdata[$id]['hitpoints'];
                            $threat_gain += $logdata[$id]['threat'];
                            $threat_gain_overall += $logdata[$id]['threat'];
                        }
                        break;
                    case HEAL:
                        if($logdata[$id]['target_name'] == $char) {
                            $heal_received = $logdata[$id]['hitpoints'];
                            $hitpoint_gain_overall -= $logdata[$id]['hitpoints'];
                        }
                        if($logdata[$id]['source_name'] == $char) {
                            $heal_done = $logdata[$id]['hitpoints'];
                            $threat_gain += $logdata[$id]['threat'];
                            $threat_gain_overall += $logdata[$id]['threat'];
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
                            <td>".date('H:i:s', $logdata[$id]['timestamp'])."</td>
                            <td>".$logdata[$id]['source_name']."</td>
                            <td>".$logdata[$id]['target_name']."</td>
                            <td>".$logdata[$id]['ability_name']."</td>
                            <td>".$logdata[$id]['effect_name']."</td>
                            <td>".$logdata[$id]['damage_type']."</td>
                            <td>".$damage_done."</td>
                            <td>".$heal_done."</td>
                            <td>".$threat_gain."</td>
                            <td>".$damage_received."</td>
                            <td>".$heal_received."</td>
                            <td>".$hitpoint_gain_overall."</td>
                            <td>".$threat_gain_overall."</td>
                        </tr>";
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