<?
    class Tab {
        var $name;
        var $title;
        var $columns;
        var $data;
        var $html;
        var $class;
        
        function Tab($name, $title, $columns='', $data='', $html='', $class='') {
            $this->name = $name;
            $this->title = $title;
            if(isset($columns) && !empty($columns)) {
                $this->columns = $columns;
            }
            if(isset($data) && !empty($data)) {
                $this->data = $data;
            }
            if(isset($html) && !empty($html)) {
                $this->html = $html;
            }
            if(isset($class) && !empty($class)) {
                $this->class = $class;
            }
        }
        
        function load_logdata($start_id, $end_id) {
            global $parser;

            $parser->read_loglines($start_id, $end_id);
            $parser->gather_logdata();
            unset($parser->loglines);
            
            return $parser->logdata;
        }
        

        function nameplate() {
            if($this->data || $this->html) {
                return "<li><a href='#".$this->name."'>".$this->title."</a></li>";
            } else {
                return '';
            }
        }

        function tabcontent() {
            $output = '';
            if($this->data || $this->html) {
                $output .= "<div id='".$this->name."'>";
                if(isset($this->columns) && count($this->columns)>0) {
                    $output .= "<table class='".($this->class?$this->class:'dataTable')." ".$this->name."'>
                            <thead>
                                <tr>";
                    foreach($this->columns as $colum_title) {
                        $output .= "\n<th>$colum_title</th>";
                    }
                    $output .= "</tr>
                            </thead>
                            <tbody>
                                ".$this->data."
                            </tbody>
                        </table>";
                } else {
                    $output .= $this->data;
                }
                if(isset($this->html)) {
                    $output .= $this->html;
                }
                $output .= "</div>";
            }
            return $output;
        }
        
        function datatable_constructor_for_json() {
            return "$('#datatable_json_".$this->name."').dataTable( {
                        'bProcessing': true,
                        'sAjaxSource': 'report.php?op=".$this->name."_json',
                        'iDisplayLength': 25,
                        'bStateSave': false,
                        'bJQueryUI': true,
                        'sDom': 'R<\"H\"pirfC>t<\"F\"pi>'
                    });\n";
        }
        
        function tabcontent_htmlskeleton_for_json() {
            $output = '';
            if($this->data || $this->html) {
                $output .= "<div id='".$this->name."'>";
                if(isset($this->columns) && count($this->columns)>0) {
                    $output .= "<table class='dataTable_json_".$this->name."'>
                            <thead>
                                <tr>";
                    foreach($this->columns as $colum_title) {
                        $output .= "\n<th>$colum_title</th>";
                    }
                    $output .= "</tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>";
                }
                if(isset($this->html)) {
                    $output .= $this->html;
                }
                $output .= "</div>";
            }
            return $output;
        }
        
        function datatable_content_json() {
            return '{ "aaData": '.json_encode($this->data).' }';
        }
    }
?>