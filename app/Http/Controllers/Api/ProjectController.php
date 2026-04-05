<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    // 🔓 PUBLIC : Liste tous les projets avec leurs images et commentaires approuvés
    public function index()
    {
        $projects = Project::with(['images', 'comments'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    // 🔓 PUBLIC : Détail d'un projet
    public function show($id)
    {
        $project = Project::with(['images', 'comments'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $project
        ]);
    }

    // 🔒 ADMIN : Créer un projet (minimum 3 images requises)
    public function store(Request $request)
{
    \Log::info('Données reçues:', $request->all());
    \Log::info('Fichiers:', $request->file('images'));

    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'technologies' => 'required', // Peut être string JSON ou array
        'project_url' => 'nullable|url',
        'github_url' => 'nullable|url',
        'images' => 'required|array|min:3',
        'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    if ($validator->fails()) {
        \Log::error('Validation failed:', $validator->errors()->toArray());
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // Gérer technologies (JSON string ou array)
    $technologies = $request->technologies;
    if (is_string($technologies)) {
        $technologies = json_decode($technologies, true) ?? explode(',', $technologies);
    }

    $project = Project::create([
        'title' => $request->title,
        'description' => $request->description,
        'technologies' => $technologies,
        'project_url' => $request->project_url,
        'github_url' => $request->github_url,
    ]);

    \Log::info('Projet créé:', ['id' => $project->id]);

    // Sauvegarder les images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('projects', 'public');
            \Log::info('Image sauvegardée:', ['path' => $path]);
            
            $project->images()->create([
                'image_path' => $path,
                'order' => $index,
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Projet créé avec succès',
        'data' => $project->load('images')
    ], 201);
}

    // 🔒 ADMIN : Modifier un projet
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'technologies' => 'sometimes|array',
            'project_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'new_images' => 'sometimes|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'delete_image_ids' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier qu'il restera au moins 3 images après suppression
        if ($request->has('delete_image_ids')) {
            $remainingImages = $project->images()->count() - count($request->delete_image_ids);
            $newImagesCount = $request->has('new_images') ? count($request->file('new_images')) : 0;
            
            if (($remainingImages + $newImagesCount) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un projet doit avoir minimum 3 photos. Ajoutez plus d\'images avant de supprimer.'
                ], 422);
            }

            // Supprimer les images sélectionnées
            foreach ($request->delete_image_ids as $imageId) {
                $image = $project->images()->find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        // Mettre à jour les champs
        $project->update($request->only([
            'title', 'description', 'technologies', 'project_url', 'github_url'
        ]));

        // Ajouter nouvelles images
        if ($request->hasFile('new_images')) {
            $lastOrder = $project->images()->max('order') ?? -1;
            
            foreach ($request->file('new_images') as $index => $image) {
                $path = $image->store('projects', 'public');
                
                $project->images()->create([
                    'image_path' => $path,
                    'order' => $lastOrder + $index + 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Projet mis à jour',
            'data' => $project->load('images')
        ]);
    }

    // 🔒 ADMIN : Supprimer un projet
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Supprimer les images du storage
        foreach ($project->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Projet supprimé'
        ]);
    }
}