<?php

namespace Mauricius\LaravelHtmx\View;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class BladeFragment
{
    public static function render(string $view, string $fragment, array $data = []): string
    {
        $path = View::make($view, $data)->getPath();

        $content = File::get($path);

        $re = sprintf('/(?<!@)@fragment[ \t]*\([\'"]{1}%s[\'"]{1}\)/s', $fragment);

        preg_match($re, $content, $matches, PREG_OFFSET_CAPTURE);
        throw_if(empty($matches), "No fragment called \"$fragment\" exists in \"$path\"");

        $start = $matches[0][1];

        preg_match_all('/@endfragment/s', $content, $matches, PREG_OFFSET_CAPTURE, $start);

        foreach ($matches[0] as $match) {
            $end = $match[1];
            $sub = substr($content, $start, $end - $start);

            $count_frag = preg_match_all('/(?<!@)@fragment[ \t]*\([\'"]{1}\S*[\'"]{1}\)/s', $sub, $matches);
            $count_end = preg_match_all('/@endfragment/s', $sub, $matches);

            if ($count_frag - 1 - $count_end == 0) {
                break;
            }
        }

        return Blade::render($sub, $data);
    }
}
