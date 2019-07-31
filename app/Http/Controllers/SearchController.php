<?php

namespace App\Http\Controllers;

use App\product;
use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function search(SearchRequest $request){

        $query = product::query();

        //keyword項目に入っていたらnameから検索
        if($request->filled('name')){
            $query->where('name','like','%'. $request->keyword .'%');
        }

        //値段範囲が入っていれば値段から検索
        if($request->filled(['min_value', 'max_value'])){
            $query->whereBetween('value',[$request->min_value,$request->max_value]);
        }
        $product = $query->get();
        foreach ($product as $p){
            if(Storage::disk('s3')->exists("/image/$p->image")) {
                $img = Storage::disk('s3')->get("/image/$p->image");
                $p->image = base64_encode($img);
            } else {
                $p->image = "Image not found";
            }
        }

        $response['status']  = '200 OK';
        $response['summary'] = 'success.';
        $response['data']    = $product;
        return $response;
    }
}
