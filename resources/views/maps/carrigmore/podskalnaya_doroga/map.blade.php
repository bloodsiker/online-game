@include('maps.base.map', [
    'mapTitle' => 'Подскальная дорога',
    'mapFrameView' => 'maps.carrigmore.podskalnaya_doroga.frame',
    'mapDataFile' => 'podskalnaya_doroga.php',
    'mapAreaId' => 1582,
    'mapAreaLabel' => 'Подскальная дорога',
    'mapPassages' => [
        ['from' => 1582, 'direction' => '→', 'slug' => 'j87Gtf4JU2', 'location' => 1581, 'label' => 'Заваленная Штольня'],
        ['from' => 1592, 'direction' => '←', 'slug' => 'Fd19lMn67Dsq', 'location' => 1593, 'label' => 'Карригмор'],
    ],
])
