<?php

namespace FlSouto;

if(!is_dir(__DIR__.'/tmp_dir/')){
    mkdir(__DIR__.'/tmp_dir/');
}

class Imagick{

    var $file;
    protected static $sequence = 0;

    function __construct($input)
    {

        $id = self::$sequence++;

        $input = self::unwrap($input);

        $ext = explode('.',$input);
        $ext = end($ext);
        $this->file = __DIR__.'/tmp_dir/img'.$id.'.'.$ext;
        copy($input, $this->file);

    }

    static function unwrap($img){
        if($img instanceof self){
            return $img->file;
        }
        return $img;
    }

    function __invoke(){
        return new self($this);
    }

    static function select($path){
        if(is_array($path)){
            shuffle($path);
            $path = current($path);
        }
        $files = glob($path);
        shuffle($files);
        $path = current($files);
        return new self($path);
    }

    function ext(){
        return pathinfo($this->file,PATHINFO_EXTENSION);
    }

    function brightness(){
        return shell_exec('convert '.$this->file.' -colorspace Gray -format "%[fx:image.mean]" info:');
    }

    function size(){
        return explode('x',shell_exec("convert '$this->file' -format \"%wx%h\" info:"));
    }

    function mod($command){
        $out = __DIR__.'/tmp_dir/mod.'.$this->ext();
        shell_exec("convert $command '$this->file' '$out'");
        copy($out, $this->file);
        return $this;
    }
    function hash(){
        return md5(file_get_contents($this->file));
    }
    function add($img, $below=false){
        $cmd = $below ? '-append' : '+append';
        $cmd .= ' '.self::unwrap($img);
        return $this->mod($cmd);
    }

    function pick($pick_w, $pick_h){
        $clone = $this();
        list($w,$h) = $this->size();
        $x_offset = rand(0,$w - $pick_w);
        $y_offset = rand(0,$h - $pick_h);

        return $clone->cut($pick_w, $pick_h, $x_offset, $y_offset);
    }

    function cut($cut_w, $cut_h, $x_offset=0, $y_offset=0){
        return $this->mod("-extract {$cut_w}x{$cut_h}+{$x_offset}+{$y_offset}");
    }

    function flip($invert=false){
        $cmd = $invert ? '+flip' : '-flip';
        return $this->mod($cmd);
    }

    function flop($invert=false){
        $cmd = $invert ? '+flop' : '-flop';
        return $this->mod($cmd);
    }

    function rotate($deg){
        return $this->mod('-rotate '.$deg);
    }

    function mix($img, $fx='blend'){
        // convert orange.png black.png -compose Blend -composite result.png
        $img = self::unwrap($img);
        $out = __DIR__."/tmp_dir/out.jpg";
        if($fx == 'rand'){
            $types = self::mixtypes();
            $fx = $types[array_rand($types)];
        }
        shell_exec("convert '$this->file' '$img' -compose $fx -composite '$out'");
        copy($out, $this->file);
        return $this;
    }

    static function mixtypes(){
        static $types = null;
        if(is_null($types)){
            $types = array_filter(explode("\n",shell_exec('identify -list compose')));
        }
        return $types;
    }

    function colorize($color, $percent=100){
        if($color == 'rand'){
            $color = 'rgb('.implode(',',[rand(0,255),rand(0,255),rand(0,255)]).')';
        }
        $out = __DIR__.'/tmp_dir/tinted.jpg';
        shell_exec("convert '$this->file' -colorspace gray -fill '$color' -tint $percent '$out'");
        copy($out, $this->file);
        return $this;
    }

    function colorspace(){
        return shell_exec("identify -format %[colorspace] '$this->file'");
    }

    function sfactor($set_value=null){
        if(!$set_value){
            $smp = shell_exec("identify -verbose '$this->file' | grep sampling-factor");
            $smp = explode(':',$smp);
            $smp = trim($smp[2]);
            return $smp;
        }
        return $this->mod('-sampling-factor '.$set_value);
    }

    function save($as){
        copy($this->file, $as);
        return $this;
    }

}
