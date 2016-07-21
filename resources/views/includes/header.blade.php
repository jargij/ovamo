
<div class="col-md-1"></div>
<div class="col-md-10">
<br><h2>


@if ( isset($title) )
<?php
    $showTitle = parse_url($title, PHP_URL_HOST);
    $showTitle = str_replace('www.', '', $showTitle); 
?>
{{ $date_added }} - <a href="{{ $title }}">{{$vacature_sites_lijst_id}} - {{ $showTitle }}</a>
@endif

@if ( isset($status) )
 - {{$status}}
@endif

</h2>

<a href="http://localhost:9999/action/previousschool">previousschool</a> - 
<a href="http://localhost:9999/action/nextschool">nextschool</a> | 
<a href="http://localhost:9999/action/previousdate">previousdate</a> - 
<a href="http://localhost:9999/action/nextdate">nextdate</a>

<hr>
</div>
<div class="col-md-1"><h1><a href="/info">info</a></h1></div>