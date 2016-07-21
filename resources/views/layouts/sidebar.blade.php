<?php 
$nextschoolurl = URL::to('/action/nextschool');
$previousschoolurl = URL::to('/action/previousschool');
$nextdateurl = URL::to('/action/nextdate');
$previousdateurl = URL::to('/action/previousdate');
?>

<!doctype html>
<html>
<head>
    @include('includes.head')
</head>
<body>
<div class="container-fluid">

    <header class="row">
        @include('includes.header')
    </header>

    <div id="main" class="row">

        <!-- sidebar content -->
        <div id="sidebar" class="col-md-2">
            @include('includes.sidebar')
        </div>

        <!-- main content -->
        <div id="content" class="col-md-10">

            <style type="text/css" media="screen">
                iframe:focus { 
                    outline: none;
                    width: 900px;
                }

                iframe[seamless] { 
                    display: block;

                }
            </style>            

            <iframe width="900px" height= "6000px" src="http://localhost:9999/getonesite/{{$vacature_sites_lijst_id}}/{{$date_added}}"></iframe>
            
        </div>

    </div>

    <footer class="row">
        @include('includes.footer')
    </footer>

</div>
</body>

<script>

$(document).ready(function smoothScroll() {

    console.log('hello');

    //om te kunnen navigeren met het keyboard. 1tm4 voor tabbladen navigatie
    //up down links rechts voor 
    $(window).keyup(function (e) {
        // if($(".form-control").is(":focus") == true) {
        //  $(".headerone").text("Zoeken...");
        // }
        console.log('key'+e)
        
            if (e.keyCode == 49) { // 1        and 27 = Escape
                
                window.location.href = "http://www.google.com";
                //$('.nav a[href="action/nextschool"]'); 
            }
            if (e.keyCode == 50) { // 2        

                window.location.href = "{{$url}}";
                //$('.nav a[href="#Verloop"]').tab('show'); 
            }            

            if (e.keyCode == 87) { // w        
                window.location.href = "{{$previousschoolurl}}";
            }
            if (e.keyCode == 83) { // s        
                window.location.href = "{{$nextschoolurl}}";
            }            
            if (e.keyCode == 65) { // a        
                window.location.href = "{{$previousdateurl}}";
            }            
            if (e.keyCode == 68) { // d        
                window.location.href = "{{$nextdateurl}}";
            }
            if (e.keyCode == 51) { // 3        
                $('.nav a[href="#Methode"]').tab('show'); 
            }
            if (e.keyCode == 52) { // 4        
                $('.nav a[href="#Bronnen"]').tab('show'); 
            }
            if (e.keyCode == 65 && !e.ctrlKey) { // A = DOWN      
                currentId = $(".hero").attr("id");
                rightID = findRightWijkprofiel(alleBestaandeIDs, currentId, "next");
                getWijkprofiel(rightID);
                
                $(".headerone").text("Laden... ");
            }
            if (e.keyCode == 81) { // Q = up    
                currentId = $(".hero").attr("id");
                rightID = findRightWijkprofiel(alleBestaandeIDs, currentId, "prev");
                getWijkprofiel(rightID);
                
                $(".headerone").text("Laden... ");
            } //einde if keycode = 81

    }); //einde window keyup function   

}); //einde document ready
</script>

</html>