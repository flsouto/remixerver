<?php

$loop_fx = [
    'reverb' => function($loop){
        $loop->mod('reverb');
    },
    'reverse' => function($loop){
        $loop->mod('reverse');
    },
    'oops' => function($loop){
        $loop->mod('oops');
    },
    'pitch' => function($loop){
        $loop->mod('pitch '.mt_rand(-50,50));
    },
    'chop' => function($loop){
        $loop->chop(array_rand([16=>'',8=>'',4=>'',2=>'']));
    },
    'tremolo' => function($loop){
        $loop->mod("tremolo ".rand(4,9)." ".rand(40,90));
    },
    'hlpass' => function($loop){
        if(mt_rand(0,1)){
            $loop->mod('highpass '.mt_rand(1000,4000));
        } else {
            $loop->mod('lowpass '.mt_rand(1000,4000));
        }
    }
];
function maybe_apply_fx($loop){
    if(rand(0,1)) apply_fx($loop);
}
function apply_fx($loop){
    global $loop_fx;
    $num_fx = mt_rand(0,count($loop_fx));
    $fx_arr = array_values($loop_fx);
    shuffle($fx_arr);
    $fx_arr = array_slice($fx_arr, 0, $num_fx);
    foreach($fx_arr as $fx){
        $fx($loop);
    }
}
