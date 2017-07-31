<?php

return [
    // optional header. Should refer to a blade file.
    'header_view' => env('ED_HEADER_INCLUDE', ''),

    // optional footer. Should refer to a blade file.
    'footer_view' => env('ED_FOOTER_INCLUDE', ''),

    // maximimum avatar size
    'avatar_size' => env('ED_MAX_AVATAR_SIZE', 100),

    // applicable sentence builders
    'required_sentence_builders' => [
        'latin'   => App\Adapters\LatinSentenceBuilder::class,
        'tengwar' => App\Adapters\TengwarSentenceBuilder::class
    ]
];