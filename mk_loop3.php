<?php
require_once('Sampler.php');
require_once('fx.php');

use FlSouto\Sampler;
while(true){

    shell_exec("rm sampler/FlSouto/tmp_dir/*.wav 2>/dev/null");
    shell_exec("rm $(ls loops_out/*.mp3 -t | tail -n 1)");
    shell_exec("rm $(ls loops_out/*.wav -t | tail -n 1)");

    $l1 = Sampler::select(["loops_in/*.wav","loops_out/*.wav"]);
    $l2 = Sampler::select(["loops_in/*.wav","loops_out/*.wav"]);
    $l1 = $l1->copy(0,'1/4');
    $l2 = $l2->copy(0,'1/4');
    maybe_apply_fx($l1);
    maybe_apply_fx($l2);

    $len = ($l1->len() + $l2->len()) / 2;

    $l1->resize($len);
    $l2->resize($len);
    $l1->fade(0,-rand(15,20));
    $l2->fade(-rand(15,20),0);
    try{
        $loop = $l1()->mix($l2,false);
    } catch(\Exception $e){
        $loop = $l1()->mix($l2,true);
    }

    $loop->x(4);
    $loop->maxgain();
    $hash = $loop->hash();
    $loop->save("loops_out/$hash.wav");
    $loop->save("loops_out/$hash.mp3");
    echo $hash."\n";
    sleep(10);
}
