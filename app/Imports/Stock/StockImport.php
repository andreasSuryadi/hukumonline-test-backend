<?php

namespace App\Imports\Stock;

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Dealer;
use App\Models\Stock;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;

class StockImport implements ToModel, WithHeadingRow, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsErrors, SkipsFailures;
    
    public function model(array $stock)
    {
        DB::transaction(function() use ($stock) {
            $dealer = Dealer::whereName($stock['dealername'])->first();
            if (empty($dealer)) {
                $dealer = Dealer::create([
                    'name' => $stock['dealername'],
                ]);
            }
    
            $carBrand = CarBrand::whereName($stock['carbrand'])->first();
            if (empty($carBrand)) {
                $carBrand = CarBrand::create([
                    'name' => $stock['carbrand'],
                ]);
            }
            $carBrand->dealer()->attach($dealer->id);
    
            $carModel = CarModel::create([
                'name' => $stock['carmodel'],
                'dealer_id' => $dealer->id,
                'car_brand_id' => $carBrand->id
            ]);

            $stock = Stock::create([
                'car_model_id' => $carModel->id,

                'stock_number' => $stock['stocknumber'],
                'stock_date' => $stock['stockdate'],
                'ip_address' => $stock['ip_address'],
            ]);
        });
    }
}
