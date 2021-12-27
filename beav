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
            $this->maps = str_split(fread($fin,8));
    
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

    public function decodePath()
    {
        $bin = 0;
        $h = 0;
        foreach ($this->maps as $key)
        {
            $bin <<= 8;
            $bin += abs(ord($key));
        }
        $check_bin = abs($bin);
        $nibbles = $check_bin;
        $i = 0;
        while (($check_bin) > 256)
        {
            $check_bin = abs(round($check_bin * 0.10));
            $i++;
        }
        
        $this->line .= chr(ceil($check_bin));
        $check_bin = ceil($check_bin);
        echo " ".$i. "!";
        $bin = $check_bin;
        $this->line .= chr($i);
        while ($i > 0)
        {
            $i--;
            $check_bin *= (round($check_bin) *10 + $check_bin*0.99);
        }
        $this->line .= chr((int)($nibbles - $check_bin));
        echo "\$".(int)($nibbles-$check_bin)."\$";
        //$this->line .= chr($check_bin);// str_repeat("0",8-strlen(decbin(abs()))) . decbin(abs($j));
        //$this->line .= chr($i);// str_repeat("0",8-strlen(decbin(abs()))) . decbin(abs($j));
        return;
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

        foreach (str_split($this->line,1) as $kv)
        {
            $str .= $kv;
        }
        $this->path_cntr += strlen($str);
        $this->display($filename);

        $this->line = "";
        fwrite($fout,$str);
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
