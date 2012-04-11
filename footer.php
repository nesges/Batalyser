<?
    global $cache_stats_filename, $openOptions, $duration, $logfiles;
    
    $jquery_common_init = '
                $( ".accordion" ).accordion({
                    autoHeight: false,
                    collapsible: true
                });
                $( ".tabs" ).tabs();';
?>
        <script type="text/javascript">
            $(document).ajaxComplete(function(e, xhr, settings){
                <?=$jquery_common_init?>
                $( ".dataTable_ajaxLoaded").dataTable( {
                    "bJQueryUI": true,
                    "bStateSave": false,
                    "bPaginate": false,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    "bRetrieve": true,
                    "sDom": "R<\'H\'W>t",
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $("td:odd", nRow).addClass( "col2" );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $( ".dataTableFullFightStats_ajaxLoaded" ).dataTable( {
                    "bJQueryUI": true,
                    "bStateSave": false,
                    "bPaginate": false,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    "bRetrieve": true,
                    "sDom": "R<\'H\'W>t",
                    "aaSorting": [[ 0, "asc" ]]
                });
            });
            
            $(document).ready(function() {
                <?=$jquery_common_init?>
                $( "input:submit, a, button", ".navigation" ).button();
                $( "a", ".navigation" ).click(function() { return false; });
                $( "input:submit, button" ).button();
                $("#accordion_ajax").accordion({
                    header: "h3",
                    clearStyle: true,
                    autoHeight: false,
                    active: false,
                    change: function(event, ui){
                        var clicked = $(this).find(".ui-state-active").attr("id");
                        $("#"+clicked).load("/widgets/"+clicked);
                    }
                }); 
                $("h3", "#accordion_ajax").click(function(e) {
                    var contentDiv = $(this).next("div");
                    contentDiv.load($(this).find("a").attr("href"));      
                });
                $( ".dataTable").dataTable( {
                    "bJQueryUI": true,
                    "bStateSave": false,
                    "bPaginate": false,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": false,
                    "bAutoWidth": false,
                    "sDom": "R<\'H\'W>t",
                    "fnRowCallback": function( nRow, aaData, iDisplayIndex ) {
                        $("td:odd", nRow).addClass( "col2" );
                        return nRow;
                    },
                    "aaSorting": [[ 0, "asc" ]]
                });
                $( "#accordion_options" ).accordion({
                    <?
                        if(!count($logfiles)) {
                            print "active: 1,\n";
                        }
                    ?>
                    autoHeight: false,
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
                $('.dataTableFullFightStats').dataTable( {
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
                    step: 10,
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
                    title: '<?=guil('dialog_options_title')?>',
                    width: 800,
                    position: 'top'
                });
                $( "#dialog_help" ).dialog({
                    autoOpen: false,
                    title: '<?=guil('dialog_help_title')?>',
                    width: 800,
                    position: 'top'
                });
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
                    title: '<?=guil('dialog_login_title')?>',
                    modal: true
                });
                $( "#dialog_error" ).dialog({
                    title: '<?=guil('dialog_error_title')?>',
                    modal: true
                });
                $( "#dialog_message" ).dialog({
                    title: '<?=guil('dialog_message_title')?>',
                    modal: true
                });
                $( "#dialog_upload" ).dialog({
                    title: '<?=guil('dialog_upload_title')?>',
                    autoOpen: false,
                    modal: true
                });
                $('.dialog').dialog();
            } );
        </script>
    </body>
</html>
<?exit()?>