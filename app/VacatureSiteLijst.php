<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\VacatureSite;
use App;

class VacatureSiteLijst extends Model
{	  

    protected $table = "vacature_sites_lijst";

    public function vacatureSite() {
        return $this->hasMany('App\VacatureSite');
    }

    protected $fillable = ['oude_naam', 'nieuwe_naam', 'getal', 'code', 'brin', 'opmerking'];
}
