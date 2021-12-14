<?php
/**
 * This is written by A. David Pulse
 * This is fair-use code. You may not make
 * money from its use without express written
 * consent from the author. This will likely
 * mean that you may need a license. This is
 * intelligent property and an algorithmic 
 * math property in the software kind. It
 * was created for the use of anyone. But it is
 * also very much the author's right to hold
 * the patent to himself. Therefore you may
 * contact him at inland14@live.com or
 * baboonxiv@gmail.com to seek your usability
 * usage constraints. Thank you -A. David Pulse
 * 
 * This heading must accompany anywhere the file is used.
 */

// ltrim each line
// use decomp to check answer
class Comb {
  
  public $output = "";
  public $bytes = 0;
  
  public function adiff($ic, &$hex, &$temp, $const)
  {
    // leave a 1 at the end
    $assertion = 0; // This will count the flipping of bits, 1010101 for example
    $trim = substr($ic,0,1);
    $temp1 = "";
    
    // So first we're seeing if the bits are 10101010 or so
    // and how many times it's going to do that in a row.
    // this has always been the reason we could not archive
    // files in this way. It's because noone found a good way
    // to incorporate these bits.
    //$f--; 
    if (substr($ic,0,1) != substr($ic,1,1))
    {               // Once the first flipped bit is reached
      do {
        $assertion++; // Increment counter once for each flipping bit
        $ic = substr($ic, 2); // Make sure to reset to the flipping bit
      } while ($trim == substr($ic,0,1) && substr($ic,0,1) != substr($ic,1,1));
      // Right above, there I made sure the flipping bits didnt flip off
      $temp .= "$trim";
      if ($assertion == 1)
        $temp .= decbin(bindec($trim) ^ 1); // Just goes to show you the flipping bits
      else
      {
        //echo decbin(!($assertion%4)) . " " . decbin(($assertion%4)) . "\n";
        while ($assertion > 0)
        {
          // Again more of the flipping bits. Just dont' get put off, because every 1st of 3 bits is another flipped bit ($trim ^ 1)
          // BTW, You're going to have to use the ! operator to get this right.
          $temp .= str_repeat(decbin(bindec($trim) ^ 1),3-strlen(decbin(!($assertion%4)))) . decbin(!($assertion%4));
          $assertion >>= 2; 
        }
      }
    }
    else if (substr($ic,0,1) == substr($ic,1,1))
    { 
      $h = strlen($ic);
      $oc = ltrim($ic,"$trim");
      $assertion = $h-strlen($oc);
      $ic = $oc;
      $temp .= "$trim";
      if ($assertion == 1)
        $temp .= "$trim";
      else
      {
        while ($assertion > 0)
        {
          $temp .= str_repeat("0",2-strlen(decbin(!($assertion%4)))) . decbin(!($assertion%4));
          $assertion >>= 2; 
        }
      }
    }
    if (strlen($ic) < 1)
    {
      // Here down to while : test decompress
      $temp1 = ($temp);
      $blank = "";
      //$blank = $this->decomp_get_num_bits($temp1, $blank);
      $rev = ($blank);
      //echo $const."\n\r\n\r";
      //echo ($rev)."++++\n\r\n\r";

      // Significant while
      while (strlen($temp) > 0)
      {
        $temp2 = strrev(substr($temp,0,8%(strlen($temp)+1)));
        $hex .= chr(bindec($temp2));
        $temp = (strlen($temp) >= 8)?substr($temp,8%(strlen($temp)+1)):"";
      }
      $hex;
      return $ic;
    }
    return $this->adiff($ic, $hex, $temp, $const);
  }

  public function decomp($total, &$temp, &$blank = "")
  {
    // HERE IS THE CODE FOR MAKING THE DECOMPRESSION
    // $f is ALWAYS AT LEAST 1
    // +1
    if (strlen($temp) < 1) 
      return ($blank); //$total;
    $setting = (substr($temp,0,1)); // Remove the front bit
    $temp = substr($temp,1);
    $bit = intval(substr($temp,0,1));
    $temp = substr($temp,1);
    {
      if ($setting == "1") 
      {
        $s = decbin($bit) . decbin($bit ^ 1);
      }
      else
        $s = $bit;
      $blank .= "\$\$" . $this->decomp_get_num_bits($temp,$s);
      return strrev($blank); // $this->decomp($total, $temp, $blank);
    }
  }

  public function decomp_get_num_bits(&$temp, &$blank = "")
  {
    if (strlen($temp) == 0)
      return $blank;
    $setting = substr($temp,0,1); // Remove the front bit
    $temp = substr($temp,1);
    
    $bit = substr($temp, 0, 1);
    $temp = substr($temp,1);
    $s = "";
    if ($setting == "1") 
    {
      $db = decbin(((bindec($bit) ^ 1) << 1) + (bindec($bit)));
      $s = "$db";
    }
    else
      $s = "$bit";
    
    $temp_len = strlen($temp);
    $temp_trim = ltrim($temp, "0");
    $temp_trim_len = strlen($temp_trim);
    $temp_reps = 0; 
    if ($temp_trim_len != $temp_len)
      $temp_reps = 2 * ($temp_len - $temp_trim_len);

    $trim_left_overs = ltrim($temp_trim,"1");
    $cnt_single_reps = $temp_trim_len - strlen($trim_left_overs) - 1;
    $temp_trim = substr($trim_left_overs,1);
    $temp = $temp_trim;                                                                                                               
    $blank .= str_repeat("$s", ($temp_reps + $cnt_single_reps));
    return $this->decomp_get_num_bits($temp, $blank);
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
    // How many times do we zip?
    while ($c < 2)
    {
      $z = 0;
      for (; $d!="" ;)
      {
    // 8 chars at a time
        $x.= $this->fd(substr($d,0,2000%(strlen($d)+1)), $z);
        $d = substr($d,2000%(strlen($d)+1));
        $d = (strlen($d) > 2000)?$d:"";
      }
      $z = bindec($z);
      while ($z > 0)
      {
        $x.=chr(strrev($z)%256);
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
$filename = "enwik9"; //"../../../Avengers.mp4"; //
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
      $enw9 = fread($enw, 10000%(filesize($filename)));
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
