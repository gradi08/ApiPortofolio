<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    // 🔓 PUBLIC : Ajouter un commentaire (en attente de modération)
    public function store(Request $request, $projectId)
    {
        $validator = Validator::make($request->all(), [
            'visitor_name' => 'required|string|max:100',
            'visitor_title' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::findOrFail($projectId);

        $comment = $project->allComments()->create([
            'visitor_name' => $request->visitor_name,
            'visitor_title' => $request->visitor_title,
            'content' => $request->content,
            'is_approved' => false, // En attente de modération
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commentaire soumis, en attente de modération',
            'data' => $comment
        ], 201);
    }

    // 🔒 ADMIN : Liste tous les commentaires (pour modération)
    public function index()
    {
        $comments = Comment::with('project')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    // 🔒 ADMIN : Approuver un commentaire
    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Commentaire approuvé'
        ]);
    }

    // PUBLIC : Commentaires d'un projet avec pagination
public function getProjectComments($projectId, Request $request)
{
    $perPage = $request->get('per_page', 5);
    
    $comments = Comment::where('project_id', $projectId)
        ->where('is_approved', true)
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

    return response()->json([
        'success' => true,
        'data' => $comments
    ]);
}

    // 🔒 ADMIN : Rejeter/Supprimer un commentaire
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Commentaire supprimé'
        ]);
    }
}