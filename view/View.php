<?php

class View
{

    public static function makeHtml($data, $template){
        //replace in the template the {{ ... }} by the values
        return str_replace(array_keys($data), $data, file_get_contents("template/$template"));
    }

    public static function makeLoopHtml($data, $template){
        $html = "";
        foreach ($data as $value) {
            $html .= self::makeHtml($value, $template);
        }
        return $html;
    }

    // (No need)
    // public static function addTitleHtml(int $size, $title){
    //     if ($size >= 1 && $size <= 6){
    //         return "<h".$size.">".$title."</h".$size.">";
    //     }
    // }

    // public static function addDiv($place, $class=NULL){
    //     if ($place == "start"){
    //         return "<div class='".$class."'>";
    //     }
    //     elseif ($place == "end"){
    //         return "</div>";
    //     }
    // }

    // public static function addHtmlTag($tag, $place){
    //     if ($place == "start"){
    //         return "<".$tag.">";
    //     }
    //     elseif ($place == "end"){
    //         return "</".$tag.">";
    //     }
    // }

}
