<?php

return [
    'nama'   => 'Boyolali',
    'contoh' => 'Mojosongo, Boyolali',   // contoh pengisian kolom "Area"
    'pusat'  => [-7.5329, 110.5969],     // titik tengah peta (kota Boyolali)
    'zoom'   => 11,

    // Kotak batas (perkiraan Kabupaten Boyolali). Titik di luar kotak ditolak.
    'batas' => [
        'lat_min' => -7.62,
        'lat_max' => -7.10,
        'lng_min' => 110.35,
        'lng_max' => 110.84,

        'geojson' => public_path('geojson/wilayah.geojson'),
    ],
];