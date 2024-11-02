<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'continent_code',
        'continent_name',
        'country_code2',
        'country_code3',
        'country_name',
        'country_name_official',
        'country_capital',
        'state_prov',
        'district',
        'city',
        'zipcode',
        'latitude',
        'longitude',
        'is_eu',
        'calling_code',
        'country_tld',
        'languages',
        'country_flag',
        'geoname_id',
        'isp',
        'connection_type',
        'organization',
        'asn'
    ];
}
