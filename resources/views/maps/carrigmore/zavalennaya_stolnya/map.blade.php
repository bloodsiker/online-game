@include('maps.base.map', [
    'mapTitle' => 'Заваленная Штольня',
    'mapFrameView' => 'maps.carrigmore.zavalennaya_stolnya.frame',
    'mapDataFile' => 'zavalennaya_stolnya.php',
    'mapAreaId' => 1530,
    'mapAreaLabel' => 'Заваленная Штольня',
    'mapPassages' => [
        ['from' => 1530, 'direction' => '→', 'slug' => 'Gx7Qm2Vp9L', 'location' => 1529, 'label' => 'Гранитный Перевал'],
        ['from' => 1581, 'direction' => '←', 'slug' => 'Pd4Kr8Lm2X', 'location' => 1582, 'label' => 'Подскальная дорога'],
    ],
])
