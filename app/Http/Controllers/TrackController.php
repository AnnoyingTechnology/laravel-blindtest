<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrackController extends Controller
{
    public function getTrack($id)
    {
        // Find the track by ID
        $track = Track::findOrFail($id);

        // Get the file path from storage
        $filePath = 'music/' . $track->file;


        // Check if the file exists
        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Get the full path to the file
        $fullPath = Storage::disk('local')->path($filePath);

        // Return the file as a response
        return response()->file($fullPath);

    }
}
