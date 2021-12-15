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
  public $crush_left = 0;
  public $input = 0;
  public $v;

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
      $trim_opp = decbin(bindec($trim) ^ 1);
      $temp .= "$trim";
      $assertion--;
      if ($assertion == 0)
        $temp .= $trim_opp;
      else
      {
        while ($assertion > 0)
        {
          $temp .= str_repeat($trim_opp,strlen(decbin($assertion))); //2-strlen((decbin(($assertion%4))))) . (decbin(($assertion%4))));
          $assertion >>= 2;
        }
        $temp .= "$trim";
      }
      //$temp .= (str_repeat($trim_opp,2-strlen(strrev(decbin(!($assertion%4))))) . strrev(decbin(!($assertion%4))));
    }
    else if (substr($ic,0,1) == substr($ic,1,1))
    {              // Once the first flipped bit is reached
      do {
        $assertion++; // Increment counter once for each flipping bit
        $ic = substr($ic, 2); // Make sure to reset to the flipping bit
      } while ($trim == substr($ic,0,1) && substr($ic,0,1) == substr($ic,1,1));
      $trim_opp = decbin(bindec($trim) ^ 1);
      $temp .= "$trim";
      $assertion--;
      if ($assertion == 0)
        $temp .= "$trim";
      else
      {
        $trims = "";
        while ($assertion > 0)
        {
          $temp .= str_repeat($trim,strlen(decbin($assertion)));//$temp .= (str_repeat($trim,2-strlen((decbin(($assertion%4))))) . (decbin(($assertion%4))));
          $assertion >>= 2; 
        }
        $temp .= "$trim_opp";
      }
      
      //$temp .= (str_repeat($trim_opp,2-strlen(strrev(decbin(!($assertion%4))))) . strrev(decbin(!($assertion%4))));
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
        $temp2 = (substr($temp,0,8%(strlen($temp)+1)));
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
    return $hex;
  }

  public function compress($d)
  {
    $x = "";
    $c = 0;
    // How many times do we zip?
    while ($c < 1)
    {
      $z = 0;
      for (; $d!="" ;)
      {
    // 8 chars at a time
        $x .= $this->fd(substr($d,0,1000), $z);
        $d = (strlen($d) >= 1000)?$d:"";
        $d = substr($d,1000%(strlen($d)+1));
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

  public function crush(string $filename, string $zipfile, int $zipcnt = 1, string $input_name = "")
  {
    $timea = date_create();
    $a = 0;

    if ($zipcnt == 0 && $input_name != "")
    {
      
      echo round($this->input/(filesize($input_name)+1)*100 ,2) . "%   ::    ". round($this->bytes/(filesize($filename))*100 ,2). "%      :: ($this->input / " . filesize($input_name) . ")      :: " .$this->v->i.":". ($this->v->s + $this->v->f)."\r";
      rename("$zipcnt", $zipfile);
      echo "\r\nOutput size: ".filesize($zipfile)."\r\n";
      return;
    }
    $this->input = 0;
    if ($zipcnt <= 0)
      exit("Zip Count must be greater than 0.");
    
    if (strlen($filename) == 0 || strlen($zipfile) == 0)
      exit("Filename invalid");

    $enw = fopen($filename,"r");
    if (file_exists($zipcnt))
    {
      fclose($enw);
      $enw = fopen($zipcnt, "r");
    }

    $size = 0; 
    if ($input_name == "") {
      $input_name = $filename;
      echo "Input Size: " . filesize($input_name) . "\r\n";
      $size = (file_exists($zipcnt)) ? filesize($zipcnt) : filesize($input_name);
      $this->v = date_diff(date_create(),$timea);
    }
    //echo $size."\r\n";
    $out = null;
    $out = fopen("tmp","w");
    
    {
      $f = 0;
      $this->input = 0;
      $m = 0;
      $this->bytes = 0;
      $this->v = date_diff(date_create(),$timea);
      while ($this->bytes < filesize($filename))
      {
        $this->v = date_diff(date_create(),$timea);
        echo round($this->input/($this->bytes + 1)*100 ,2) . "%   ::    ". round($this->bytes/(filesize($input_name)+1)*100 ,2). "%      :: ($this->input / " . filesize($input_name) . ")      :: " .$this->v->i.":". ($this->v->s + $this->v->f)."\r";
        $enw9 = fread($enw, 10000%(filesize($filename)+1));
        $this->output = $this->compress($enw9);
        $this->bytes += strlen($enw9);
        if (FALSE == fwrite($out, $this->output))
        {
            echo "ERROR: CANNOT CONTINUE";
            exit();
        }
        $this->input += strlen($this->output);
      }
      fclose($out);
      fclose($enw);
      
      $a++;
      rename("tmp",($zipcnt-1));
      
    }
    return $this->crush($zipcnt-1, $zipfile, $zipcnt-1, $input_name);
  }
}

$x = new Comb();
// Arguments: Input, Output, Zip iterations
$x->crush($argv[1], $argv[2], $argv[3]);
?>
