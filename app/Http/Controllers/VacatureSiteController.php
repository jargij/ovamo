<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class VacatureSiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    

    public function index(Request $request)
    {
        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        //$record = $this->viewOneVacatureViaApi(1, "2016-07-14");
        


        $record = DB::select("SELECT * FROM vacature_sites WHERE `vacature_sites_lijst_id` = 1 AND date_added > 2018-08-14 ORDER BY date_added DESC LIMIT 1");

        $record = $record[0];

        //echo "<pre><xmp>";
        //print_r($record);
        //echo "</pre></xmp>";

        echo $record->date_added;

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        $data['content'] = $record->content;
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', 1);
        $request->session()->put('date_added', $data['date_added']);

        //echo $request->session()->pull('vacatureId', 1);
        //echo $request->session()->pull('date_added');

        return view('vactureSites.index', $data); 
    }

    public function getListOfVacatureWebsites() {
        $vacatureWebsitesLijstFromDb = DB::select("SELECT id, oude_naam, nieuwe_naam, brin, code FROM `vacature_sites_lijst`");

        foreach ($vacatureWebsitesLijstFromDb as $key => $vacatureWebsite) {

            if (isset($vacatureWebsite->nieuwe_naam) and $vacatureWebsite->nieuwe_naam != '' and $vacatureWebsite->nieuwe_naam != '?') {
                $vacatureWebsitesLijst[$key]['url'] = $vacatureWebsite->nieuwe_naam;
            }
            else {
                $vacatureWebsitesLijst[$key]['url'] = $vacatureWebsite->oude_naam;
            }
            $vacatureWebsitesLijst[$key]['id'] =  $vacatureWebsite->id;
            $vacatureWebsitesLijst[$key]['menu_url'] =  parse_url($vacatureWebsitesLijst[$key]['url'], PHP_URL_HOST);
            $vacatureWebsitesLijst[$key]['menu_url'] = str_replace('www.', '', $vacatureWebsitesLijst[$key]['menu_url']); 
        }
        return $vacatureWebsitesLijst;
    }

    public function getAllScrapedVacatureWebsiteDates($vacatureId = 2) {
        $result = DB::select("SELECT date_added FROM `vacature_sites` WHERE vacature_sites_lijst_id= ".$vacatureId);
        //$result = DB::select("SELECT * FROM `vacature_sites` WHERE id=".$vacatureId);
    
        echo "<pre><xmp>";
        print_r($result);
        echo "</pre></xmp>";
    }    
    
    public function info(Request $request) {
        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        $vacatureId = $request->session()->pull('vacatureId', 1);
        $datum = $request->session()->pull('date_added', '14-07-2016');

        $record = DB::select("SELECT * FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$datum.'" LIMIT 1');

        $record = $record[0];

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        $data['content'] = "
        <h1>Welkom op de Onderwijs VAcature MOnitor</h1>
        <p><b>Doel:</b> Historische gegevens bekijken van de vacature pagina van scholen</p>
        <p><b>Werking:</b> Elke dag worden er nieuwe records toegevoegd van een aantal scholen, op dit moment 269. Per website wordt opgeslagen welke URL is geraadpleegd, de datum, de responscode van de website (200, 301, 303, 404 zijn de meest voorkomende) en wanneer de pagina goed beschikbaar is de content. Deze applicatie laat de standaard de laatst opgeslagen website zien</p>
        <p><b>Navigatie:</b> Meerdere mogelijkheden:
        <ul>
        <li>Je kan met links werken, <br><br>
            previousschool = navigeert naar de vorige school, op id gebaseerd
            <br>nextschool = navigeert naar de volgende school, op id gebaseerd
            <br>nextdate = navigeert naar de volgende datum die in de database voorkomt, als er op 19-6 en 21-6 wel resultaten zijn en 20-6 niet, wordt 20-6 overgeslagen.
            <br>previousdate = navigeert naar de volgende datum die in de database voorkomt,
            <br><br>de links aan de linkerkant brengen je naar de school met de huidige gebruikte datum.</li><br>

        <li>Gebruik maken van URL<br><br>/action/previousschool <br> /action/nextschool
        <br> /action/nextdate
        <br> /action/previousdate
        <br> /vacaturesites/ toont de eerste school, laatste datum
        <br> /vacaturesites/5/2016-07-14 - de 5 is het id van de school, daarna de datum.</li>
<br>
        <li>Keyboard: <br><br>
        W = nextschool
        <br>S = previousschool
        <br>A = previousdate
        <br>D = nextdate

        </ul></p>
        <p><b>Links aanpassen:</b> Links naar vacaturesites kunnen aangepast worden, graag de gewenste wijziging mailen naar zie hieronder.</p>

        <p><b>Vragen:</b> Mailen naar koppers@dialogic.nl of vankan@dialogic.nl

    
        ";
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', $record->vacature_sites_lijst_id);
        $request->session()->put('date_added', $data['date_added']);

        $data['content'] = $this->getBodyContentFromWholeWebsite($data['content']);

        //echo $request->session()->pull('vacatureId', 1);
        //echo $request->session()->pull('date_added');

        return view('vactureSites.show', $data); ;
    }    

    public function loadStandardContent($vacatureId, $datum, Request $request) {

        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        $record = DB::select("SELECT * FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$datum.'" LIMIT 1');

        $record = $record[0];

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        //$data['content'] = $record->content;
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', $vacatureId);
        $request->session()->put('date_added', $data['date_added']);

       // $data['content'] = $this->getBodyContentFromWholeWebsite($data['content']);

        //echo $request->session()->pull('vacatureId', 1);
        //echo $request->session()->pull('date_added');

        return view('vactureSites.index', $data); 

        //dit kan je gebruiken om de volgende  / vorige datum te selecteren:
        //SELECT date_added, id, `vacature_sites_lijst_id` FROM vacature_sites WHERE `vacature_sites_lijst_id` = 2 AND date_added > 2016-07-14 ORDER BY date_added DESC LIMIT 1
        //SELECT date_added, id, `vacature_sites_lijst_id` FROM vacature_sites WHERE `vacature_sites_lijst_id` = 2 AND date_added > 2016-07-14 ORDER BY date_added ASC LIMIT 1
    }

    public function ajaxRequestForOneVacatureWebsite($vacatureId, $datum, Request $request) {
        
        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        $record = DB::select("SELECT * FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$datum.'" LIMIT 1');

        $record = $record[0];

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        $data['content'] = $record->content;
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', $vacatureId);
        $request->session()->put('date_added', $data['date_added']);

        $data['content'] = $this->getBodyContentFromWholeWebsite($data['content']);

        //echo $request->session()->pull('vacatureId', 1);
        //echo $request->session()->pull('date_added');

        return view('vactureSites.show', $data); 
    }

    public function nextschool(Request $request)
    {
        $date_added = $request->session()->pull('date_added');
        $vacatureId = $request->session()->pull('vacatureId');
        $vacatureId++;

        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        //$record = DB::select("SELECT * FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$date_added.'" LIMIT 1');

        try {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$date_added.'" LIMIT 1');

        } catch (\Exception $e) {
            $record = DB::select('SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE date_added = "'.$date_added.'"  ORDER BY vacature_sites_lijst_id ASC LIMIT 1');

        }

        if (!isset($record[0])) {
            $record = DB::select('SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE date_added = "'.$date_added.'"  ORDER BY vacature_sites_lijst_id ASC LIMIT 1');
        }

        $record = $record[0];

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        //$data['content'] = $record->content;
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', $vacatureId);
        $request->session()->put('date_added', $data['date_added']);

        return view('vactureSites.index', $data); 
    }

    public function getFullSiteInfo(Request $request) {

    }

    public function previousschool(Request $request)
    {
        $date_added = $request->session()->pull('date_added');
        $vacatureId = $request->session()->pull('vacatureId');
        $vacatureId--;

        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        //$record = DB::select("SELECT * FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$date_added.'" LIMIT 1');

        try {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$date_added.'" LIMIT 1');

        } catch (\Exception $e) {
            $record = DB::select('SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE date_added = "'.$date_added.'"  ORDER BY vacature_sites_lijst_id DESC LIMIT 1');

        }

        if (!isset($record[0])) {
            $record = DB::select('SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE date_added = "'.$date_added.'"  ORDER BY vacature_sites_lijst_id DESC LIMIT 1');
        }


        $record = $record[0];

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        //$data['content'] = $record->content;
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', $vacatureId);
        $request->session()->put('date_added', $data['date_added']);

        return view('vactureSites.index', $data); 
    }
    

    public function nextdate(Request $request)
    {
        $date_added = $request->session()->pull('date_added');
        $vacatureId = $request->session()->pull('vacatureId');
    
        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        //$record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added > "'.$date_added.'" ORDER BY date_added ASC LIMIT 1');

        try {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added > "'.$date_added.'" ORDER BY date_added ASC LIMIT 1');

        } catch (\Exception $e) {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' ORDER BY date_added ASC LIMIT 1');

        }

        if (!isset($record[0])) {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' ORDER BY date_added ASC LIMIT 1');
        }

        $record = $record[0];

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        //$data['content'] = $record->content;
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', $vacatureId);
        $request->session()->put('date_added', $data['date_added']);

        return view('vactureSites.index', $data); 
    }

    public function previousdate(Request $request)
    {
        $date_added = $request->session()->pull('date_added');
        $vacatureId = $request->session()->pull('vacatureId');

        $data['vacatureWebsitesLijst'] = $this->getListOfVacatureWebsites();

        //$record = DB::select("SELECT * FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added = "'.$date_added.'" LIMIT 1');
        
        //$record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added < "'.$date_added.'" ORDER BY date_added DESC LIMIT 1');

        try {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' AND date_added < "'.$date_added.'" ORDER BY date_added DESC LIMIT 1');

        } catch (\Exception $e) {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' ORDER BY date_added DESC LIMIT 1');

        }

        if (!isset($record[0])) {
            $record = DB::select("SELECT id, url, date_added, status, vacature_sites_lijst_id FROM vacature_sites WHERE `vacature_sites_lijst_id` = ".$vacatureId.' ORDER BY date_added DESC LIMIT 1');
        }


        $record = $record[0];

        $data['id'] = $record->id;
        $data['title'] = $record->url;
        $data['date_added'] = $record->date_added;
        $data['url'] = $record->url;
        //$data['content'] = $record->content;
        $data['status'] = $record->status;
        $data['vacature_sites_lijst_id'] = $record->vacature_sites_lijst_id;
        $data['vacature_sites_table']['url'] = $record->url;

        $request->session()->put('vacatureId', $vacatureId);
        $request->session()->put('date_added', $data['date_added']);

        return view('vactureSites.index', $data); 
    }

    public function viewOneVacatureViaApi($vacatureId, $date_added) 
    {
       // $record = DB::table('vacature_sites')->find($vacatureId);
       // $bear = Bear::find(1);

        $result = DB::select("SELECT * FROM `vacature_sites` WHERE id=".$vacatureId." AND date_added='".$date_added."'");
    
        return $result[0];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('vactureSites.create'); 
    }

    public function getBodyContentFromWholeWebsite($vacatureWebsite) {
        // print_r($vacatureWebsite);

        //preg_match('/</head>(.*)<\/body>/s', $vacatureWebsite, $matches);

        //print_r($matches);

        return $vacatureWebsite;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        return view('vactureSites.store'); 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {


        $data['id'] = $id;

         return view('vactureSites.show', $data); 
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        //
        return view('vactureSites.edit', $id); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //
        //
        return view('vactureSites.update', $id); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    
     return view('vactureSites.destroy', $id); 
    }
}
