<?php
// ltrim each line
// use decomp to check answer
class Comb {
  
  public $current = "";
  public $bytes = 0;

  public function adiff($ic, &$hex, &$temp, $const)
  {
    // leave a 1 at the end
    if (strlen($ic) < 1)
      return $ic;
    
    $h = strlen($ic);
    $trim = substr($ic,0,1);
    $oc = ltrim($ic,"$trim");
    $f = $h-strlen($oc);

    // $f is ALWAYS AT LEAST 1
    // +1
    $f--;
    // WHAT BIT ARE WE USING
    //if ($temp == "")
    $temp .= "$trim";
    $t = ($f == 0) ? "0" : "1";
    $temp .= $t;// . $temp;
    if ($f == 0)
      goto SPM; // (Specially Placed Marker) $f == 0
    if ($f > 0)
    {
      $temp .= str_repeat("0", 3-strlen(decbin($f%8))).decbin($f%8);// . $temp;
      // +3 bits to gauge distance between 0->1
      $f -= $f%8; // delete from 8 bits
      //$f >>= 3; // bitwise right 3 (4 bit)
      $t = ($f == 0) ? "0" : "1"; // are we yet at 0
      $temp .= $t;// . $temp;
      goto SPM; // we found $f == 0 so goto SPM
    }
    while ($f > 1)
    {
      if ($f >= 2)
        $temp .= "0"; // each taking off 2 bits
      $f -= 2;
    }
    $temp .= "1" . decbin($f); // '1' to close off, and the final 0 or 1 bits the line is
    SPM:
    if (strlen($oc) < 4)
    {
      // Here to while : test decompress
      $temp1 = ($temp);
      $blank = "";
      $blank = $this->decomp($blank,$temp1);
      $rev = ($blank);
      echo $const."\n\r";
      echo ($rev).$oc."++++\n\r";

      while (strlen($temp) > 0)
      {
        $temp2 = substr($temp,0,8%(strlen($temp)+1));
        $hex .= chr(bindec($temp2));
        $temp = (strlen($temp) >= 8)?substr($temp,8%(strlen($temp)+1)):"";
      }
    }
    return ($oc);
  }

  public function decomp($total, &$temp)
  {
    // HERE IS THE CODE FOR MAKING THE DECOMPRESSION
    // $f is ALWAYS AT LEAST 1
    // +1
    $bit = substr($temp,0,1);
    if (strlen($temp) <= 1)
      return// str_repeat("$bit",abs(strlen($total)-16));
      $total;
    $bit = substr($temp,0,1); //
    $total .= $bit; //"$bit";
    $temp = substr($temp,1);
    // Next bit is the $f >= 0 bit ('>' == 1)
    if (substr($temp,0,1) == "0")
    {
      $temp = substr($temp,1); // >= 0
      return $this->decomp(($total), $temp);
    }
    else
    {
      $temp = substr($temp,1); // >= 0
      $bitLength = substr($temp,0,3); // This is the 3 bits of length
      $number = bindec(strrev($bitLength)); // this turns into a number
      $total .= str_repeat("$bit",$number); // we repeat that number
      $temp = substr($temp,3); // remove these three bits
      if (substr($temp,0,1) == "1") // is there more? (y == 1)
        goto fin; // go get the rest
      $temp = substr($temp,1);  //remove the bit from the if above
      return $this->decomp(($total), $temp);
    }
    fin:
    {
      $temp = substr($temp,1); // remove 0 or more bit
      $ltrim = ltrim($temp,"0"); // delete '0's that mean 2 bits
      $diff = strlen($temp) - (strlen($ltrim) - 1); // construct diff
      $temp = $ltrim; // match up with current $ltrim
      $temp = substr($temp,1); // remove difference
      $final_bit = bindec(substr($temp,0,1)); // use up last bit of series
      $total .= str_repeat("$bit",($diff*2)+$final_bit);// add to $total
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
    // echo strlen($d) . "<br>";
    

    while ($c < 1)
    {
      $z = 0;
      for (; $d!="" ;)
      {
    // 8 chars at a time
        $x.= $this->fd(substr($d,0,2%(strlen($d)+1)), $z);
        $d = substr($d,2%(strlen($d)+1));
        $d = (strlen($d) > 2)?$d:"";
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