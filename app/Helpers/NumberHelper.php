<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Convert number to Indonesian words
     */
    public static function terbilang($x)
    {
        $angka = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        
        if ($x < 12)
            return " " . $angka[$x];
        elseif ($x < 20)
            return self::terbilang($x - 10) . " belas";
        elseif ($x < 100)
            return self::terbilang($x / 10) . " puluh" . self::terbilang($x % 10);
        elseif ($x < 200)
            return "seratus" . self::terbilang($x - 100);
        elseif ($x < 1000)
            return self::terbilang($x / 100) . " ratus" . self::terbilang($x % 100);
        elseif ($x < 2000)
            return "seribu" . self::terbilang($x - 1000);
        elseif ($x < 1000000)
            return self::terbilang($x / 1000) . " ribu" . self::terbilang($x % 1000);
        elseif ($x < 1000000000)
            return self::terbilang($x / 1000000) . " juta" . self::terbilang($x % 1000000);
        elseif ($x < 1000000000000)
            return self::terbilang($x / 1000000000) . " milyar" . self::terbilang($x % 1000000000);
    }
}
