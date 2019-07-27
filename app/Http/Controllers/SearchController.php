<?php

namespace App\Http\Controllers;

use App\product;
use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;

class SearchController extends Controller
{
    public function search(SearchRequest $request){
        $query = product::query();
        if($request->filled('keyword')){
            $query->where('name','like','%'. $request->keyword .'%');
        }
        if($request->filled(['min_value', 'max_value'])){
            $query->whereBetween('value',[$request->min_value,$request->max_value]);
        }
        $product = $query->get();
        $response['status']  = 'OK';
        $response['summary'] = 'success.';
        $response['data']    = $product;
        return $response;
    }
}
