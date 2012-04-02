<?
    global $cache_stats_filename, $openOptions, $duration, $logfiles, $benchmark;
    session_start();
?>
        <script type="text/javascript">
            $(function(){
                $( ".accordion" ).accordion({
                    autoHeight: false
                });
                $( "#accordion_options" ).accordion({
                    <?
                        if(!count($logfiles)) {
                            print "active: 1,\n";
                        }
                    ?>
                    autoHeight: false,
                });
                $( ".tabs" ).tabs();
                $( "input:submit, a, button", ".navigation" ).button();
                $( "a", ".navigation" ).click(function() { return false; });
            });
            $(document).ready(function() {
                $('.dataTable').dataTable( {
                    'bJQueryUI': true,
                    'bStateSave': false,
                    "bPaginate": false,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    'sDom': 'R<"H"W>t',
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $('td:odd', nRow).addClass( 'col2' );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $('.dataTableScrolling').dataTable( {
                    'bJQueryUI': true,
                    'bStateSave': false,
                    "bPaginate": false,
                    /* 'sScrollY': '200px',          buggy atm (datatables 1.9)
                       'bScrollAutoCss': true,  */
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    'sDom': 'R<"H"W>t',
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $('td:odd', nRow).addClass( 'col2' );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $('#datatable_optionsLogfiles').dataTable( {
                    'bJQueryUI': true,
                    "bSort": true,
                    "bFilter": false,
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $('td:odd', nRow).addClass( 'col2' );
                        return nRow;
                    },
                    "aaSorting": [[ 1, "desc" ]]
                });
                $( "#min_fight_duration_slider" ).slider({
                    <?
                        if($_SESSION['min_fight_duration']) {
                            print "value: ".$_SESSION['min_fight_duration'].",\n";
                        } else {
                            print "value: 11,\n";
                        }
                    ?>
                    min: 1,
                    max: 600,
                    step: 20,
                    slide: function( event, ui ) {
                        $( "#min_fight_duration_slider_value" ).val( ui.value );
                    }
                });
                // $( "#logrange_slider" ).slider({
                //     range: true,
                //     min: 1,
                //     max: <?= $duration ?>,
                //     <?
                //         print "values: [".($_SESSION['min_logrange']?$_SESSION['min_logrange']:0).", ".($_SESSION['max_logrange']?$_SESSION['max_logrange']:$duration)."],\n";
                //     ?>
                //     slide: function( event, ui ) {
                //         $( "#logrange_slider_value1" ).val( ui.values[ 0 ] );
                //         $( "#logrange_slider_value2" ).val( ui.values[ 1 ] );
                //     }
                // });
                $( "#min_fight_duration_slider_value" ).val( $( "#min_fight_duration_slider" ).slider( "value" ) );
                $( "#dialog_options" ).dialog({
                    <?
                        if(! $_SESSION['min_fight_duration'] || !$_SESSION['log_id'] || $openOptions==1) {
                            print "autoOpen: true,\n";
                            print "modal: true,\n";
                        } else {
                            print "autoOpen: false,\n";
                            print "modal: false,\n";
                        }
                    ?>
                    title: 'Optionen',
                    width: 800,
                    position: 'top'
                });
                $( "#dialog_help" ).dialog({
                    autoOpen: false,
                    title: 'Hilfe',
                    width: 800,
                    position: 'top'
                });
                $( "input:submit, button" ).button();
                $( "#button_open_dialog_options" ).click(function() {
                    $( "#dialog_options" ).dialog( "open" );
                    return false;
                });
                $( "#button_open_dialog_help" ).click(function() {
                    $( "#dialog_help" ).dialog( "open" );
                    return false;
                });
                $( "#button_start_upload" ).click(function() {
                    $( "#dialog_upload" ).dialog( "open" );
                    return false;
                });
                $( "#dialog_login" ).dialog({
                    title: 'Login',
                    modal: true
                });
                $( "#dialog_error" ).dialog({
                    title: 'Oops, ein Fehler!',
                    modal: true
                });
                $( "#dialog_message" ).dialog({
                    title: 'Eine Nachricht für Dich',
                    modal: true
                });
                $( "#dialog_upload" ).dialog({
                    title: 'Upload gestartet',
                    autoOpen: false,
                    modal: true
                });
                $('.dialog').dialog();
            } );
        </script>
    </body>
</html>