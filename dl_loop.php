<?php

$gids = require(__DIR__."/meta_gids.php");
$gid = $gids[array_rand($gids)];

$curl = curl_init($url="https://drive.google.com/uc?export=download&id=".$gid);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
$meta = curl_exec($curl);

preg_match('/"gid":"(.*?)"/', $meta, $m);

if(empty($m[1])){
    file_put_contents(__DIR__."/errors","MP3 gid not found when parsing contents of $url\n",FILE_APPEND);
    die();
}

$mp3_gid = $m[1];

$curl = curl_init("https://drive.google.com/uc?export=download&id=".$mp3_gid);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
$mp3 = curl_exec($curl);

if(empty($mp3)){
    echo "Empty mp3 file downloaded from $url";
    die();
}

file_put_contents("/tmp/downloaded.mp3", $mp3);
$dest = "loops_in/$mp3_gid.wav";
shell_exec("sox /tmp/downloaded.mp3 $dest");

echo $dest."\n";

