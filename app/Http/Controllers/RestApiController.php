<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RestApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = product::all();
        return $items;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $img = $request->image;
        $file_name = $this->getFilename($img);
        Storage::put("/image/$file_name",$img);
        $product = new product;

        $product->fill($request->all());
        $product->image = "$file_name";
        $product->save();
        return response('データベース更新完了', 200)
            ->header('Content-Type', 'application/json');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = product::find($id);
        $path = "/image/{$data->image}";
        $img = Storage::get($path);
        $img = base64_encode($img);
        $data->image = $img;
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, $id)
    {
        $product = product::find($id);
        if($product == NULL) {
            return response('存在しないIDです。', 200)
                ->header('Content-Type', 'application/json');
        }
        if($request->filled('image')){
            $img = $request->image;
            $file_name =$this->getFilename($img);
            Storage::delete("/image/$product->image");
            Storage::put("/image/$file_name",$img);
            $product->image = "$file_name";
        }
        foreach (array('name','desc','value') as $r){
            if($request->filled($r)) {
                $product->$r = $request->$r;
            }
        }
        $product->save();
        return response('データベース更新完了', 200)
            ->header('Content-Type', 'application/json');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function getFilename($img){
        $file_name = md5(uniqid(rand(), true));
        $img = base64_decode($img);
        $type = finfo_buffer(finfo_open(), $img,FILEINFO_MIME_TYPE);
        switch ($type) {
            case 'image/jpeg':
                $ext='jpg';
                break;
            case 'image/png':
                $ext='png';
                break;
            case 'image/gif':
                $ext='gif';
                break;
        }
        $file_name = "$file_name.$ext";
        return $file_name;
    }
}
