<?php

$sconfig=[
    'ml_subscribe'=>[
        'description'=>'FormIt hook for subscribe',
    ],
    'ml_unsubscribe'=>[
        'description'=>'Snippet for unsubscribe',
    ],
];

foreach($sconfig?:[] as $snippet=>$options){
    $snippet_file=$config['component']['core'].'elements/snippets/'.$snippet.'.php';
    if(!file_exists($snippet_file))continue;
    $data['modSnippet'][$snippet]=[
        'fields'=>[
            'name' => $snippet,
            'description' => $options['description'],
            'snippet' => trim(str_replace(['<?php', '?>'], '', file_get_contents($snippet_file))),
            'source' => 1,
        ],
        'options'=>[
            'search_by'=>['name'],
        ],
        'relations'=>[
            'modCategory'=>[
                'main'=>'Snippets'
            ]
        ]
    ];
}