<?php
/**
 * This is written by A. David Pulse
 * This is fair-use code. You may not make
 * money from its use without express written
 * consent from the author. This will likely
 * mean that you may need a license. This is
 * intelligent property and an algorithmic 
 * math property of the software kind. It
 * was created for the use of anyone. But it is
 * also very much the author's right to hold
 * the patent to himself. Therefore you may
 * contact him at inland14@live.com and seek
 * usage constraints. Thank you -the author 
 */

// ltrim each line
// use decomp to check answer
class Comb {
  
  public $output = "";
  public $bytes = 0;
  
  public function adiff($ic, &$hex, &$temp, $const, $bit)
  {
    // leave a 1 at the end
    $assertion = 0; // This will count the flipping of bits, 1010101 for example
    start:
    if (strlen($ic) < 1)
      return $ic;
    $h = strlen($ic);
    $trim = substr($ic,0,1);
    $oc = ltrim($ic,"$trim");
    $f = $h-strlen($oc);

    // So first we're seeing if the bits are 10101010 or so
    // and how many times it's going to do that in a row.
    // this has always been the reason we could not archive
    // files in this way. It's because noone found a good way
    // to incorporate these bits.
    //$f--;
    if ($f == 1) { // Once the first flipped bit is reached
      $assertion++; // Increment counter once for each flipping bit
      $ic = $oc; // Make sure to reset to the flipping bit
      goto start; // restart
    }
    if ($assertion > 0) // if we have already seen the flipping bits
    {
      $temp .= "1"; // Make an assertion that we have been using the flipping bit technique
                    // This is aligned with the bit at line #56
      $f = $assertion; // turn $assertion over to $f which is the count of flipped off and on again bits
      //$ic = $oc; // Questionable line here. Have we flipped them enough?
      $assertion = 0; // reset $assertion to count again next time. Kinda pointless with it not by reference
    }
    else // If there were no assertions, and there were no flipping bits to handle, than we carry on with "0"
      $temp .= "0"; // This tells the file that we are going straight thru with a series of bits.
                    // this is aligned with the "1" bit at line #49
    $temp .= ($f == 2) ? "00" : ($f >= 3) ? "01" : "1"; // The "1" here makes $f == 1
    // So we remove 2 bits from the line
    if ($f >= 3) // 3+ flippin bits
      $temp .= str_repeat("0",2-strlen(decbin($f%4))) . decbin($f%4); // removed up to 3
    $f -= $f%4;// now, remove the MOD 4 from this
    $f >>= 2; // bitshift right twice to reveal the next bits // removed up to 3
    if ($f == 0) // are we set?
    {
      goto done; // yes
    }
    {
      $temp .= ($f == 2) ? "00" : ($f >= 3) ? "01" : "1";// counting the number of flippin bits left to be handled
      if ($f >= 3) // 3+ flippin bits
        $temp .= str_repeat("0",2-strlen(decbin($f%4))) . decbin($f%4); // removed up to 3
      $f -= $f%4; // remove the annotated bits flipped here
      $f >>= 2; // Scour the bits off now
      if ($f == 0) // are we set?
      {
        goto done; // yes
      }
    }
    {
      $temp .= ($f == 2) ? "00" : ($f >= 3) ? "01" : "1";
      if ($f >= 3) // 3+ flippin bits
        $temp .= str_repeat("0",2-strlen(decbin($f%4))) . decbin($f%4); // removed up to 3
      $f -= $f%4; // remove the annotated bits flipped here
      $f >>= 2; // Scour the bits off now
      if ($f == 0) // are we set?
      {
        goto done; // yes
      }
    }
    {
      $temp .= ($f == 2) ? "00" : ($f >= 3) ? "01" : "1";
      if ($f >= 3) // 3 flippin bits
        $temp .= str_repeat("0",2-strlen(decbin($f%4))) . decbin($f%4); // removed up 3
      $f -= $f%4; // remove the annotated bits flipped here
      $f >>= 2; // Scour the bits off now
    }
    // This below has done perfect for ridding the rest of the bits
    // I do not suspect the bits to be more than a number, 12 bits long.
    {
      $temp .= ($f == 2) ? "00" : ($f >= 3) ? "01" : "1";
//      $temp .= ($f > 3) ? "1" : "0";  // Is $f 2 or 3 bits long?
      if ($f >= 3) // 3 flippin bits
        $temp .= str_repeat("0",2-strlen(decbin($f%4))) . decbin($f%4); // removed up 3
      $f -= $f%4; // remove the annotated bits flipped here
      $f >>= 2; // Scour the bits off now
    }
    echo ($f > 0) ? "\n" : "";
    done:
    if (strlen($oc) < 1)
    {
      // Here down to while : test decompress
      $temp1 = ($temp);
      $blank = "";
      $sub = "";
      $blank = $this->decomp($blank,$temp1, boolval($bit), $sub);
      $rev = ($blank);
      echo $const."\n\r\n\r";
      echo ($rev)."++++\n\r";

      // Significant while
      while (strlen($temp) > 0)
      {
        $temp2 = substr($temp,0,8%(strlen($temp)+1));
        $hex .= chr(bindec($temp2));
        $temp = (strlen($temp) >= 8)?substr($temp,8%(strlen($temp)+1)):"";
      }
      return $oc;
    }
    return $this->adiff($oc, $hex, $temp, $const, $bit);
  }

  public function decomp($total, &$temp, $bit, $sub)
  {
    // HERE IS THE CODE FOR MAKING THE DECOMPRESSION
    // $f is ALWAYS AT LEAST 1
    // +1
    if (strlen($temp) < 1)
      return $sub; //$total;
    $setting = bindec(substr($temp,0,1)); // Remove the front bit
    {
      $temp = substr($temp,1); // line 39/44 ($SPMB) 
      //$number = bindec(substr($temp,0,2)); // line 46
      
      //$temp = substr($temp, 2); // line 46
      {
        if (substr($temp,0,2) == "00")
        {
          if ($setting == 0)
            //$total .= 
            $sub .= " $bit" . (($bit) ^ 1);
          else
            //$total .= 
            $sub .= " $bit$bit";
          $temp = substr($temp,2);
        }
        else if (substr($temp,0,2) == "01")
        {
          //$total .=
          $stub = "$bit" . (($bit) ^ 1);
          if ($setting == 0)
            $sub .= " " . $this->decomp_get_num_bits($temp,$bit);
          else
            $sub .= " " . $this->decomp_get_num_bits($temp, ($stub));
        }
        else if (substr($temp,0,1) == "1")
        {
          //$total .= 
          $sub .= " $bit";
          //$bit = ($bit ^ 1);
          $temp = substr($temp,1);
        }
        $bit = ($bit ^ 1);
      }
      return $this->decomp($total, $temp, ($bit), $sub);
    }
  }

  public function decomp_get_num_bits(&$temp, $bit)
  {
    $temp_reps = 0;
    
    if (substr($temp,0,2) == "00")
    {
      $temp_reps += 2;
      $temp = substr($temp, 2);
    }
    else if (substr($temp,0,1) == "1")
    {
      $temp_reps += 1;
      $temp = substr($temp, 1);
    }
    while (substr($temp,0,2) == "01")
    {
      $temp = substr($temp,2);
      $temp_reps <<= 2;
      $temp_reps += bindec(substr($temp,0,2));
      $temp = substr($temp,2);
    }

    return str_repeat("$bit",$temp_reps);
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
      $t = $this->adiff($t, $hex, $z, $r, bindec(substr(decbin(ord($r[0])),0,1)));
    } while (strlen($t) > 8);
    $hex .= chr(bindec($t)%256);
    return $hex;
  }

  public function compress($d)
  {
    $x = "";
    $c = 0;
    while ($c < 2)
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
$zipfile = "output.xiv";
$filename = "enwik9";
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
      $enw9 = fread($enw, 300000%(filesize($filename)));
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