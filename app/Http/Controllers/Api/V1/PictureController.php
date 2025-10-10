<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Picture;
use App\Enums\PictureTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PictureController extends Controller
{
    /**
     * Upload image for a specific model
     */
    public function uploadImage(Request $request, $modelType, $modelId)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            'caption' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate model type
        $validTypes = ['disaster', 'disaster_report', 'disaster_victim', 'disaster_aid'];
        if (!in_array($modelType, $validTypes)) {
            return response()->json([
                'message' => 'Invalid model type. Must be one of: ' . implode(', ', $validTypes)
            ], 400);
        }

        // Validate that the model exists
        $modelClass = $this->getModelClass($modelType);
        $model = $modelClass::find($modelId);
        
        if (!$model) {
            return response()->json([
                'message' => ucfirst(str_replace('_', ' ', $modelType)) . ' not found.'
            ], 404);
        }

        // Handle file upload
        $file = $request->file('image');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('pictures/' . $modelType, $fileName, 'public');

        // Create picture record
        $picture = Picture::create([
            'foreign_id' => $modelId,
            'type' => PictureTypeEnum::from($modelType),
            'caption' => $request->caption,
            'file_path' => $filePath,
            'mine_type' => $file->getMimeType(),
            'alt_text' => $request->alt_text,
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully.',
            'data' => [
                'id' => $picture->id,
                'foreign_id' => $picture->foreign_id,
                'type' => $picture->type->value,
                'caption' => $picture->caption,
                'file_path' => $picture->file_path,
                'url' => Storage::url($picture->file_path),
                'mine_type' => $picture->mine_type,
                'alt_text' => $picture->alt_text,
                'created_at' => $picture->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * Get all images for a specific model
     */
    public function getImages(Request $request, $modelType, $modelId)
    {
        // Validate model type
        $validTypes = ['disaster', 'disaster_report', 'disaster_victim', 'disaster_aid'];
        if (!in_array($modelType, $validTypes)) {
            return response()->json([
                'message' => 'Invalid model type. Must be one of: ' . implode(', ', $validTypes)
            ], 400);
        }

        // Validate that the model exists
        $modelClass = $this->getModelClass($modelType);
        $model = $modelClass::find($modelId);
        
        if (!$model) {
            return response()->json([
                'message' => ucfirst(str_replace('_', ' ', $modelType)) . ' not found.'
            ], 404);
        }

        $pictures = Picture::where('foreign_id', $modelId)
            ->where('type', PictureTypeEnum::from($modelType))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $pictures->map(function ($picture) {
                return [
                    'id' => $picture->id,
                    'foreign_id' => $picture->foreign_id,
                    'type' => $picture->type->value,
                    'caption' => $picture->caption,
                    'file_path' => $picture->file_path,
                    'url' => Storage::url($picture->file_path),
                    'mine_type' => $picture->mine_type,
                    'alt_text' => $picture->alt_text,
                    'created_at' => $picture->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ], 200);
    }

    /**
     * Get specific image
     */
    public function getImage(Request $request, $modelType, $modelId, $imageId)
    {
        // Validate model type
        $validTypes = ['disaster', 'disaster_report', 'disaster_victim', 'disaster_aid'];
        if (!in_array($modelType, $validTypes)) {
            return response()->json([
                'message' => 'Invalid model type. Must be one of: ' . implode(', ', $validTypes)
            ], 400);
        }

        $picture = Picture::where('foreign_id', $modelId)
            ->where('id', $imageId)
            ->where('type', PictureTypeEnum::from($modelType))
            ->first();

        if (!$picture) {
            return response()->json([
                'message' => 'Image not found.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $picture->id,
                'foreign_id' => $picture->foreign_id,
                'type' => $picture->type->value,
                'caption' => $picture->caption,
                'file_path' => $picture->file_path,
                'url' => Storage::url($picture->file_path),
                'mine_type' => $picture->mine_type,
                'alt_text' => $picture->alt_text,
                'created_at' => $picture->created_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Update image details
     */
    public function updateImage(Request $request, $modelType, $modelId, $imageId)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate model type
        $validTypes = ['disaster', 'disaster_report', 'disaster_victim', 'disaster_aid'];
        if (!in_array($modelType, $validTypes)) {
            return response()->json([
                'message' => 'Invalid model type. Must be one of: ' . implode(', ', $validTypes)
            ], 400);
        }

        $picture = Picture::where('foreign_id', $modelId)
            ->where('id', $imageId)
            ->where('type', PictureTypeEnum::from($modelType))
            ->first();

        if (!$picture) {
            return response()->json([
                'message' => 'Image not found.'
            ], 404);
        }

        $picture->update($request->only(['caption', 'alt_text']));

        return response()->json([
            'message' => 'Image updated successfully.',
            'data' => [
                'id' => $picture->id,
                'foreign_id' => $picture->foreign_id,
                'type' => $picture->type->value,
                'caption' => $picture->caption,
                'file_path' => $picture->file_path,
                'url' => Storage::url($picture->file_path),
                'mine_type' => $picture->mine_type,
                'alt_text' => $picture->alt_text,
                'updated_at' => $picture->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Delete image
     */
    public function deleteImage(Request $request, $modelType, $modelId, $imageId)
    {
        // Validate model type
        $validTypes = ['disaster', 'disaster_report', 'disaster_victim', 'disaster_aid'];
        if (!in_array($modelType, $validTypes)) {
            return response()->json([
                'message' => 'Invalid model type. Must be one of: ' . implode(', ', $validTypes)
            ], 400);
        }

        $picture = Picture::where('foreign_id', $modelId)
            ->where('id', $imageId)
            ->where('type', PictureTypeEnum::from($modelType))
            ->first();

        if (!$picture) {
            return response()->json([
                'message' => 'Image not found.'
            ], 404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($picture->file_path)) {
            Storage::disk('public')->delete($picture->file_path);
        }

        $picture->delete();

        return response()->json([
            'message' => 'Image deleted successfully.'
        ], 200);
    }

    /**
     * Get model class based on type
     */
    private function getModelClass($modelType)
    {
        $modelMap = [
            'disaster' => \App\Models\Disaster::class,
            'disaster_report' => \App\Models\DisasterReport::class,
            'disaster_victim' => \App\Models\DisasterVictim::class,
            'disaster_aid' => \App\Models\DisasterAid::class,
        ];

        return $modelMap[$modelType];
    }
}
