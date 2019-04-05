<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    
    //1...* Relation
    public function posts() {
        return $this->hasMany('App\Post');

    }

}
