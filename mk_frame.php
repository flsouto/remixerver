<?php

require_once(__DIR__.'/imagick/Imagick.php');

function gen(){

    $src[]= "frames_in/*.jpg";
    $src[]= "frames_out/*.jpg";

    $img = \FlSouto\Imagick::select($src);
    $final_w = 1280;
    $final_h = 720;

    $pick_w = $final_w / 2;
    $pick_h = $final_h / 2;

    if(rand(0,1)){
        $a = $img->pick($pick_w,$pick_h);
    } else {
        $a = $img->pick($pick_h,$pick_w);
        $a->rotate(rand(0,1) ? 90 : 270);
    }
    if(rand(0,1)){
        $b = $a()->flip(rand(0,1));
    } else {
        $b = $a()->flop(rand(0,1));
    }
    $top = $a->add($b);
    $bottom = $top()->rotate(180);
    $top->add($bottom, 1);
    $top->colorize('rgb('.implode(',',[rand(0,100),rand(0,100),rand(0,100)]).')', 80);
    return $top;
}

$a = gen();
if(rand(0,1)){
    $b = gen();
    $a->mix($b,'blend');
}

$i=1;
while($a->colorspace()=='Gray'){
    $a->colorize('rand',rand(50,100));
    $i++;
    if($i>10){
        return;
    }
}

$a->sfactor('2x2,1x1,1x1');
if(implode('x',$a->size()) != '1280x720'){
    die('dimensions failed');
}
$hash = $a->hash();
$img = "frames_out/$hash.jpg";
$a->save($img);

echo $img;
