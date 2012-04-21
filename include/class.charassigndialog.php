<?
    class CharassignDialog extends Dialog {
        function CharassignDialog() {
            global $userchars;
            
            parent::Dialog('charassign', 'Deine Chars diesem Log zuordnen', '', 1);
            $parser = new Parser();

            $userchars_in_parser = 0;
            $select_options_userchars = '<option value="-1"></option>';
            foreach($userchars as $userchar_id => $userchar) {
                $select_options_userchars .= '<option value="'.$userchar_id.'">'.$userchar['name'].' ('.$userchar['class'].'/'.$userchar['server'].')</option>';
                $userchar_names[] = $userchar['name'];
                if(in_array($userchar['name'], array_keys($parser->players))) {
                    $userchars_in_parser++;
                }
            }

            if($userchars_in_parser) {
                $html = '<p>Ordne hier die im aktuell geladenen Logfile vorkommenden Chars deinen eigenen Chars zu. Du kannst deine Chars im Optionen-Dialog anlegen.</p>
                    <form action="" method="POST">
                        <table>';
                foreach(array_keys($parser->players) as $logchar) {
                    if(in_array($logchar, $userchar_names)) {
                        $html .= '<tr><td>'.$logchar.' ist </td><td><select name="selectchar['.$logchar.']">'.$select_options_userchars.'</select></td></tr>';
                    }
                }
                $html .= '</table>
                        <input type="hidden" name="op" value="charassign">
                        <center><input type="submit" value="Zuordnung speichern"></center>
                    </form>';
            } else {
                $html = '<p>Keiner deiner Chars wurde im aktuell geladenen Logfile gefunden. Du kannst deine Chars im Optionen-Dialog anlegen.</p>';
            }
            $this->content = $html;
        }
    }
?>