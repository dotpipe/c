<?php
// ltrim each line
// use decomp to check answer
class Comb {
  
  public $current = "";
  public $bytes = 0;
  
  public function adiff($ic, &$hex, &$temp, $const)
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

    $f--;
    if ($trim != substr($ic,0,1) && $f == 0) { // Once the flipping bits are reached
      $assertion++; // Increment counter once for each flipping bit
      $ic = $oc; // Make sure to recount the one taken off by the flipping bit
      goto start;

    }
    else if ($f == 0) { // Start a new order of flipping bits
      $assertion++; // Increment for the first flipping bit
      $ic = $oc; // Reset the $ic variable because of the flipping bit
      goto start;
    }
    else if ($assertion > 0) // if we have already seenthe flipping bits
    {
      $temp .= "1"; // Make a assertion that we have been using the flipping bit technique
      $f = $assertion; // turn $assertion over to $f which is the count of flipped off and on again bits
      //$ic = $oc; // Questionable line here. Have we flipped them enough?
      $assertion = 0; // reset $assertion to count again next time. Kinda pointless with it not by reference
    }
    else // If there were no assertions, and there were no flipping bits to handle, than we carry on with "0"
      $temp .= "0"; // This tells the file that we are going straight thru with a series of bits.
    $temp .= ($f == 2) ? "00" : ($f >= 3) ? "01" : "1"; // The final end here is accounting to $f == 1
    $f -= $f%4;// now remove the MOD 4 from this
    $f >>= 2; // Make it easy to get a lower number next time
    if ($f == 0) // Check to see if we're done
      goto done; // Goto clean up at done
      {
        $temp .= ($f > 3) ? "1" : "0";  // Is $f 2 or 3 bits long?
        if ($f > 3) // 3 flippin bits
          $temp .= str_repeat("0",3-strlen(decbin($f%8))) . decbin($f%8);
        else // 2 flippin bits
          $temp .= str_repeat("0",2-strlen(decbin($f%4))) . decbin($f%4);
        $f -= $f%8; // remove the annotated bits flipped here
        $f >>= 3; // Scour the bits off now
        if ($f == 0) // are we set?
          goto done; // yes
      }
      {
        $temp .= ($f > 3) ? "1" : "0";  // Is $f 2 or 3 bits long?
        if ($f > 3) // 3 flippin bits
          $temp .= str_repeat("0",3-strlen(decbin($f%8))) . decbin($f%8);
        else // 2 flippin bits
          $temp .= str_repeat("0",2-strlen(decbin($f%4))) . decbin($f%4);
        $f -= $f%8; // remove the annotated bits flipped here
        $f >>= 3; // Scour the bits off now
      }
    echo ($f > 0);
    done:
    if (strlen($oc) < 1)
    {
      // Here down to while : test decompress
      $temp1 = ($temp);
      $blank = "";
      //$blank = $this->decomp($blank,$temp1, bindec($trim));
      $rev = ($blank);
      //echo $const."\n\r";
      //echo ($rev).$oc."++++\n\r";

      // Significant while
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

  public function decomp($total, &$temp, $bit = 0)
  {
    // HERE IS THE CODE FOR MAKING THE DECOMPRESSION
    // $f is ALWAYS AT LEAST 1
    // +1
    if (strlen($temp) < 1)
      return $total;
    // This is the actual bit were using (line 24)
    $total .= "$bit";
    $SPMB = bindec(substr($temp,0,1)); // Remove the front bit
    {
      $temp = substr($temp,1); // line 39/44 ($SPMB) 
      $number = bindec(substr($temp,0,2)); // line 46
      
      $temp = substr($temp, 2); // line 46
      
      if (substr($temp,0,1) == "1") { // this was only 3 or less for bits
        
        $temp = substr($temp,1); // remove if checked "1"
        $a = $bit ^ 1; // Use current bit
        $b = $a ^ 1; // flippin bits takes off the edge of single bits
        $c = 0; // count to $number
        while ($number > $c)
        {
          $total .= str_repeat("$a$b",$number);
          $c += 2;
        }
        if ($c - $number == -1)
          $total .= "$a";
      } 
      else if (substr($temp,0,1) == "0")
      { 
        $temp = substr($temp,1);
        $temp_reps = strlen($temp) - strlen(ltrim($temp,"0"));
        $temp_reps <<= 2;
        $temp_reps += ($number); // Take the difference
        $temp = ltrim($temp,"0");
        $temp = substr($temp,1);
        $total .= str_repeat("$bit",$temp_reps);
      }
      return $this->decomp($total, $temp, ($bit ^ 1));
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
    
    while ($c < 2)
    {
      $z = 0;
      for (; $d!="" ;)
      {
    // 8 chars at a time
        $x.= $this->fd(substr($d,0,64%(strlen($d)+1)), $z);
        $d = substr($d,64%(strlen($d)+1));
        $d = (strlen($d) > 64)?$d:"";
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