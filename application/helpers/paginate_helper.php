<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('default_pagination_config')) {
    /**
     * Konfigurasi pagination CI3 standar untuk seluruh admin panel.
     * - Pakai query string (?halaman=2), BUKAN URI segment -- supaya konsisten dengan
     *   filter GET yang sudah dipakai di modul lain (Kas, Absensi, dst), dan otomatis
     *   ke-preserve bareng filter itu lewat 'reuse_query_string'.
     * - Markup-nya sudah pakai class Bootstrap 5 (.pagination, .page-item, .page-link)
     *   supaya tidak perlu CSS tambahan selain override warna di app.css.
     *
     * Cara pakai di controller:
     *   $this->load->library('pagination');
     *   $config = default_pagination_config(base_url('transaksi'), $total_rows, 15);
     *   $this->pagination->initialize($config);
     *   $data['pagination_links'] = $this->pagination->create_links();
     */
    function default_pagination_config($base_url, $total_rows, $per_page = 15)
    {
        return array(
            'base_url'             => $base_url,
            'total_rows'           => $total_rows,
            'per_page'             => $per_page,
            'page_query_string'    => true,
            'query_string_segment' => 'halaman',
            'use_page_numbers'     => true,
            'reuse_query_string'   => true,
            'num_links'            => 2,

            'full_tag_open'  => '<nav aria-label="Navigasi halaman"><ul class="pagination pagination-sm mb-0">',
            'full_tag_close' => '</ul></nav>',

            'num_tag_open'  => '<li class="page-item">',
            'num_tag_close' => '</li>',

            'cur_tag_open'  => '<li class="page-item active" aria-current="page"><span class="page-link">',
            'cur_tag_close' => '</span></li>',

            'next_tag_open'  => '<li class="page-item">',
            'next_tag_close' => '</li>',
            'prev_tag_open'  => '<li class="page-item">',
            'prev_tag_close' => '</li>',
            'first_tag_open' => '<li class="page-item">',
            'first_tag_close' => '</li>',
            'last_tag_open'  => '<li class="page-item">',
            'last_tag_close' => '</li>',

            'first_link' => '&laquo;',
            'last_link'  => '&raquo;',
            'next_link'  => 'Berikutnya <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>',
            'prev_link'  => '<i class="fa-solid fa-chevron-left" style="font-size:10px;"></i> Sebelumnya',

            'attributes' => array('class' => 'page-link'),
        );
    }
}