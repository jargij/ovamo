<html><title><header>ms</header></title><body>
	<?php
		/** Multiscraper: Scraper die beschikbaarheidsinformatie kan ophalen van de websites van internet-
			providers. 
			(C) Dialogic innovatie & interactie, 2014-2015
			Auteur: Tommy van der Vorst (vandervorst@dialogic.nl) - scraping-framework
			Auteur: Bob Stopler (stopler@dialogic.nl) - originele scrapers voor Ziggo en UPC
			
			De multiscraper verwacht een invoerbestand (crawl_addresses.csv) dat is geformatteerd als CSV-bestand
			met velden gescheiden door een puntkomma, en geen aanhalingstekens rond de velden. De eerste kolommen
			moeten zijn: ID-code (zelf te kiezen), postcode, huisnummer, huisletter, huisnummertoevoeging. 
			
			De multiscraper loopt, voor iedere provider waarvoor in dit script een scraper is gedefinieerd, alle
			regels in de invoer-CSV af en verstuurt in batches een verzoek aan de website van de provider. Het
			resultaat wordt geinterpreteerd (op een per provider specifieke manier). De resultaten komen per 
			provider terecht in een bestand provider_out.csv. Het formaat van de resultaten is per provider afwijkend.
			
			Om een provider toe te voegen implementeer je hieronder een class Scraper voor de provider. De variabele
			$aProviders onderaan bepaalt welke providers worden gescraped.
		**/
		
		//Wouter: heb ik uit gezet.
		//header("Content-type: text/plain");
		set_time_limit(0);
		error_reporting(E_ALL & ~E_NOTICE);
		
		interface Scraper {
			/** Retourneert de URL die moet worden opgehaald om een bepaald adres te checken op beschikbaar-
				heid. De parameter aRij bevat een regel uit de invoer-CSV. Deze ziet er als volgt uit: 
			array($sID, $sPostcode, $sHuisnummer, $sHuisletter, $sHuisnummerToevoeging)/ **/
			public function getUrl($aRij);
			
			/** Deze methode ontvangt de tekst die op de URL gevonden is en retourneert een array met 
			resultaten. Deze array wordt een rij in de output CSV. **/
			public function interpretResult($sContent);
		}
		
		class XS4AScraper implements Scraper {
			//xs4all maakt gebruik van een scriptje met json
			//je kan in de url alle variabelen invullen, xs komt dan terug met:
			/* Let op estimated_realspeed_down
				{"code":0,"result":{"input":{"zipcode":"3612AJ","housenumber":"56","housenumberext":"42"},"location":{"zipcode":"3612AJ","housenumber":"56","street":"Westbroekse Binnenweg","city":"TIENHOVEN UT","extensions":[]},"feasible":{"error":"0","location":{"zipcode":"3612AJ","housenumber":"56","extension":"42","street":"Westbroekse binnenweg","city":"Tienhoven ut"},"products":{"1":{"name":"internet_compact","estimated_realspeed_down":"2000","estimated_realspeed_up":"640","technology":"ADSL","tv":"0","hd":"0","stb":"0","attainable":1},"2":{"name":"internet_smart","estimated_realspeed_down":"2000","estimated_realspeed_up":"640","technology":"ADSL","tv":"0","hd":"0","stb":"0","attainable":1}}},"fit":{"status":"no","plandate":null}},"input":{"zipcode":"3612AJ","nr":"56","ext":"42"},"errors":[]}
			*/
			
			public function getUrl($aRij) {
				echo "<bold><br>BEGIN</bold>";
				$sPostcode = $aRij[1];
				$sHuisnummer = $aRij[2];
				$sToevoeging = $aRij[3].$aRij[4];
				echo "https://www.xs4all.nl/json/zipcheck/?zipcode=$sPostcode&nr=$sHuisnummer&ext=$sToevoeging";
				return "https://www.xs4all.nl/json/zipcheck/?zipcode=$sPostcode&nr=$sHuisnummer&ext=$sToevoeging";
				
			}
			
			public function interpretResult($sContent) {
				$aRij = array();
				$oData = @json_decode($sContent);
				$allProducts = $oData->result->feasible->products;
				if(is_object($oData))
				{
					$aRij[] = $oData->code;
					$aRij[] = $oData->error;
					$aRij[] = $oData->result->input->zipcode;
					$aRij[] = $oData->result->input->housenumber;
					$aRij[] = $oData->result->input->housenumberext;
				}
				
				$availableProducts = Array();
				foreach($allProducts as $key => $product) {
					//echo "<br>Speed: ".$product->estimated_realspeed_down;
					//echo "<br>technology: ".$product->technology;
					//echo "<br>attainable: ".$product->attainable;
					//bestaat deze technology al? 
					if ($availableProducts[$product->technology]) {
						//echo "<br>In array!";
						//controleer of de speed hoger is
						if ($availableProducts[$product->technology]["estimated_realspeed_down"] < $product->estimated_realspeed_down) {
							//echo "<br>Speed = higher!";
							//waarde is hoger, opslaan
							$availableProducts[$product->technology]["estimated_realspeed_down"] = $product->estimated_realspeed_down;
						} //end if
					} //end if			
					//technology bestaat nog niet
					else {
						//echo "<br>nieuw!";
						//controleren of de technology beschikbaar is: 
						if ($product->attainable == 1) {
							//waarde opslaan
							$availableProducts[$product->technology] = $availableProducts[$product->technology];
							$availableProducts[$product->technology]["technology"] = $product->technology;
							$availableProducts[$product->technology]["estimated_realspeed_down"] = $product->estimated_realspeed_down;
							//$availableProducts[$product->technology]["attainable"] = $product->attainable;
							} //end if
					} //end else
					//echo "<pre>";
					//print_r($availableProducts);
					//echo "</pre>";
				} //end foreach
				
				foreach ($availableProducts as $product) {
					$aRij[] = $product["technology"];
					$aRij[] = $product["estimated_realspeed_down"];
				}// end foreach
				
				//echo "<br>data:<br>";
				//echo "<pre>";
				//print_r($availableProducts);
				//print_r($aRij);
				//echo "</pre>";

				return $aRij;
				}
			}
			
			
			class TelfortGlasScraper implements Scraper {
				public function getUrl($aRij) {
					$sPostcode = $aRij[1];
					$sHuisnummer = $aRij[2];
					
					
					
					$sToevoeging = $aRij[3].$aRij[4];
					return "http://www.telfort.nl/gxcontent///web/wcbservlet/nl.telfort.gx.oplevercheckelement.deliverycheckservlet/checkfiber?zipcode=$sPostcode&housenr=$sHuisnummer&housenrext=$sToevoeging";
				}
				
				public function interpretResult($sContent) {
					$aRij = array();
					$oData = @json_decode($sContent);
					if(is_object($oData))
					{
						$aRij[] = $oData->isError;
						$aRij[] = $oData->address->zipcode;
						$aRij[] = $oData->address->houseNumber;
						$aRij[] = $oData->address->houseNumberExtension;
						$aRij[] = $oData->message;
					}
					echo "<br>old:<br>";
					//echo "<br>old message: ".$oData->message;
					
					$pat = "Controleer a.u.b. of de huisnummertoevoeging goed is ingevuld. Op uw adres zijn de volgende toevoegingen beschikbaar: , ";
					
					$newMessage = preg_replace($pat, "", $oData->message);
					
					//echo "<br>new message: ".$newMessage;
					
					return $aRij;
				}
			}
			
			class KPNBGBScraper implements Scraper {
				public function getUrl($aRij) {
					$sPostcode = $aRij[1];
					$sHuisnummer = $aRij[2];
					
					//$sToevoeging = $aRij[3].$aRij[4];
					$sHuisletter = $aRij[3];
					$sToevoeging = $aRij[4];
					return "https://kpn-compleet-fpi-info.fourstack.nl/addresses/search/v1?address=$sPostcode$sHuisnummer$sHuisletter$sToevoeging&format=json";
				}
				
				public function interpretResult($sContent) {
					
				$oData = @json_decode($sContent);
				$allProducts = $oData->search_address_response->address;

				$availableProducts = Array();
				
				//adres
				$availableProducts["zipcode"] = $allProducts->zipcode."";
				$availableProducts["housenumber"] = $allProducts->housenumber."";
				$availableProducts["housenumber_ext"] = $allProducts->housenumber_ext."";
				
				//error / warning
				$availableProducts["error"] = $oData->search_address_response->status->error."";
				$availableProducts["warning"] = $oData->search_address_response->status->warning."";
				
				//snelheid en 4g beschikbaar
				$availableProducts["actual_availability_bandwidth"] = $allProducts->actual_availability->bandwidth."";
				$availableProducts["actual_availability_mobile_4g"] = $allProducts->actual_availability->mobile_4g."";
				
				//geplande upgrades, opbouw suggereert dat dit er meerdere kunnen zijn.
				if ($allProducts->planned_upgrades) {
						foreach ($allProducts->planned_upgrades as $key => $product) {
						$availableProducts["planned_upgrades_$key"."_type"] = $product->type."";
						$availableProducts["planned_upgrades_$key"."_bandwidth"] = $product->bandwidth."";
						$availableProducts["planned_upgrades_$key"."_planning"] = $product->planning."";
					}	
				}
					
					return $availableProducts;
				}
			}
			
			class TelfortDSLScraper implements Scraper {
				public function getUrl($aRij) {
					$sPostcode = $aRij[1];
					$sHuisnummer = $aRij[2];
					
					//$sToevoeging = $aRij[3].$aRij[4];
					$sToevoeging = $aRij[3];
					return "http://www.telfort.nl/gxcontent///web/wcbservlet/nl.telfort.gx.oplevercheckelement.deliverycheckservlet/checkcopper?zipcode=$sPostcode&housenr=$sHuisnummer&housenrext=$sToevoeging";
				}
				
				public function interpretResult($sContent) {
					$aRij = array();
					$oData = @json_decode($sContent);
					if(is_object($oData))
					{
						$aRij[] = $oData->isError;
						$aRij[] = $oData->address->zipcode;
						$aRij[] = $oData->address->houseNumber;
						$aRij[] = $oData->address->houseNumberExtension;
						$aRij[] = $oData->message;
					}
					
					//$aTransformedAddresses = array;
					
					//echo "<pre>";
					//print_r()
					//echo "<pre>";
					
					
					//echo "<br>old message: ".$oData->message;
					
					$pat = "/Controleer a.u.b. of de huisnummertoevoeging goed is ingevuld. Op uw adres zijn de volgende toevoegingen beschikbaar: , /";
					
					$newMessage = preg_replace($pat, "", $oData->message);
					$newMessage = str_replace(' ', '', $newMessage);
					//$aNewMessages = array();
					$aNewMessages = explode(",", $newMessage);
					foreach($aNewMessages as $message) {
						echo "<br>addition: ".$message;
					}
					
					
					echo "<br><br>new message: ".$newMessage."<br><br>";
					
					return $aRij;
				}
			}
			
			class UPCScraper implements Scraper {
				public function getUrl($aRij)
				{
					$sURL = "http://www.upc.nl/shop/api/rest/v1/rfs/location/zipCode/".$aRij[1]."/houseNumber/".$aRij[2];
					$sExtension = $aRij[3].$aRij[4];
					if(strlen($sExtension)>0)
					{
						$sURL .= '/extension/'.$sExtension;
					}
					return $sURL;
				}
				
				public function interpretResult($sContent) {
					$oResponse = @json_decode($sContent);
					$aProducts = array();
					
					foreach($oResponse->availability as $oProduct)
					{
						$aProducts[$oProduct->name] = $oProduct->available;
						if($oProduct->name=="internet")
						{
							$sResult = $oProduct->available===true ? 'Yes' : 'No';
						}
					}
					
					return array($sResult, json_encode($aProducts));
				}
			}
			
			class ZiggoScraper implements Scraper {
				public function getUrl($aRij)
				{
					// Ziggo doet niet aan huisletters
					return "https://www.ziggo.nl/restservices/PostCodeCheck/".$aRij[1]."/".$aRij[2];
				}
				
				public function interpretResult($sContent) {
					$bBinnenVerzorgingsGebied = substr_count($sContent, "Het adres ligt binnen het verzorgingsgebied van Ziggo.") > 0;
					$bAvailable = $bBinnenVerzorgingsGebied && substr_count($sContent, "Op het adres dat u heeft ingevoerd, zijn de producten van Ziggo helaas niet beschikbaar.")==0;
					
					return array($bBinnenVerzorgingsGebied ? 'in_ziggo_area' : 'not_in_ziggo_area', $bAvailable ? 'ziggo_yes' : 'ziggo_no');
				}
			}
			
			/*	vodafone
				Maakt geen gebruik van zipcode in de url.
				Heeft een form wat een java functie uitvoert. Bij het klikken op de knop verder wordt onclick="sendZipcodeCheckData('zipcodecheck')" uitgevoerd.
				Deze functie staat in public.js en is:
				
				De form staat in dslservice.html line 61:
				<a id="submit" onclick="sendZipcodeCheckData(&#39;zipcodecheck&#39;)" rel="formSubmit" class="buttonActive"><span>Verder</span></a>
				
				public.js line: 6
				function sendZipcodeCheckData (action) {
				var rand = Math.random(9999);
				var queryString = $('#'+action).formSerialize() + '&rand=' + rand;
				$('#loading').show();
				$.post(action, queryString, function(data) {
				handleRequest(data);
				});
				}
				
				hier wordt de functie aangeroepen:
				public.js line: 102
				function submitenter(myfield,e) {
				var keycode;
				if (window.event) keycode = window.event.keyCode;
				else if (e) keycode = e.which;
				else return true;
				if (keycode == 13) {
				sendZipcodeCheckData($('form').attr('id'));
				return false;
				}
				else
				return true;
				}
			*/
			
			
			
			
			//17-2 toegevoegd door Wouter Koppers
			class tele2Scraper implements Scraper {
				
				public function checkNeedle() {
					
				}
				
				public function getUrl($aRij)
				{
					$sPostcode = $aRij[1];
					$sHuisnummer = $aRij[2];
					$sToevoeging = $aRij[3].$aRij[4];
					return "https://www.tele2.nl/thuis/postcodecheck/?postcode=$sPostcode&huisnummer=$sHuisnummer&toevoeging=$sToevoeging";
				}
				
				public function interpretResult($sContent) {
					
					/*  tot nu toe 2 berichten gevonden die tele2 kan tonen. Hierdoor 3 opties: wel internet, geen internet, ander bericht(foutafhandeling)
						tele 2 biedt 4 pakketten aan, ik heb nog geen pagina gevonden waar er combinaties van wel of niet aangeboden zijn.
						tot nu toe of tele2 biedt alle pakketten aan, of niets.
						Internet & Bellen is beschikbaar op dit adres. Bekijk Pakket
						Internet is beschikbaar op dit adres. Bekijk Pakket
						Internet & TV is beschikbaar op dit adres. Bekijk Pakket
						Internet, Bellen & TV is beschikbaar op dit adres. Bekijk Pakket
					*/	
					
					//errorhandling, deze wordt 1 als er een onbekend bericht door tele2 wordt getoond.
					$bDifferentMessage = 0;
					
					//het bericht wat in het csv bestand wordt weggeschreven:
					$message = '';
					
					//In de broncode kijken welke pakketten beschikbaar zijn.
					$bInternetEnBellenAvailable = substr_count($sContent, "Internet &amp; Bellen is beschikbaar op dit adres.") > 0; 
					$bInternetAvailable = substr_count($sContent, "Internet is beschikbaar op dit adres. ") > 0; 
					$bInternetEnTVAvailable = substr_count($sContent, "Internet &amp; TV is beschikbaar op dit adres. ") > 0; 
					$bInternetBellenEnTVAvailable = substr_count($sContent, "Internet, Bellen &amp; TV is beschikbaar op dit adres.") > 0; 
					
					//echo "<br>bInternetEnBellenAvailable: " . $bInternetEnBellenAvailable;
					//echo "<br>bInternetAvailable: " . $bInternetAvailable;
					//echo "<br>bInternetEnTVAvailable: " . $bInternetEnTVAvailable;
					//echo "<br>bInternetBellenEnTVAvailable: " . $bInternetBellenEnTVAvailable;
					
					//bericht samenstellen, wanneer een pakket beschikbaar is:
					if ($bInternetEnBellenAvailable) {
						$message = ' Internet & Bellen is beschikbaar op dit adres.';
					}
					if ($bInternetAvailable) {
						$message .= ' Internet is beschikbaar op dit adres';
					}
					if ($bInternetEnTVAvailable) {
						$message .= ' Internet & TV is beschikbaar op dit adres.';
					}
					if ($bInternetBellenEnTVAvailable) {
						$message .= ' Internet, Bellen & TV is beschikbaar op dit adres.';
					}
					
					//niet beschikbaar:
					if (!$bInternetAvailable && !$bInternetEnBellenAvailable && !$bInternetEnTVAvailable && !$bInternetBellenEnTVAvailable) {
						$bInternetNotAvailable = substr_count($sContent, "unavailable") > 0;
						
						$message = 'Er zijn helaas geen pakketten beschikbaar op dit adres.';	
						
						//er wordt een onbekend bericht getoond:
						//zowel available ans unavailable hebben de start tag, ik neem aan dat alle berichten die aan de gebruiker worden getoond binnen deze start tag staan.
						if (!$bInternetNotAvailable) {
							$bDifferentMessage = substr_count($sContent, "start") > 0;
							
							$message = 'Niet duidelijk wat voor bericht tele 2 geeft. Hieronder de code:<br><br> ';
							
							//de "start" tag vinden in de source van de tele2 site tot de tekst "wijzig adres"
							//de tekst die hier tussen staat teruggeven als tekst
							$posStart = strpos($sContent, "start");
							$posTot = strpos($sContent, "Wijzig adres");
							
							//om de tag start"> te verwijderen, 7 karakters opschuiven:
							$posStart+=7;
							
							$tele2Code = substr($sContent, $posStart, ($posTot-$posStart)); 
							$tele2Code = preg_replace('/\s+/', ' ', trim($tele2Code)); //enters weghalen
							$message = $message."<br>tele2Code: " . $tele2Code;	
						}
					} 
					
					/*
						$aRij = array();
						$oData = @json_decode($sContent);
						if(is_object($oData))
						{
						$aRij[] = $oData->isError;
						$aRij[] = $oData->address->zipcode;
						$aRij[] = $oData->address->houseNumber;
						$aRij[] = $oData->address->houseNumberExtension;
						$aRij[] = $oData->message;
						}
						return $aRij;
					*/
					//echo '<br><br>';
					$aRij = array();
					
					$aRij[] = $bDifferentMessage;
					$aRij[] = "1742gb";
					$aRij[] = 55;
					$aRij[] = "b";
					$aRij[] = $message;
					
					return $aRij;
				}
				
			}
			
			function crawl($fIn, $fOut, Scraper $oScraper, $iBatchSize = 1)
			{
				$bDone = false;
				
				while(!$bDone)
				{
					$iStartTime = microtime(true);
					$oMulti = curl_multi_init();
					
					$aThreads = array();
					for($iItem=0; $iItem < $iBatchSize && !$bDone; $iItem++) 
					{
						
						$aItem = fgetcsv($fIn, 0, ';');
						if($aItem===FALSE)
						{
							$bDone = TRUE;
							break;
						}
						$oThread = curl_init();
						$sURL = $oScraper->getURL($aItem);
						//echo "URL=$sURL";
						curl_setopt($oThread, CURLOPT_URL, $sURL);
						curl_setopt($oThread, CURLOPT_HEADER, false);
						curl_setopt($oThread, CURLOPT_VERBOSE , 1);
						curl_setopt($oThread, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($oThread, CURLOPT_RETURNTRANSFER, true);
						curl_multi_add_handle($oMulti, $oThread);
						$aThreads[$aItem[0]] = $oThread;
					}
					
					// Run concurrent requests
					$iRunning = null;
					do {
						curl_multi_exec($oMulti, $iRunning);
					} 
					while($iRunning > 0);
					
					// Evaluate responses
					foreach($aThreads as $sID => $oThread)
					{
						$sContent = curl_multi_getcontent($oThread);
						$sError = curl_error($oThread);
						$iResponseCode = curl_getinfo($oThread, CURLINFO_HTTP_CODE);
						$iResponseTime = curl_getinfo($oThread, CURLINFO_TOTAL_TIME);
						
						$aRij = array($sID, $iResponseCode, $iResponseTime, $sError);
						if($iResponseCode==200)
						{
							try {
								$aRij = array_merge($aRij, $oScraper->interpretResult($sContent));
							}
							catch(Exception $oException)
							{
								$aRij[] = $oException->getMessage();
							}
						}
						
						echo implode($aRij, ';')."\r\n";
						fputcsv($fOut, $aRij, ';');
					}
					
					curl_multi_close($oMulti);
					$iDuration = microtime(true)-$iStartTime;
					echo round($iDuration/count($aThreads),3)."s/address\r\n";
					sleep(10);
				}
			}
			
			$aProviders = array(
			//'ziggo' => new ZiggoScraper(),
			//'upc' => new UPCScraper(),
			//'tele2' => new tele2Scraper(),
			
			//'telfort_glas' => new TelfortGlasScraper(),
			//'telfort_dsl' => new TelfortDSLScraper()
			//'defysiotherapeut' => new DeFysiotherapeutScraper()
			//'XS4A' => new XS4AScraper()
			'KPNBGB' => new KPNBGBScraper()
			//
			);
			
			//$fIn = fopen("crawl_addresses_small.csv", "r");
			$fIn = fopen("adressen_zeeland2016_extra.csv", "r");
			//$fIn = fopen("testsetInteressanteAdressenKlein.csv", "r");
			foreach($aProviders as $sProvider => $oScraper)
			{
				echo "Start crawl for provider $sProvider\r\n";
				fseek($fIn, 0);
				$fOut = fopen($sProvider."adressen_zeeland2016_extra_OUTPUT.csv", "w");
				crawl($fIn, $fOut, $oScraper);
				fclose($fOut);
			}
		?>
	</body>
</html>