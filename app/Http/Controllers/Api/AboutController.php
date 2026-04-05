<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    // 🔓 PUBLIC : Infos sur toi (tu peux aussi les mettre en base si tu veux les modifier via admin)
    public function index()
    {
        // Tu peux hardcoder ici ou créer une table settings
        return response()->json([
            'success' => true,
            'data' => [
                'name' => 'Gradi N\'chaki',
                'title' => 'Développeur Full Stack & Graphiste',
                'bio' => 'Passionné par le développement web, je connais plusieurs langages de
                            programmation et outils, et je suis motivé par la résolution de problèmes et
                            l’optimisation de l’expérience utilisateu',
                'email' => 'ton@email.com',
                'location' => 'Ta ville',
                'social_links' => [
                    'github' => 'https://github.com/tonpseudo',
                    'linkedin' => 'https://linkedin.com/in/tonpseudo',
                    'twitter' => 'https://twitter.com/tonpseudo',
                ],
                'skills' => [
                    'Frontend' => ['React', 'Vue.js', 'Tailwind CSS', 'TypeScript'],
                    'Backend' => ['Laravel', 'Node.js', 'PostgreSQL', 'Redis'],
                    'DevOps' => ['Docker', 'AWS', 'CI/CD'],
                    'Design' => ['Photoshop', 'Illustrator'],
                ]
            ]
        ]);
    }
}