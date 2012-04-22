<?
    class Tab_Full_Fight_Graphs extends Tab {
        function Tab_Full_Fight_Graphs($name, $char, $start_id, $end_id, $eventtext=1, $class='') {
            $data = "<div class='accordion_ajax_ajax'>
                        <h4><a href='linechart_google.php?log_id=".$_SESSION['log_id']."&min_id=".$start_id."&max_id=".$end_id."&section[0]=hpprogress&cond[0][]=target_name=".$char."&section[1]=damage&cond[1][]=target_name=".$char."&section[2]=heal&cond[2][]=target_name=".$char."'>HP Verlauf</a></h4><div></div>
                        <h4><a href='linechart_google.php?log_id=".$_SESSION['log_id']."&min_id=".$start_id."&max_id=".$end_id."&section[0]=damage&cond[0][]=target_name=".$char."'>Damage an $char</a></h4><div></div>
                        <h4><a href='linechart_google.php?log_id=".$_SESSION['log_id']."&min_id=".$start_id."&max_id=".$end_id."&section[0]=damage&cond[0][]=source_name=".$char."'>Damage durch $char</a></h4><div></div>
                        <h4><a href='linechart_google.php?log_id=".$_SESSION['log_id']."&min_id=".$start_id."&max_id=".$end_id."&section[0]=allhealers'>Heilung an $char</a></h4><div></div>
                        <h4><a href='linechart_google.php?log_id=".$_SESSION['log_id']."&min_id=".$start_id."&max_id=".$end_id."&section[0]=heal&cond[0][]=source_name=".$char."'>Heilung durch $char</a></h4><div></div>
                        <h4><a href='linechart_google.php?log_id=".$_SESSION['log_id']."&min_id=".$start_id."&max_id=".$end_id."&section[0]=threat&cond[0][]=source_name=".$char."'>Bedrohung durch $char</a></h4><div></div>
                        <!--h4><a href='linechart_google.php?log_id=".$_SESSION['log_id']."&min_id=".$start_id."&max_id=".$end_id."&section[0]=threat&cond[0][]=source_name=".$char."&overall[0]=1'>Summierte Bedrohung durch $char</a></h4><div></div-->
                    </div>";
            
            parent::Tab(
                $name, 
                guil('fightprogress').' ('.guil('graphs').')', 
                '', 
                $data,
                $html,
                $class
            );
        }
    }
?>