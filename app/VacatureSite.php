<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\VacatureSiteLijst;
use App;

class VacatureSite extends Model
{

	protected $table = "vacature_sites";

    public function vacatureSiteLijst() {
        return $this->belongsTo('App\VacatureSiteLijst'); // this matches the Eloquent model
    }

    protected $fillable = ['vacature_sites_lijst_id', 'url', 'date_added', 'content', 'image_path', 'status', 'error'];
}
