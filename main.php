<?php

require __DIR__.'/vendor/autoload.php';

use App\PetriNetwork;

$data = json_decode(file_get_contents('input.json'), true);

$petriNetwork = new PetriNetwork($data);

$petriNetwork->run();
