<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    //

    //*...1 relation
    public function user () {
        
        return $this->belongsTo('App\User', 'user_id');
    }

    //*...1 relation
    public function category () {
        
        return $this->belongsTo('App\Category', 'category_id');
    }
}
