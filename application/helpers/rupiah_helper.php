<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('rupiah')) {
    /**
     * Format angka menjadi format Rupiah, mis: 15000 -> Rp 15.000
     */
    function rupiah($angka, $prefix = 'Rp ')
    {
        return $prefix . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('angka')) {
    /**
     * Format angka dengan pemisah ribuan tanpa prefix Rp, mis: 15000 -> 15.000
     */
    function angka($angka, $desimal = 0)
    {
        return number_format((float) $angka, $desimal, ',', '.');
    }
}
