<?
    class Benchmark {
        var $snapshots;
        var $memory_peak_usage;
        var $memory_peak_usage_real;
        var $runtime;
        
        function Benchmark() {
            $this->snapshots = array();
            $this->snapshot('__initialisation');
        }
        
        function snapshot($name) {
            $s = count($this->snapshots);
            $this->snapshots[$s]['name']        = $name;
            $this->snapshots[$s]['time']        = microtime(true);
            $this->snapshots[$s]['mem']         = memory_get_usage();
            $this->snapshots[$s]['memreal']     = memory_get_usage(true);
            $this->snapshots[$s]['includes']    = get_included_files();
            
            if($s>0) {
                $this->snapshots[$s]['diff']['time'] = $this->snapshots[$s]['time'] - $this->snapshots[$s-1]['time'];
                $this->snapshots[$s]['diff']['mem'] = $this->snapshots[$s]['mem'] - $this->snapshots[$s-1]['mem'];
                $this->snapshots[$s]['diff']['memreal'] = $this->snapshots[$s]['mem_real'] - $this->snapshots[$s-1]['mem_real'];
                $this->snapshots[$s]['diff']['includes_count'] = count($this->snapshots[$s]['includes']) - count($this->snapshots[$s-1]['includes']);
            }
        }
        
        function summary($filename='') {
            $this->memory_peak_usage = memory_get_peak_usage();
            $this->memory_peak_usage_real = memory_get_peak_usage(true);
            $this->runtime = microtime(true) - $this->snapshots[0]['time'];
            
            $summary = "\n\n\n--- ".date('Y-m-d H:i:s')." ---\n";
            ob_start();
            print_r($_SESSION);
            print_r($this);
            $summary .= ob_get_clean();
            $summary .= "\n-- -- -- -- -- -- -- -- -- -- -- --\n";
            foreach($this->snapshots as $snapshot) {
                $summary .= sprintf("% 8s % 8s %s\n", $snapshot['mem'], $snapshot['diff']['mem'], $snapshot['name']);
            }
            $summary .= "-- -- -- -- -- -- -- -- -- -- -- --\n";
            if($filename) {
                $fh = fopen($filename, 'a');
                fwrite($fh, $summary);
                fclose($fh);
            }
            
            return $summary;
        }
    }
?>