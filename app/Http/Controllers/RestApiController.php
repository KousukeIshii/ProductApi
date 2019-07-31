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
        $product = product::all();
        $response['status']  = '200 OK';
        $response['summary'] = 'success.';
        $response['data']    = $product;
        return $response;
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
        $img = base64_decode($img);
        Storage::disk('s3')->put("/image/$file_name",$img);
        $product = new product;
        $product->fill($request->all());
        $product->image = "$file_name"; //画像のファイル名をデータベースに保存
        $product->save();

        $response['status']  = '200 OK';
        $response['summary'] = 'success.';
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = product::find($id);
        if($this->checkData($product) != NULL) {
            $response = $this->checkData($product);
            return $response;
        }
        $path = "/image/{$product->image}"; //データベースのファイル名から画像を取得

        if(Storage::disk('s3')->exists($path)) {//画像データがストレージにあった場合はデータを取得
            $img = Storage::disk('s3')->get($path);
            $img = base64_encode($img);
        } else { //画像データがなければメッセージを返す
            $img = "商品画像は削除されました。";
        }

        $product->image = $img;
        $response['status']  = '200 OK';
        $response['summary'] = 'success.';
        $response['data']    = $product;
        return $response;
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
        if($this->checkData($product) != NULL) {
            $response = $this->checkData($product);
            return $response;
        }
        if($request->filled('image')){ //requestに画像ファイルが含まれていれば画像ファイルを更新
            $img = $request->image;
            if(Storage::disk('s3')->exists("/image/$product->image")) {
                Storage::disk('s3')->delete("/image/$product->image");
            }
            $file_name = $this->getFilename($img);
            Storage::disk('s3')->put("/image/$file_name", $img);
            $product->image = "$file_name";
        }
        foreach (array('name','desc','value') as $r){ //リクエストに含まれているデータのみ更新
            if($request->filled($r)) {
                $product->$r = $request->$r;
            }
        }
        $product->save();

        $response['status']  = '200 OK';
        $response['summary'] = 'success.';
        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = product::find($id);
        if($this->checkData($product) != NULL) {
            $response = $this->checkData($product);
            return $response;
        }
        if(Storage::disk('s3')->exists("/image/$product->image")) {
            Storage::disk('s3')->delete("/image/$product->image");
        }
        $product->delete();

        $response['status']  = '200 OK';
        $response['summary'] = 'success.';
        $response['data']    = [];
        return $response;
    }

    private function getFilename($img){
        $file_name = md5(uniqid(rand(), true)); //ファイル名の動的作成
        $img = base64_decode($img); //BASE64の画像をデコード
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

    private function checkData($data){
        if($data == NULL){
            $response['status']  = '400 Bad request';
            $response['summary'] = '存在しないIDです。';
            return $response;
        }
        return NULL;
    }
}
