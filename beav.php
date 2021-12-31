    <?php

    class shuriken
    {
        public $maps;
        public $markers;
        public $path_cntr;
        public $line = "";
        public $map_iter;
        public $dictionary = [];

        function __construct($filename, $out)
        {
        
            $fin = fopen("$filename", "r");
            $fout = fopen("$out","w");

            $left = 0;

            $str = "";
            $compressed_rate = 0;
            while (filesize("$filename") > $this->map_iter)
            {
                $this->maps = str_split(fread($fin,7));
    
                $this->map_iter += count($this->maps);
    
                $this->decodePath();
    
                if ($this->map_iter%140000 == 0)
                {
                    $this->aggregateBits($filename, $fout);
                }
            }

            $this->aggregateBits($filename, $fout);
            echo "\n";
            fclose($fin);
            fclose($fout); 
        }

        public function verifyDecode($check_bin, $nibbles, $h, $i, $j)
        {
            $nibs = ($check_bin - $nibbles);
            $g = 0;
            $f = 0;

            do
            {
                for ($g = 0 ; $g < $i && pow(2,$i)-1 > ($nibs + (1 << $g+1)) ; $g++)
                {
                    $formula = (1 << $g);
                    $nibs += $formula;
                    
                    $nibs += ($nibs < 0) ? (1 << ($i)) * -1 : 0;
                    
                    if (pow(2,$i-1) - $nibs <= 8 && $g == $j)
                    {
                        echo abs($check_bin - $nibs) . " ";
                        return ".\n\r";
                    }
                }
                $f++;
            } while ($f < $i);
            echo ($check_bin - $nibs) . " ";
            return "($i $f $g)-($h $j $i) \n\r";
        }

        public function decodeString($h, $j, $nib)
        {
            $this->line .= str_repeat('0',6-strlen(decbin(($h-1)))) . decbin($h-1);
            $this->line .= str_repeat('0',5-strlen(decbin(($j)))) . decbin($j);
            if (strlen(decbin($nib-1)) > 3)
                $this->line .= str_repeat('0',7-strlen(decbin(($nib - 1)))) . decbin($nib - 1). "";
            else
                $this->line .= str_repeat('0',3-strlen(decbin(($nib - 1)))) . decbin($nib - 1). "";
        }

        public function decodePath()
        {
            $bin = 0;
            $rep = "";
            $h = 0;
            $check_bin = 0;
            foreach ($this->maps as $key)
            {
                    $bin <<= 8;
                    $bin += ord($key);
            }
            $nibbles = 0; 
            $check_bin = $bin;
                
            $h = 0;
            $j = 0;
            $i = 1;
            while (pow(2,$i) <= $check_bin) $i++;
            
            if ($check_bin ^ (1 << $i+1) > $check_bin && $check_bin < 0)
            {
                $check_bin *= -1;
                $check_bin ^= (1 << ($i+1));
               // echo decbin(1 << ($i+1)) . "\r\n" . decbin($check_bin) . "\r\n";
            }
                    
            for ( ; $h < $i ; $h++)
            {
                for ($j = 0 ; $j < $i && $check_bin > ($nibbles + (1 << $j+1)) ; $j++)
                {
                    $formula = (1 << $j);
                    $nibbles += $formula;
                        
                    $nibbles += ($nibbles < 0) ? (1 << $i) * -1 : 0;
                    
                    if (abs($check_bin - $nibbles) <= 8) {
                        echo "($h  $i)";
                        echo $this->verifyDecode($check_bin, abs($check_bin - $nibbles), $h, $i, $j);
                        return $this->decodeString($h, $j, abs($check_bin - $nibbles));
                        
                    }
                }
                //$nibbles <<= 1;
            }
            echo (($check_bin - $nibbles) < 8) ? "*" : "";
            $nibbles = ($check_bin - $nibbles);
            echo $this->verifyDecode($check_bin, $nibbles, $h, $i, $j);
            return $this->decodeString($h, $j, ($check_bin - $nibbles));
            // $this->line .= str_repeat("0",8-strlen(decbin(($check_bin - $nibbles)))) . decbin(abs($check_bin - $nibbles))."00";
        }

        public function encodePath($filename)
        {
        
            $info = fopen($filename, "r");
            $outfo = fopen($filename.".xiv", "w");
            $decbin = "";
            $this->maps = fread($info, filesize($filename));
            foreach (str_split($this->maps,1) as $kv)
            {
                $decbin .= decbin(ord($kv));
            }

            while (strlen($decbin) > 0)
            {
                $bin1s = ltrim('1',$decbin);
                $last = substr($decbin,0,8);
                $x = strlen($bin1s);
                $last = bindec($last);

                for ($i = 0 ; $i < strlen($decbin) - $x ; $i++)
                {
                    $last <<= 7;
                    $last = (($last + 127));
                }
                $last >>= 8;
                $string = "";
                while ($last > 0)
                {
                    $byte = $last%256;
                    $string .= chr($byte);
                    $last = ($last >> 8);
                }
                fwrite($outfo,$string);
                $decbin = substr($decbin, -$x - 8);
            }
            fclose($outfo);
            fclose($info);
        }

        public function aggregateBits($filename, $fout)
        {
            $str = "";

            foreach (str_split($this->line,8) as $val)
            {
                $str .= chr($val);
            }

            $this->path_cntr += strlen($str);
            $this->display($filename);
            fwrite($fout,$str);
            $this->line = "";
         }
     
         public function display($filename)
         {
            echo "Percent done: " . round($this->map_iter/filesize("$filename"),3)*100 . " %    \t\t\t";
            echo "Compression Rate: " . round($this->path_cntr/$this->map_iter,3)*100 . " %    \r";
         }
    }

    $x = new shuriken($argv[1], $argv[2]);
    $y = new shuriken($argv[2], "out1.file");
    $y = new shuriken("out1.file","out2.file");

    ?>
