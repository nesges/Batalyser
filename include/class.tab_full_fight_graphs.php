<?
    class Tab_Full_Fight_Graphs extends Tab {
        function Tab_Full_Fight_Graphs($name, $char, $start_id, $end_id, $eventtext=1) {
            //global $logdata;
            //$logdata = parent::load_logdata($start_id, $end_id);

            $data = '';
            //if($logdata['base']['damage']) {
                $data .= "<p><img src='?op=pixelmap&min_id=".$start_id."&max_id=".$end_id."&section=damage&char=".$char."&conditions_lvalue[]=source_name&conditions_rvalue[]=".$char."&eventtext=".$eventtext."' alt='Damage Graph'></p>";
            //}
            //if($logdata['base']['heal']) {
                $data .= "<p><img src='?op=pixelmap&min_id=".$start_id."&max_id=".$end_id."&section=heal&char=".$char."&conditions_lvalue[]=source_name&conditions_rvalue[]=".$char."&eventtext=".$eventtext."' alt='Heal Graph' ></p>";
            //}
            $data .= "<p><img src='?op=pixelmap&min_id=".$start_id."&max_id=".$end_id."&section=threat&char=".$char."&conditions_lvalue[]=source_name&conditions_rvalue[]=".$char."&eventtext=".$eventtext."' alt='Threat Graph' ></p>";
            $data .= "<p><img src='?op=pixelmap&min_id=".$start_id."&max_id=".$end_id."&section=threat_overall&char=".$char."&conditions_lvalue[]=source_name&conditions_rvalue[]=".$char."&eventtext=".$eventtext."' alt='Threat-Overall Graph'></p>";

            parent::Tab(
                $name, 
                'Kampfverlauf (Graphen)', 
                '', 
                $data
            );
        }
    }
?>