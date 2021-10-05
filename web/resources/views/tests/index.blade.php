<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>test pages</title>
    <style type="text/css">
        li{list-style-type: none}
        li.h2{list-style-type: circle;font-size:15pt;padding-top:12px;padding-bottom:8px;font-weight: bold;background:#f5f5f5}
        li.h3{font-size:12pt;padding-top:8px;padding-bottom:4px;;font-weight: bold; }
        li.space{height:10px}
    </style>
</head>
<body>

<h1>Test pages </h1>

<ul>
    @foreach ($urls as $url)
            @if (preg_match('/^h2(.+)/',$url,$m))
                <li class="h2">{{ $m[1] }}</li>
            @elseif (preg_match('/^h3(.+)/',$url,$m))
                <li class="h3">{{ $m[1] }}</li>
            @elseif ($url == '-')
                <li class="space"></li>
            @else
                <li><a href="{{ $url }}">{{ $url }}</a></li>
            @endif
    @endforeach
</ul>

</body>
