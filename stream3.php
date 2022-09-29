<?php

$last_frame = null;
$last_mp3 = null;

while(true) {
    $mp3 = trim(shell_exec("ls loops_out/*.mp3 -t | head -n 1"));
    if($mp3 == $last_mp3 && $last_frame && file_exists($last_frame)){
        $frame = $last_frame;
    } else {
        $frames = trim(shell_exec("ls frames_out/*.jpg -t | head -n 100"));
        $frames = explode("\n", $frames);
        srand(crc32($mp3));
        $frame = $frames[array_rand($frames)];
    }
    $last_frame = $frame;
    $last_mp3 = $mp3;
    echo $mp3;
    shell_exec($cmd='
    ffmpeg -y \
      -loop 1 -framerate 3 -i '.$frame.' \
      -i '.$mp3.' -t $(soxi -D '.$mp3.') \
      -c:v libx264 -preset ultrafast -pix_fmt yuv420p -minrate 6000k -maxrate 6000k -bufsize 12000k -b:v 6000k \
      -r 25 -g 30 -keyint_min 60 -x264opts "keyint=60:min-keyint=60:no-scenecut" \
      -s 1200x720 -tune zerolatency \
      -b:a 128k -c:a copy  \
      -strict experimental \
      -f flv rtmp://a.rtmp.youtube.com/live2/d88u-1r0e-dyz7-gxyh-78yr
    ');
    sleep(1);
}
