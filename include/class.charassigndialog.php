<?
    class CharassignDialog extends Dialog {
        function CharassignDialog() {
            global $userchars, $logfiles, $sql;
            
            parent::Dialog('charassign', guil('assignyourchars'), '', 1);
            $parser = new Parser();

            $userchars_in_parser = 0;
            foreach($userchars as $userchar_id => $userchar) {
                $userchar_names[] = $userchar['name'];
                if(in_array($userchar['name'], array_keys($parser->players))) {
                    $userchars_in_parser++;
                }
            }

            if($userchars_in_parser) {
                $html = '<p>'.guil('assignyourchars_note').'. '.guil('createchars_note').'</p>
                    <form action="" method="POST">
                        <table>';
                foreach(array_keys($parser->players) as $logchar) {
                    if(in_array($logchar, $userchar_names)) {
                        $html .= '<tr><td>'.$logchar.' ist </td><td><select name="selectchar['.$logchar.']"><option value="-1"></option>';
                        foreach($userchars as $userchar_id => $userchar) {
                            if($userchar['name'] == $logchar) {
                                $selected = 'selected="selected"';
                            } else {
                                $selected = '';
                            }
                            $html .= '<option '.$selected.' value="'.$userchar_id.'">'.$userchar['name'].' ('.$userchar['class'].'/'.$userchar['server'].')</option>';
                        }
                        $html .= '</select></td></tr>';
                    }
                }
                $html .= '</table>
                        <input type="hidden" name="op" value="charassign">
                        <center><input type="submit" value="'.guil('saveassignment').'"></center>
                    </form>';
            } else {
                $html = '<p>'.guil('noneofyourcharsfound').' '.guil('createchars_note').'</p>';
            }
            
            if(count($logfiles[$_SESSION['log_id']]['chars'])>0) {
                $html .= '<p>Aktuell zugeordnet:</p><ul>';
                foreach($logfiles[$_SESSION['log_id']]['chars'] as $logfile_char) {
                    if(isset($userchars[$logfile_char['id']])) {
                        // own log, own char
                        $charname = $userchars[$logfile_char['id']]['name'];
                        $classname = $userchars[$logfile_char['id']]['class'];
                        $servername = $userchars[$logfile_char['id']]['server'];
                    } else {
                        // public log, others char
                        $res = $sql['main']->query("select c.name,
                                                    coalesce(cl.".$_SESSION['language'].", cl.de, cl.en, cl.fr, cl.other),
                                                    s.name
                                                    from `char` c
                                                        join class cl on (cl.class_id = c.class_id)
                                                        join server s on (s.id = c.server_id)
                                                        where c.id='".$logfile_char['id']."'");
                        list($charname, $classname, $servername) = $sql['main']->fetch_row($res);
                    }
                    $html .= '<li>"'.$logfile_char['name'].'" ist "'.$charname.' ('.$classname.'/'.$servername.')"</li>';
                }
                $html .= '</ul>';
            }

            $this->content = $html;
        }
    }
?>