<?
    class Tab_DpsHpsTps_per_Target extends Tab {
        function Tab_DpsHpsTps_per_Target($name, $char, $start_id, $end_id, $class='', $hidexps=0) {
            global $parser;

            $targets = array();
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

                if(preg_match('/^'.$char.'(:.+)?/', $logdata['source_name'])) {
                    $target_name = $logdata['target_name'];
                    switch($logdata['effect_id']) {
                        case HEAL:
                            $targets[$target_name]['healed'] += $logdata['hitpoints'];
                            break;
                        case DAMAGE:
                            $targets[$target_name]['damage'] += $logdata['hitpoints'];
                    }
                    $targets[$target_name]['threat'] += $logdata['threat'];
                    $targets[$target_name]['target_type'] = $logdata['target_type'];
                }
            }

            $data = '';
            $duration = $end_timestamp - $start_timestamp;
            if($duration) {
                foreach($targets as $target_name => $target) {
                    if($target['target_type'] == 'companion') {
                        $target_name = preg_replace('/'.$char.':/', '', $target_name).' (Companion)';
                    }
                    if($target['damage']>0 || $target['healed']>0 || $target['threat']>0) {
                        switch($target['target_type']) {
                            case 'player':      
                                if($target_name == $char) {
                                    // self
                                    $bgcolor = 'style="background-color:#ddffdd"'; 
                                } else {
                                    // other player
                                    $bgcolor = 'style="background-color:#ffffdd"'; 
                                }
                                break;
                            case 'companion':   $bgcolor = 'style="background-color:#ddddff"'; break;
                            default:            $bgcolor = 'style="background-color:#ffdddd"'; break;
                        }
                        $data .= "<tr>
                                <td ".$bgcolor.">".$target_name."</td>
                                <td>".$target['damage']."</td>
                                <td>".$target['healed']."</td>
                                <td>".$target['threat']."</td>";
                        if(!$hidexps) {
                            $data .= "<td>".round($target['damage'] / $duration, 2)."</td>
                                <td>".round($target['healed'] / $duration, 2)."</td>
                                <td>".round($target['threat'] / $duration, 2)."</td>";
                        }
                        $data .= "</tr>";
                    }
                }
            }

            $headers = array(guil('target'), guil('damage'), guil('heal'), guil('threat'));
            if(!$hidexps) {
                $title = guil('xpspertarget');
                $headers = array_merge($headers, array('DPS', 'HPS', 'TPS'));
            } else {
                $title = guil('damagehealthreatpertarget');
            }

            parent::Tab(
                $name, 
                $title, 
                $headers, 
                $data,
                $html,
                $class
            );
        }
    }
?>