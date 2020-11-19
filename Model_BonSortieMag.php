<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BonSortieMag extends Model
{
    protected $fillable = [
        'idSite','idStock','date', 'superviseur','numbonsm','prixtotal','destination','valide','observation','updateby','validateby'
      ];
}
