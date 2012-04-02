<?
    class Tab_Char_DpsTps_per_Ability extends Tab {
        
        function Tab_Char_DpsTps_per_Ability($name, $char, $start_id, $end_id) {
            global $logdata;
            
            $data = '';
            $html = '';
            
            $used_abilities = array();
            $overall = array();
            for($id=$start_id; $id<=$end_id; $id++) {
                if(preg_match('/^'.$char.'(:(.+))?/', $logdata[$id]['source_name'], $matches)) {
                    $ability_name = $logdata[$id]['ability_name'];
                    if($logdata[$id]['source_type'] == 'companion') {
                        $ability_name = $matches[2].': '.$ability_name;
                    }
                    switch($logdata[$id]['effect_id']) {
                        case DAMAGE:
                            $used_abilities[$ability_name]['damage'] += $logdata[$id]['hitpoints'];
                            $used_abilities[$ability_name]['threat'] += $logdata[$id]['threat'];
                            $used_abilities[$ability_name]['hit'] += $logdata[$id]['hit'];
                            $used_abilities[$ability_name]['crit'] += $logdata[$id]['crit'];
                            $used_abilities[$ability_name]['miss'] += $logdata[$id]['miss'];
                            $used_abilities[$ability_name]['dodge'] += $logdata[$id]['dodge'];
                            $used_abilities[$ability_name]['parry'] += $logdata[$id]['parry'];
                            $used_abilities[$ability_name]['deflect'] += $logdata[$id]['deflect'];
                            $used_abilities[$ability_name]['immune'] += $logdata[$id]['immune'];
                            $used_abilities[$ability_name]['count']++;
            
                            $overall['damage'] += $logdata[$id]['hitpoints'];
                            $overall['threat'] += $logdata[$id]['threat'];
                            $overall['hit'] += $logdata[$id]['hit'];
                            $overall['crit'] += $logdata[$id]['crit'];
                            $overall['miss'] += $logdata[$id]['miss'];
                            $overall['dodge'] += $logdata[$id]['dodge'];
                            $overall['parry'] += $logdata[$id]['parry'];
                            $overall['deflect'] += $logdata[$id]['deflect'];
                            $overall['immune'] += $logdata[$id]['immune'];
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
                            <td>".$ability['damage']."</td>
                            <td>".round($ability['damage'] / $duration, 2)."</td>
                            <td>".round($ability['damage'] / $ability['count'], 2)."</td>
                            <td>".$ability['threat']."</td>
                            <td>".round($ability['threat'] / $duration, 2)."</td>
                            <td>".round($ability['threat'] / $ability['count'], 2)."</td>
                            <td>".($ability['hit']+$ability['crit'])."</td>
                            <td>".$ability['hit']."</td>
                            <td>".$ability['crit']."</td>
                            <td>".$ability['miss']."</td>
                            <td>".$ability['dodge']."</td>
                            <td>".$ability['parry']."</td>
                            <td>".$ability['deflect']."</td>
                            <td>".$ability['immune']."</td>
                            <td>".round(100/$ability['count']*($ability['hit']+$ability['crit']), 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['hit'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['crit'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['miss'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['dodge'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['parry'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['deflect'], 2)."%</td>
                            <td>".round(100/$ability['count']*$ability['immune'], 2)."%</td>
                        </tr>";
                }
            
                $html = "<div style='border-top: 1px solid silver'>Gesamt:<table>
                    <tr>
                        <td rowspan='10' colspan='2'>
                        <img src='?op=piechart"
                            ."&labels[0]=Hit"
                            ."&labels[1]=Crit"
                            ."&labels[2]=Miss"
                            ."&labels[3]=Dodge"
                            ."&labels[4]=Parry"
                            ."&labels[5]=Deflect"
                            ."&labels[6]=Immune"
                            ."&values[0]=".$overall['hit']
                            ."&values[1]=".$overall['crit']
                            ."&values[2]=".$overall['miss']
                            ."&values[3]=".$overall['dodge']
                            ."&values[4]=".$overall['parry']
                            ."&values[5]=".$overall['deflect']
                            ."&values[6]=".$overall['immune']
                        ."' alt='Hit/Crit/Miss/Dodge..'>
                        </td>
                    </tr>
                    <tr><td>Treffer (hit+crit): </td><td>".($overall['hit']+$overall['crit'])."</td>      <td>".round(100/$overall['count']*($overall['hit']+$overall['crit']),     2)."%</td></tr>
                    <tr><td>Treffer (noncrit):</td><td>".$overall['hit']."</td>    <td>".round(100/$overall['count']*$overall['hit'],     2)."%</td></tr>
                    <tr><td>Kritisch:       </td><td>".$overall['crit']."</td>     <td>".round(100/$overall['count']*$overall['crit'],    2)."%</td></tr>
                    <tr><td>Verfehlt:       </td><td>".$overall['miss']."</td>     <td>".round(100/$overall['count']*$overall['miss'],    2)."%</td></tr>
                    <tr><td>Ausgewichen:    </td><td>".$overall['dodge']."</td>    <td>".round(100/$overall['count']*$overall['dodge'],   2)."%</td></tr>
                    <tr><td>Parriert:       </td><td>".$overall['parry']."</td>    <td>".round(100/$overall['count']*$overall['parry'],   2)."%</td></tr>
                    <tr><td>Schild:         </td><td>".$overall['deflect']."</td>  <td>".round(100/$overall['count']*$overall['deflect'], 2)."%</td></tr>
                    <tr><td>Immun:          </td><td>".$overall['immune']."</td>   <td>".round(100/$overall['count']*$overall['immune'],  2)."%</td></tr>
                </table>
                </div>";
            }
            
            parent::Tab(
                $name, 
                'Damage pro Fähigkeit', 
                array('Fähigkeit', 'Use', 'Damage', 'DPS', 'Damage/Use', 'Threat', 'TPS', 'Threat/Use',
                        'Hit (alle)', 'Hit (noncrit)', 'Crit', 'Miss', 'Dodge', 'Parry', 'Deflect', 'Immune',
                        'Hit (alle) %', 'Hit (noncrit) %', 'Crit %', 'Miss %', 'Dodge %', 'Parry %', 'Deflect %', 'Immune %'), 
                $data,
                $html
            );
        }
    }
?>
