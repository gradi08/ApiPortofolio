<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    // 🔓 PUBLIC : Infos sur toi (tu peux aussi les mettre en base si tu veux les modifier via admin)
    public function index()
{
    return response()->json([
        'success' => true,
        'data' => [
            'name' => 'Gradi N\'Chaki',
            'title' => 'Développeur Full Stack & Graphiste',
            'bio' => 'Passionné par le développement web, je connais plusieurs langages de programmation et outils, et je suis motivé par la résolution de problèmes et l\'optimisation de l\'expérience utilisateur.',
            'email' => 'gradinchaki08@gmail.com',
            'location' => 'B3j 767/ Salongo sud / Lemba',
            'social_links' => [
                'github' => 'https://github.com/gradi08',
                'linkedin' => 'https://www.linkedin.com/in/gradi-n-chaki-0b2a60359',
                'whatsapp' => '243973439644'
            ],
            'skills' => [
                'Langages & Frameworks' => ['Laravel', 'PHP', 'JavaScript', 'React', 'Python', 'Java','Bootstrap','Django'],
                'Outils & Design' => ['Adobe Photoshop', 'Adobe Illustrator', 'Git', 'GitHub', 'Microsoft Excel'],
                'Base de données' => ['MySQL', 'PostgreSQL'],
            ]
        ]
    ]);
}
}