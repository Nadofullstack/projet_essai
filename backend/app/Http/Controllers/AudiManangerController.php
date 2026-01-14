<?php

namespace App\Http\Controllers;

use App\Models\AudiManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AudiManangerController extends Controller
{
    /**
     * Afficher la liste des enregistrements audio
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $recordings = AudiManager::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recordings
        ]);
    }

    /**
     * Afficher un enregistrement spécifique
     */
    public function show($id): JsonResponse
    {
        $recording = AudiManager::find($id);

        if (!$recording || $recording->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Enregistrement non trouvé ou non autorisé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $recording
        ]);
    }

    /**
     * Créer un nouvel enregistrement audio
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audio_file' => 'required|file|mimes:mp3,wav,m4a,ogg|max:51200', // 50MB max
            'duration' => 'required|numeric|min:1',
            'quality' => 'required|in:low,medium,high,studio',
            'format' => 'required|in:mp3,wav,m4a,ogg',
            'category' => 'nullable|in:voice_note,meeting,lecture,podcast,music,other'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();

        // Gérer le fichier audio
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $path = $file->store('audio_recordings', 'public');
            $data['file_path'] = $path;
            $data['file_size'] = $file->getSize();
            $data['file_name'] = $file->getClientOriginalName();
        }

        $recording = AudiManager::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Enregistrement créé avec succès',
            'data' => $recording
        ], 201);
    }

    /**
     * Mettre à jour un enregistrement
     */
    public function update(Request $request, $id): JsonResponse
    {
        $recording = AudiManager::find($id);

        if (!$recording || $recording->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Enregistrement non trouvé ou non autorisé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|in:voice_note,meeting,lecture,podcast,music,other',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $recording->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Enregistrement mis à jour avec succès',
            'data' => $recording->fresh()
        ]);
    }

    /**
     * Supprimer un enregistrement
     */
    public function destroy($id): JsonResponse
    {
        $recording = AudiManager::find($id);

        if (!$recording || $recording->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Enregistrement non trouvé ou non autorisé'
            ], 404);
        }

        // Supprimer le fichier physique
        if ($recording->file_path) {
            Storage::disk('public')->delete($recording->file_path);
        }

        $recording->delete();

        return response()->json([
            'success' => true,
            'message' => 'Enregistrement supprimé avec succès'
        ]);
    }

    /**
     * Télécharger un enregistrement
     */
    public function download($id): JsonResponse
    {
        $recording = AudiManager::find($id);

        if (!$recording || $recording->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Enregistrement non trouvé ou non autorisé'
            ], 404);
        }

        $filePath = storage_path('app/public/' . $recording->file_path);
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier audio non trouvé'
            ], 404);
        }

        return response()->download($filePath, $recording->file_name);
    }

    /**
     * Obtenir les enregistrements par catégorie
     */
    public function byCategory($category): JsonResponse
    {
        $user = Auth::user();
        $recordings = AudiManager::where('user_id', $user->id)
            ->where('category', $category)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recordings
        ]);
    }

    /**
     * Obtenir les statistiques des enregistrements
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        $totalRecordings = AudiManager::where('user_id', $user->id)->count();
        $totalDuration = AudiManager::where('user_id', $user->id)->sum('duration');
        $totalSize = AudiManager::where('user_id', $user->id)->sum('file_size');
        
        $recordingsByCategory = AudiManager::where('user_id', $user->id)
            ->selectRaw('category, COUNT(*) as count, SUM(duration) as total_duration')
            ->groupBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_recordings' => $totalRecordings,
                'total_duration' => $totalDuration,
                'total_size' => $totalSize,
                'by_category' => $recordingsByCategory
            ]
        ]);
    }

    /**
     * Rechercher des enregistrements
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'category' => 'nullable|in:voice_note,meeting,lecture,podcast,music,other'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $query = $request->query;
        $category = $request->category;

        $recordings = AudiManager::where('user_id', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('tags', 'LIKE', "%{$query}%");
            })
            ->when($category, function ($q) use ($category) {
                return $q->where('category', $category);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recordings
        ]);
    }
}
