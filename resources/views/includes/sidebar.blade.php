    <nav id="sidebar-nav">
        <ul class="nav nav-pills nav-stacked">
   
	    @if ( isset($vacatureWebsitesLijst) )
		@foreach ($vacatureWebsitesLijst as $vacatureWebsites)

			<?php 
			$url = URL::to('/vacaturesites/'.$vacatureWebsites['id'].'/'.$date_added);
			?>


			
			<!--<li>{{ $vacatureWebsites['id'] }} - {{ $vacatureWebsites['menu_url'] }}</li>
			<li><a href="/vacaturesites/{{ $vacatureWebsites['id'] }}/{{ $date_added }}">{{ $vacatureWebsites['id'] }} - {{ $vacatureWebsites['menu_url'] }}</a></li>-->
			<br><a href="{{ $url }}">{{ $vacatureWebsites['id'] }} - {{ $vacatureWebsites['menu_url'] }}</a>


		@endforeach
	    @endif

        </ul>
    </nav>