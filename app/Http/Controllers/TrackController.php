<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrackController extends Controller
{
    public function getTrack($id)
    {

        // Find the track by ID
        $track = Track::findOrFail($id);

        // Get the file path from storage
        $filePath = 'music/'.$track->file;

        // Check if the file exists
        if (! Storage::disk('local')->exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Get the full path to the file
        $fullPath = Storage::disk('local')->path($filePath);

        // Get file size
        $fileSize = filesize($fullPath);

        // Get MIME type
        $mimeType = mime_content_type($fullPath);

        // Set headers
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
        ];

        // Parse the Range header if present (single byte ranges, RFC 9110;
        // other range units and multi-range requests are ignored per spec)
        $start = 0;
        $end = $fileSize - 1;
        $isPartial = false;

        if ($range = request()->header('Range')) {
            if (preg_match('/^bytes=(\d*)-(\d*)$/', $range, $matches)) {
                [$rawStart, $rawEnd] = [$matches[1], $matches[2]];

                if ($rawStart === '' && $rawEnd !== '') {
                    // suffix range: the last N bytes
                    $suffix = min((int) $rawEnd, $fileSize);
                    $start = $fileSize - $suffix;
                } elseif ($rawStart !== '') {
                    $start = (int) $rawStart;
                    $end = $rawEnd !== '' ? min((int) $rawEnd, $fileSize - 1) : $fileSize - 1;
                }

                // unsatisfiable range: start beyond the file or inverted bounds
                if ($start > $end || $start >= $fileSize) {
                    return response('', 416, ['Content-Range' => "bytes */$fileSize"]);
                }

                if ($start > 0 || $end < $fileSize - 1) {
                    $isPartial = true;
                    $headers['Content-Length'] = $end - $start + 1;
                    $headers['Content-Range'] = "bytes $start-$end/$fileSize";
                }
            }
        }

        // Return streamed response
        return new StreamedResponse(function () use ($fullPath, $start, $end) {
            $handle = fopen($fullPath, 'rb');
            if ($start > 0) {
                fseek($handle, $start);
            }
            $buffer = 1024 * 8;
            $currentPosition = $start;

            while (! feof($handle) && $currentPosition <= $end) {
                $length = min($buffer, $end - $currentPosition + 1);
                echo fread($handle, $length);
                $currentPosition += $length;
                flush();
            }

            fclose($handle);
        }, $isPartial ? 206 : 200, $headers);

    }
}
