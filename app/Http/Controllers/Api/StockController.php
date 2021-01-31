<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Stock\ListStockResource;
use App\Imports\Stock\StockImport;
use App\Models\CarModel;
use App\Models\Dealer;
use App\Models\Stock;
use Illuminate\Http\Request;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $filterBy = $request->query('filter_by', 'dealer');
        $isZeroStock = $request->query('is_zero_stock', false);
        $fromDate = $request->query('from_date', date('Y-m-01'));
        $toDate = $request->query('to_date', date('Y-m-t'));
        $orderBy = $request->query('sort_field', 'name');
        $orderDirection = $request->query('sort_order', 'desc');

        $fromDate = date("Y-m-d", strtotime($fromDate));
        $toDate = date("Y-m-d", strtotime($toDate)) . " 23:59:59";

        $carModels = CarModel::with(['carBrand', 'dealer'])
            ->orderBy($orderBy, $orderDirection);
            
        if ($filterBy == 'dealer') {
            $carModels = $carModels->whereHas('dealer', function ($x) use ($search) {
                $x->where('name', 'LIKE', '%' . $search . '%');
            });
        } else if ($filterBy == 'car_brand') {
            $carModels = $carModels->whereHas('carBrand', function ($x) use ($search) {
                $x->where('name', 'LIKE', '%' . $search . '%');
            });
        } else if ($filterBy == 'car_model') {
            $carModels = $carModels->where('name', 'LIKE', '%' . $search . '%');
        }
            
        $carModels = $carModels->get();

        $listStock = [];
        foreach ($carModels as $carModel) {
            $stocks = Stock::where('car_model_id', $carModel->id)
                ->whereBetween('stock_date', [$fromDate, $toDate])
                ->get();

            $list = [];
            foreach ($stocks as $stock) {
                $list[] = [
                    'id' => $stock->id,
                    'stock' => $stock->stock_number,
                    'date' => $stock->stock_date,
                ];
            }

            if ($isZeroStock == 'true') {
                if (count($list) == 0) {
                    $listStock[] = [
                        'id' => $carModel->id,
                        'dealer' => $carModel->dealer['name'],
                        'car_brand' => $carModel->carBrand['name'],
                        'car_model' => $carModel->name,
                        'stocks' => $list
                    ];
                }
            } else if ($isZeroStock == 'false') {
                $listStock[] = [
                    'id' => $carModel->id,
                    'dealer' => $carModel->dealer['name'],
                    'car_brand' => $carModel->carBrand['name'],
                    'car_model' => $carModel->name,
                    'stocks' => $list
                ];
            }
        }

        $total = count($listStock);
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->has('page') ? (int) $request->page : 1;
        $offset = ($currentPage - 1) * $perPage;
        $data = array_slice($listStock, $offset, $perPage);
        $data = new LengthAwarePaginator($data, $total, $perPage, $currentPage, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $request->query()
        ]);

        return ListStockResource::collection($data);
    }

    public function import(Request $request)
    {
        $import = new StockImport();
        $import->import($request->file('file'));

        return response()->json("Import Sukses", 200);
    }
}
