<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    protected $table = 'products';
    protected $guarded = array('id');

    public static $rules = array(
        'image' => 'required',
        'name' => 'required',
        'desc' => 'required',
        'value' => 'required'
    );

    public function getData()
    {
        return $this->image . ':' .$this->name . ':' .$this->desc . ':' .$this->value;
    }

}
