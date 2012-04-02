<?
    class Tab_Char_HpsTps_per_Ability extends Tab {
        
        function Tab_Char_HpsTps_per_Ability($name, $char, $start_id, $end_id) {
            global $logdata;
            
            $data = '';
            $html = '';

            $used_abilities = array();
            $overall = array();
            for($id=$start_id; $id<=$end_id; $id++) {
                if(preg_match('/^'.$char.'(:.+)?/', $logdata[$id]['source_name'])) {
                    $ability_name = $logdata[$id]['ability_name'];
                    if($logdata[$id]['source_type'] == 'companion') {
                        $ability_name = $matches[2].': '.$ability_name;
                    }
                    switch($logdata[$id]['effect_id']) {
                        case HEAL:
                            $used_abilities[$ability_name]['heal'] += $logdata[$id]['hitpoints'];
                            $used_abilities[$ability_name]['threat'] += $logdata[$id]['threat'];
                            $used_abilities[$ability_name]['hit'] += $logdata[$id]['hit'];
                            $used_abilities[$ability_name]['crit'] += $logdata[$id]['crit'];
                            $used_abilities[$ability_name]['count']++;
            
                            $overall['heal'] += $logdata[$id]['hitpoints'];
                            $overall['threat'] += $logdata[$id]['threat'];
                            $overall['hit'] += $logdata[$id]['hit'];
                            $overall['crit'] += $logdata[$id]['crit'];
                            $overall['count']++;
                    }
                }
            }
            
            $duration = $logdata[$end_id]['timestamp'] - $logdata[$start_id]['timestamp'];
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
                            <td>".$ability['hit']."</td>
                            <td>".$ability['crit']."</td>
                            <td>".round(100/$ability['count']*$ability['hit'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['crit'], 2)."%</td>
                        </tr>";
                }
            
                $html = "<div style='border-top: 1px solid silver'>Gesamt:<table>
                    <tr>
                        <td rowspan='6' colspan='2'>
                        <img src='?op=piechart"
                            ."&labels[0]=Hit"
                            ."&labels[1]=Crit"
                            ."&values[0]=".$overall['hit']
                            ."&values[1]=".$overall['crit']
                        ."' alt='Hit/Crit/Miss/Dodge..'>
                        </td>
                    </tr>
                    <tr><td>Normal:         </td><td>".$overall['hit']."</td>       <td>(".round(100/$overall['count']*$overall['hit'],     2)."%)</td></tr>
                    <tr><td>Kritisch:       </td><td>".$overall['crit']."</td>      <td>(".round(100/$overall['count']*$overall['crit'],    2)."%)</td></tr>
                    <tr><td>HPS:            </td><td colspan='2'>".round($overall['heal']/$duration, 2)."</td></tr>
                </table>
                </div>";
            }

            parent::Tab(
                $name, 
                'Heal pro Fähigkeit', 
                array('Fähigkeit', 'Use', 'Heal', 'HPS', 'Heal/Use', 'Threat', 'TPS', 'Threat/Use',
                        'Hit', 'Crit', 'Hit %', 'Crit %'), 
                $data,
                $html
            );
        }
    }
?>