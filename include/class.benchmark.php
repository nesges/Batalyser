<?
    class Benchmark {
        var $filename;
        var $snapshots;
        var $memory_peak_usage;
        var $memory_peak_usage_real;
        var $runtime;
        
        function Benchmark($filename) {
            $this->filename = $filename;
            $this->snapshots = array();
            
            $cpu = getrusage();
        	define('PHP_TUSAGE', microtime(true));
        	define('PHP_RUSAGE', $cpu["ru_utime.tv_sec"]*1e6+$cpu["ru_utime.tv_usec"]);
        	
        	if($this->filename) {
                $fh = fopen($this->filename, 'a');
                fwrite($fh, "\n--- ".date('Y-m-d H:i:s')." ---\n");
                fwrite($fh, sprintf("% 17s % 10s %s\n", 'memreal', 'cpu', 'name'));
                fclose($fh);
            }
        }
        
        function snapshot($name) {
            $s = count($this->snapshots);
            $this->snapshots[$s]['name']        = $name;
            $this->snapshots[$s]['time']        = microtime(true);
            $this->snapshots[$s]['mem']         = memory_get_usage();
            $this->snapshots[$s]['memreal']     = memory_get_usage(true);
            $this->snapshots[$s]['cpu']         = $this->getCpuUsage();
            // $this->snapshots[$s]['includes']    = get_included_files();
            
            if($s>0) {
                $this->snapshots[$s]['diff']['time'] = $this->snapshots[$s]['time'] - $this->snapshots[$s-1]['time'];
                $this->snapshots[$s]['diff']['mem'] = $this->snapshots[$s]['mem'] - $this->snapshots[$s-1]['mem'];
                $this->snapshots[$s]['diff']['memreal'] = $this->snapshots[$s]['memreal'] - $this->snapshots[$s-1]['memreal'];
                $this->snapshots[$s]['diff']['cpu'] = $this->snapshots[$s]['cpu'] - $this->snapshots[$s-1]['cpu'];
                $this->snapshots[$s]['diff']['includes_count'] = count($this->snapshots[$s]['includes']) - count($this->snapshots[$s-1]['includes']);
            }
            
            if($this->filename) {
                $snapshot = sprintf("% 17s % 10s %s\n", $this->snapshots[$s]['memreal'], $this->snapshots[$s]['cpu'], $name);
                
                $fh = fopen($this->filename, 'a');
                fwrite($fh, $snapshot);
                fclose($fh);
            }
        }
        
        function summary() {
            if(count($this->snapshots)>0) {
                $this->memory_peak_usage = memory_get_peak_usage();
                $this->memory_peak_usage_real = memory_get_peak_usage(true);
                $this->runtime = microtime(true) - $this->snapshots[0]['time'];
                
                $summary = "\n\n\n--- ".date('Y-m-d H:i:s')." ---\n";
                ob_start();
                print_r($_SESSION);
                print_r($this);
                $summary .= ob_get_clean();
                $summary .= "\n-- -- -- -- -- -- -- -- -- -- -- --\n";
                $summary .= sprintf("% 8s % 8s % 10s %s\n", 'mem', 'diffmem', 'diffcpu', 'name');
                foreach($this->snapshots as $snapshot) {
                    $summary .= sprintf("% 8s % 8s % 10s %s\n", $snapshot['mem'], $snapshot['diff']['mem'], round($snapshot['diff']['cpu'],2), $snapshot['name']);
                }
                $summary .= "-- -- -- -- -- -- -- -- -- -- -- --\n";
                if($this->filename) {
                    $fh = fopen($this->filename, 'a');
                    fwrite($fh, $summary);
                    fclose($fh);
                }
                
                unset($this->snapshots);            
                return $summary;
            }
        }
        
         
        function getCpuUsage() {
            // http://php.webtutor.pl/en/2011/05/13/how-to-calculate-cpu-usage-of-a-php-script/
            $dat = getrusage();
            $dat["ru_utime.tv_usec"] = ($dat["ru_utime.tv_sec"]*1e6 + $dat["ru_utime.tv_usec"]) - PHP_RUSAGE;
            $time = (microtime(true) - PHP_TUSAGE) * 1000000;
         
            // cpu per request
            if($time > 0) {
                $cpu = sprintf("%01.2f", ($dat["ru_utime.tv_usec"] / $time) * 100);
            } else {
                $cpu = '0.00';
            }
            return $cpu;
        }
    }
?>