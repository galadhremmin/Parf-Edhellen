<?php

// The welcome checklist on a new member's own profile; its structure is in `ed.welcome`.
return [
    'steps' => [
        'name' => [
            'title' => 'Choose your name',
            'text' => 'Right now you\'re ":nickname". Pick the name you\'d like to be known by.',
            'action' => 'Choose a name',
        ],
        'avatar' => [
            'title' => 'Add a picture',
            'text' => 'So people recognise you in discussions and next to your contributions.',
            'action' => 'Choose a picture',
        ],
        'introduction' => [
            'title' => 'Introduce yourself',
            'text' => 'A few words about you: the languages you\'re learning, or what brought you here.',
            'action' => 'Write an introduction',
        ],
        'background' => [
            'title' => 'Choose a background',
            'text' => 'A picture across the top of your profile, from our library or your own.',
            'action' => 'Choose a background',
        ],
        'contribution' => [
            'title' => 'Contribute a word',
            'text' => 'Know a word that\'s missing, or a better translation? Suggest it, and a reviewer will take a look.',
            'action' => 'Make a contribution',
        ],
        'discuss' => [
            'title' => 'Say hello',
            'text' => 'Introduce yourself in Discuss, or ask the question you came with.',
            'action' => 'Go to Discuss',
        ],
    ],
];
