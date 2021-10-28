<?php
// ltrim each line
// use decomp to check answer
class Comb {
  
  public $current = "";
  public $bytes = 0;
  
  public function adiff($ic, &$hex, &$temp, $const)
  {
    // leave a 1 at the end
    $assertion = 0;
    start:
    if (strlen($ic) < 1)
      return $ic;
    $h = strlen($ic);
    $trim = substr($ic,0,1);
    $oc = ltrim($ic,"$trim");
    $f = $h-strlen($oc);

    // $f is ALWAYS AT LEAST 1
    // +1
    // WHAT BIT ARE WE USING
    //if ($temp == "")
    $f--;
    if ($assertion > 0 && $f == 0) {
      $assertion++;
      $temp .= "0";
      $ic = $oc;
      goto start;

    }
    else if ($assertion == 0 && $f == 0) {
      $assertion++;
      $ic = $oc;
      goto start;
    }
    else if ($assertion > 0 && $f > 0)
    {
      $f = $assertion;
      $assertion = 0;
      $f--;
      $oc = $ic;
      $temp .= "1";
    }
    $decrement = $f;
    $temp = decbin(($f >> 1)%2). decbin($f%2) . $temp;
    $f >>= 2;
    while ($f >= 4)
    {
      $temp .= decbin($f <= 4); // are we at zero?
      $f >>= 2;
    }
    if (strlen($oc) < 1)
    {
      // Here to while : test decompress
      $temp1 = ($temp);
      $blank = "";
      //$blank = $this->decomp($blank,$temp1);
      $rev = ($blank);
     // echo $const."\n\r";
     // echo ($rev).$oc."++++\n\r";

      while (strlen($temp) > 0)
      {
        $temp2 = substr($temp,0,8%(strlen($temp)+1));
        $hex .= chr(bindec($temp2));
        $temp = (strlen($temp) >= 8)?substr($temp,8%(strlen($temp)+1)):"";
      }
      return $oc;
    }
    
    return $this->adiff($oc, $hex, $temp, $const);
  }

  public function decomp($total, &$temp)
  {
    // HERE IS THE CODE FOR MAKING THE DECOMPRESSION
    // $f is ALWAYS AT LEAST 1
    // +1
    if (strlen($temp) < 1)
      return// str_repeat("$bit",abs(strlen($total)-16));
      $total;
    $bit = substr($temp,0,1); // This is the actual bit were using (line 24)
    $total .= $bit;
    $temp = substr($temp,1); // Remove the front bit
    // Next bit is the $f >= 0 bit ('>' == 1)
    if (substr($temp,0,1) == "0") // Checking to see if the bit we just used is it (24)
    {
      $temp = substr($temp,1); // >= 0 on line 24
      return $this->decomp(($total), $temp);
    }
    else if (substr($temp,0,1) == "1")
    {
      $total .= "$bit"; // if there is more, we subtract 1 bit (line 30)
      $temp = substr($temp,1);
    }
    if (substr($temp,0,1) == "1") 
    {
      $total .= "$bit"; //"$bit";
      $temp = substr($temp,1);
      $bitLength = substr($temp,0,2); // This is the 2 bits of length
      $number = bindec(($bitLength)); // this turns into a number
      $total .= str_repeat("$bit",$number); // we repeat that number
      $temp = substr($temp,2); // remove these three bits
      if (substr($temp,0,1) == "1") // is there more? (y == 1)
        goto fin; // go get the rest
      $temp = substr($temp,1);  //remove the bit from the if above
      return $this->decomp(($total), $temp);
    }
    else
    {
      //$total .= "$bit"; //"$bit";
      $temp = substr($temp,1);  //remove the bit from the if above
      return $this->decomp(($total), $temp); 
    }
    fin:
    if (substr($temp,0,1) == "1")
    {
      $total .= "$bit"; //"$bit";
      $temp = substr($temp,1); // remove 0 or more bit
      $h = strlen($temp);

      if (substr($temp,0,1) == "0")
        $oc = rtrim($temp,"0"); // delete '0's that mean 2 bits each
      else
      {
        $t = substr($temp,1,1);
        $total .= str_repeat("$bit",bindec($t));
        $temp = substr($temp,2);  //remove the bit from the if above and the $t
        return $this->decomp(($total), $temp); 
      }
      $diff = $h-strlen($oc); // declare $diff
      $temp = $oc; // match up with current $ltrim
      $diff = $diff*2;
      $final_bit = bindec(substr($temp,0,1)); // use up last bit of series
      $total .= str_repeat("$bit",($diff)+$final_bit);// add to $total
      $temp = substr($temp,1); // remove final bit
      return $this->decomp(($total), $temp); // go around again
    }
  }

  public function fd($g, &$z = 0)
  {
    $t = 0;
    while ($g != "")
    {
      $cb = 8 - strlen(decbin(ord($g[0])));
      $t .= decbin(ord($g[0]));
      $t = str_repeat('0',$cb).$t;
      $g = substr($g,1);
    }
    $hex = "";
    //echo "".$t."\n\r";
    $z = 0;
    $r = $t;
    do
    {
      $t = $this->adiff($t, $hex, $z, $r);
    } while (strlen($t) > 8);
    $hex .= chr(bindec($t)%256);
    return $hex;
  }

  public function compress($d)
  {
    $x = "";
    $c = 0;
    
    while ($c < 1)
    {
      $z = 0;
      for (; $d!="" ;)
      {
    // 8 chars at a time
        $x.= $this->fd(substr($d,0,32%(strlen($d)+1)), $z);
        $d = substr($d,32%(strlen($d)+1));
        $d = (strlen($d) > 32)?$d:"";
      }
      $z = bindec($z);
      while ($z > 0)
      {
        $x.=chr($z%256);
        $z >>= 8;
      }
  // reset sources
      $hex = "";
      $d = $x;
      $x = "";
      $c++;
    }
    $s = "";
    return $d;
  }
}

$timea = date_create();
$a = 0;
$output = "temp__";
$zipfile = "out.xiv";
$filename = "pic.png";
$enw = fopen($filename,"r");
$size = filesize($filename);
echo "Input Size: $size\r\n";
    
    $out = null;
    $out = fopen("temp__","w") or die("\n\rCannot open file:  $output\n\r");

    //$a=file_get_contents("enwik9");
    $f = 0;
    $input = 0;
    $x = new Comb();
    $m = 0;

    while ($x->bytes < $size-1)
    {
      $v = date_diff(date_create(),$timea);
      echo round($input/($x->bytes+1)*100,2) . "%   ::    ". round($x->bytes/(filesize($filename)+1)*100 ,2). "%      :: ($input / $x->bytes)      :: " .$v->i.":". ($v->s + $v->f)."\r";
      $enw9 = fread($enw, 1000000%(filesize($filename)));
      $x->output = $x->compress($enw9);
      $x->bytes += strlen($enw9)%(filesize($filename));
      if (FALSE == fwrite($out, $x->output))
      {
          echo "ERROR: CANNOT CONTINUE";
          exit();
      }
      $input += strlen($x->output);
    }
    fclose($out);
    fclose($enw);
    //unlink("tmp");
    $a++;
    echo round($input/$x->bytes*100,2) . "%   ::    ". round($x->bytes/(filesize($filename)+1)*100 ,2). "%      :: ($input / $x->bytes)      :: " .$v->i.":". ($v->s + $v->f)."\r";
      
    rename("$output","tmp");
    $enw = fopen("tmp","r");
    $size = filesize("tmp");
    $filename = "$output";
    $output = "tmp";
    echo "\n\r";

rename("tmp", $zipfile);
echo "Output size: ".filesize($zipfile)."\r\n";

?>