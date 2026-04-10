<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class FixLanguage extends Command
{
    protected $signature = 'fix:language';

    protected $description = 'Command description';

    public const SPANISH = 'Chile,Colombia,Cuba,Argentina,Belize,Gibraltar,Spain,Guatemala,Dominican Republic,Mexico,Ecuador,Peru,Panama,Uruguay,Paraguay,Costa Rica,Bolivia,El Salvador,Honduras,Andorra,Nicaragua,Puerto Rico,Venezuela,Angola,Portugal,Brazil,Mozambique,Guine bissau,Sao Tome e Principe,Macao,Timor Leste';
    public const ENGLISH = 'USA,UK,Ireland,Australia,New Zealand,Germany,Bahamas,Jamaica,Ghana,Kenya,Morocco,UAE,Saudi Arabia,Philippines,Malaysia,Singapore,Trinidad,Tobago,China,Nigeria,India,Pakistan,France,Cayman Islands,Switzerland,Iraq,Nigeria,Dominica,Fiji,Salomon Islands,Barbados,Bermudas Island,Hong-Kong,Japan,South Korea,Rwanda,Uganda,Egypt,France,Morocco,Gabon,Congo,Belgium,Monaco,Côte d\'Ivoire,Benin,Guadeloupe,Saint Martin,French Guiana,Wallis and Futuna,Cameroon,Djibouti,Senegal,Mali,Seychelles,Switzerland,Togo,Niger,Algeria,Tunisia,Liberia,';
    
    public function handle()
    {
        $spanish = array_map('trim', explode(',', self::SPANISH));
        Lead::whereHas('customer', function($query) use($spanish) {
            $query->whereIn('country', $spanish);
        })->update([
            'language' => 'es'
        ]);

        $english = array_map('trim', explode(',', self::ENGLISH));
        Lead::whereHas('customer', function($query) use($english) {
            $query->whereIn('country', $english);
        })->update([
            'language' => 'en'
        ]);
    }
}
