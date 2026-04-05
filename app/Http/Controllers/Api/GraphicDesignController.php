<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GraphicDesign;
use App\Models\GraphicDesignImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GraphicDesignController extends Controller
{
    // PUBLIC : Liste avec filtres
    public function index(Request $request)
    {
        try {
            $category = $request->get('category');
            
            $designs = GraphicDesign::with('images')
                ->when($category, function($query, $category) {
                    return $query->where('category', $category);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(12);

            return response()->json([
                'success' => true,
                'data' => $designs
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur index graphic-designs:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    // PUBLIC : Détail
    public function show($id)
    {
        try {
            $design = GraphicDesign::with('images')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $design
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur show graphic-design:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Création non trouvée'
            ], 404);
        }
    }

    // ADMIN : Créer avec minimum 3 images
    public function store(Request $request)
{
    Log::info('=== DEBUT CREATION GRAPHIC DESIGN ===');
    Log::info('Headers:', $request->headers->all());
    Log::info('Content-Type:', [$request->header('Content-Type')]);
    
    try {
        // Validation des champs texte uniquement
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:logo,affiche,flyer,carte_visite,banniere,autre',
            'tags' => 'nullable',
        ]);

        if ($validator->fails()) {
            Log::error('Validation échouée:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // CORRECTION : Récupérer TOUTES les clés de fichiers
        $allFiles = $request->allFiles();
        Log::info('All files:', array_keys($allFiles));
        
        $images = [];
        
        // Méthode 1 : Parcourir toutes les clés qui commencent par "images."
        foreach ($allFiles as $key => $file) {
            Log::info("Clé trouvée: {$key}", [is_array($file) ? 'array' : 'single']);
            
            if (strpos($key, 'images.') === 0) {
                if (is_array($file)) {
                    $images = array_merge($images, $file);
                } else {
                    $images[] = $file;
                }
            }
        }
        
        // Méthode 2 : Si toujours vide, essayer $_FILES directement
        if (empty($images) && !empty($_FILES)) {
            Log::info('$_FILES:', $_FILES);
            
            foreach ($_FILES as $key => $fileInfo) {
                if (strpos($key, 'images') === 0) {
                    if (is_array($fileInfo['name'])) {
                        // Plusieurs fichiers
                        $count = count($fileInfo['name']);
                        for ($i = 0; $i < $count; $i++) {
                            if ($fileInfo['error'][$i] === UPLOAD_ERR_OK) {
                                $images[] = new \Illuminate\Http\UploadedFile(
                                    $fileInfo['tmp_name'][$i],
                                    $fileInfo['name'][$i],
                                    $fileInfo['type'][$i],
                                    $fileInfo['error'][$i],
                                    true
                                );
                            }
                        }
                    } else {
                        // Fichier unique
                        if ($fileInfo['error'] === UPLOAD_ERR_OK) {
                            $images[] = new \Illuminate\Http\UploadedFile(
                                $fileInfo['tmp_name'],
                                $fileInfo['name'],
                                $fileInfo['type'],
                                $fileInfo['error'],
                                true
                            );
                        }
                    }
                }
            }
        }

        Log::info('Nombre d\'images trouvées:', ['count' => count($images)]);

        if (count($images) < 3) {
            return response()->json([
                'success' => false,
                'errors' => ['images' => ['Veuillez fournir au moins 3 images. Reçues: ' . count($images)]]
            ], 422);
        }

        // Validation de chaque image
        foreach ($images as $idx => $image) {
            if (!$image->isValid()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['images' => ["L'image #" . ($idx + 1) . " est invalide."]]
                ], 422);
            }
            
            $ext = strtolower($image->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                return response()->json([
                    'success' => false,
                    'errors' => ['images' => ["L'image #" . ($idx + 1) . " doit être JPG, PNG ou WebP ({$ext} reçu)."]]
                ], 422);
            }
            
            if ($image->getSize() > 2048 * 1024) {
                return response()->json([
                    'success' => false,
                    'errors' => ['images' => ["L'image #" . ($idx + 1) . " dépasse 2Mo."]]
                ], 422);
            }
        }

        // Gérer tags
        $tags = $request->tags;
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $tags = ($decoded !== null && is_array($decoded)) ? $decoded : array_map('trim', explode(',', $tags));
        }
        $tags = array_filter(is_array($tags) ? $tags : []);

        // Créer le design
        $design = GraphicDesign::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'tags' => $tags,
        ]);

        Log::info('GraphicDesign créé:', ['id' => $design->id]);

        // Sauvegarder les images
        foreach ($images as $index => $image) {
            $path = $image->store('designs', 'public');
            Log::info("Image {$index} sauvegardée:", ['path' => $path]);
            
            GraphicDesignImage::create([
                'graphic_design_id' => $design->id,
                'image_path' => $path,
                'order' => $index,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Création graphique ajoutée avec succès',
            'data' => $design->load('images')
        ], 201);

    } catch (\Exception $e) {
        Log::error('Exception store:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur: ' . $e->getMessage()
        ], 500);
    }
}

    // ADMIN : Modifier
    public function update(Request $request, $id)
    {
        try {
            $design = GraphicDesign::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'category' => 'sometimes|in:logo,affiche,flyer,carte_visite,banniere,autre',
                'tags' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer nouvelles images
            $newImages = [];
            $index = 0;
            while ($request->hasFile("new_images.{$index}")) {
                $newImages[] = $request->file("new_images.{$index}");
                $index++;
            }
            
            if (empty($newImages) && $request->hasFile('new_images')) {
                $files = $request->file('new_images');
                $newImages = is_array($files) ? $files : [$files];
            }

            // Vérifier suppression images
            $deleteIds = $request->input('delete_image_ids', []);
            if (!is_array($deleteIds)) {
                $deleteIds = json_decode($deleteIds, true) ?? [];
            }

            if (count($deleteIds) > 0) {
                $remainingImages = $design->images()->count() - count($deleteIds);
                if (($remainingImages + count($newImages)) < 3) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Une création doit avoir minimum 3 photos.'
                    ], 422);
                }

                foreach ($deleteIds as $imageId) {
                    $image = $design->images()->find($imageId);
                    if ($image) {
                        Storage::disk('public')->delete($image->image_path);
                        $image->delete();
                    }
                }
            }

            // Update data
            $updateData = $request->only(['title', 'description', 'category']);
            
            if ($request->has('tags')) {
                $tags = $request->tags;
                if (is_string($tags)) {
                    $decoded = json_decode($tags, true);
                    $tags = ($decoded !== null && is_array($decoded)) ? $decoded : array_map('trim', explode(',', $tags));
                } else {
                    $tags = $tags;
                }
                $updateData['tags'] = array_filter(is_array($tags) ? $tags : []);
            }

            $design->update($updateData);

            // Ajouter nouvelles images
            if (count($newImages) > 0) {
                $lastOrder = $design->images()->max('order') ?? -1;
                
                foreach ($newImages as $index => $image) {
                    $path = $image->store('designs', 'public');
                    GraphicDesignImage::create([
                        'graphic_design_id' => $design->id,
                        'image_path' => $path,
                        'order' => $lastOrder + $index + 1,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Création graphique mise à jour',
                'data' => $design->load('images')
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur update graphic-design:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // ADMIN : Supprimer
    public function destroy($id)
    {
        try {
            $design = GraphicDesign::findOrFail($id);

            foreach ($design->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $design->delete();

            return response()->json([
                'success' => true,
                'message' => 'Création graphique supprimée'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur destroy graphic-design:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur suppression'
            ], 500);
        }
    }

    // PUBLIC : Catégories disponibles
    public function categories()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'logo' => 'Logos',
                'affiche' => 'Affiches',
                'flyer' => 'Flyers',
                'carte_visite' => 'Cartes de visite',
                'banniere' => 'Bannières',
                'autre' => 'Autres'
            ]
        ]);
    }
}