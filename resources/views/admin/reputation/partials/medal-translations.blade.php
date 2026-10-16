@include('admin.partials.content-translations', [
    'prefix' => $prefix,
    'content' => $content,
    'fields' => [
        'medal_name' => ['label' => 'Название медали'],
        'feat_medal_name' => ['label' => 'Название медали за подвиг'],
        'feat_description' => ['label' => 'Описание подвига', 'multiline' => true],
    ],
])
