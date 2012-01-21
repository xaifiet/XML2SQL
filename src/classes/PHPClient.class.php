<?php


class PHPClient
{

    public static function progress($name, $count, $total)
    {
        static $percent = null;
        static $strlen = null;
        static $strlen2 = null;
        $str = $str2 = '';
        $tmp = intval(($count*100)/$total);
        $erase = !is_null($strlen2) ? $strlen2 : 0;
        if (is_null($percent) || is_null($strlen) || $tmp != $percent) {
            $percent = $tmp;
            $erase += !is_null($strlen) ? $strlen : 0;
            $str = $name.': [';
            for ($a = 1; $a <= 50; $a++) {
                $str .= ($a*2) <= $percent ? '*' : '-';
            }
            $str .= '] : '.sprintf('% 3d%%', $percent);
            $strlen = strlen($str);
        }
        for ($a = 0; $a < $erase; $a++) {
            print chr(8);
        }
        $str2 = ' ('.sprintf('% '.strlen($total).'d', $count).' / '.$total.')';
        $strlen2 = strlen($str2);
        print $str;
        print $str2;
        if ($percent == 100) {
            $percent = null;
            $strlen = null;
            print chr(10);
        }
    }


}

?>
