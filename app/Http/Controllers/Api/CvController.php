<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CvFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CvController extends Controller
{
    // 🔓 PUBLIC : Télécharger le CV actif
    public function download()
    {
        $cv = CvFile::active();

        if (!$cv) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun CV disponible'
            ], 404);
        }

        return Storage::disk('public')->download(
            $cv->file_path, 
            $cv->original_name
        );
    }

    // 🔓 PUBLIC : Vérifier si un CV existe (pour afficher/masquer le bouton)
    public function check()
    {
        $cv = CvFile::active();

        return response()->json([
            'success' => true,
            'has_cv' => !!$cv,
            'filename' => $cv ? $cv->original_name : null
        ]);
    }

    // 🔒 ADMIN : Upload un nouveau CV (désactive l'ancien)
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cv' => 'required|file|mimes:pdf|max:5120', // Max 5MB, PDF uniquement
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Désactiver l'ancien CV
        CvFile::where('is_active', true)->update(['is_active' => false]);

        $file = $request->file('cv');
        $filename = 'cv_' . time() . '.pdf';
        $path = $file->storeAs('cv', $filename, 'public');

        $cv = CvFile::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'CV uploadé avec succès',
            'data' => $cv
        ]);
    }

    // 🔒 ADMIN : Liste historique des CV
    public function index()
    {
        $cvs = CvFile::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $cvs
        ]);
    }
}