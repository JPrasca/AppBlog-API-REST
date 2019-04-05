<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;
use App\User;

class PruebaController extends Controller
{
    public function index() {
        $titulo = 'Animales';
        $animales = ['Perro', 'Gato', 'Tigre'];
        
        return view('prueba.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }
    
    public function testOrm() {
        
        
        foreach ($posts as $post) {
            echo "<h1>". $post->title . "</h1>";
            echo "<span>Publicado por: {$post->user->name}</span>";
        }
        die();
    }
}
