<?php
require_once('Sampler.php');
require_once('fx.php');

use FlSouto\Sampler;

$l1 = Sampler::select("loops_in/*.wav");
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

$loop->maxgain();

$fps = rand(10,32);
$amps = [];

$loop->each(1/$fps,function($s) use(&$amps){
    $amp = $s->amp();
    $amps[] = $amp;
    echo $amp.PHP_EOL;
});

$amps = [...$amps, ...$amps, ...$amps, ...$amps];
$i = 0;
shell_exec("rm frames_tmp/* 2>/dev/null");
$frames_out = glob("frames_out/*.jpg");
shuffle($frames_out);
$frames_out = array_slice($frames_out, 4, rand(4,15));
foreach($amps as $a){
    $contrast = -30 + ($a * 100);
    $frame = str_pad("$i",3,"0",STR_PAD_LEFT);
    echo $frame.PHP_EOL;
    $img = $frames_out[array_rand($frames_out)];
    shell_exec("convert -brightness-contrast $contrast '$img' frames_tmp/$frame.jpg");
    $i++;
}
$loop->x(4);

$hash = $loop->hash();
$loop->save("loops_out/$hash.wav");
$loop->save('out.mp3');

shell_exec("ffmpeg -y -r $fps -i frames_tmp/%03d.jpg -c:v libx264 -pix_fmt yuv420p -crf 23 -r $fps -y /tmp/frames.mp4");
shell_exec("ffmpeg -y -i /tmp/frames.mp4 -i out.mp3 -c copy -map 0:v:0 -map 1:a:0 videos_out/$hash.mp4");
echo "$hash.mp4\n";


