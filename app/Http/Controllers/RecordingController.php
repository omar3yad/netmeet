<?php

namespace App\Http\Controllers;

use App\Jobs\UploadVideoJob;
use Illuminate\Http\Request;
use App\Models\Recording;
use Illuminate\Support\Facades\Auth;


class RecordingController extends Controller
{
    public function index(Request $request)
    {
        // ?? ??? ????? ????? ???? ??????? ???????? ???? ???? ???? ?? ??? ?????
        if (Auth::check()) {
            $username = Auth::user()->username;
            $recordings = Recording::where('username', $username)->orderBy('recorded_at', 'desc')->get();
        } else {
            // ?? ?? ????? ?????? username ?? session ?? ???? ?? ?????????
            $recordings = Recording::orderBy('recorded_at', 'desc')->get();
        }

        return view('recordings.index', compact('recordings'));
    }

public function getRecordingsByUser(Request $request)
{
    $username = $request->query('username'); 
    if (!$username) {
        $username = session('username'); 
    }

    // ??? ??????????
    $recordings = Recording::where('username', $username)
                           ->orderBy('recorded_at', 'desc')
                           ->get();

    // ????? ???????? ??? JSON
    return response()->json([
        'status' => 'ok',
        'data' => $recordings
    ]);
}


    // ??? ??????? ??????
    public function uploadVideo(Request $request)
    {
        // Validate ?????
        $request->validate([
            'file' => 'required|file|mimes:webm,mp4,mov|max:51200', // 50MB
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        try {
            $file = $request->file('file');
            $fileName = time() . "_" . $file->getClientOriginalName();

            // ??? ????? ?????? ?? public/videos
            $file->move(public_path('videos'), $fileName);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }

        // username
        $username = Auth::check() ? Auth::user()->username : ($request->input('username') ?? 'Guest');
        // Force authenticated username if logged in (override any frontend value)
        if (Auth::check()) { $username = Auth::user()->username; }

        // ??? ???????? ?? DB
        $recording = Recording::create([
            'username' => $username,
            'file_name' => $fileName,
            'file_path' => 'videos/' . $fileName,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Uploaded OK',
            'file' => $fileName,
            'recording_id' => $recording->id
        ], 200);
    }

    // Handle chunk uploads for real-time cloud recording (appending chunks directly to final file)
    public function uploadVideoChunk(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
                'session_id' => 'required|string',
                'chunk_index' => 'required|integer',
                'filename' => 'required|string',
            ]);

            $sessionId = $request->input('session_id');
            $chunkIndex = (int) $request->input('chunk_index');
            $originalFileName = $request->input('filename');

            // Sanitize session_id to prevent any directory traversal attacks
            $sessionId = preg_replace('/[^a-zA-Z0-9_-]/', '', $sessionId);

            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'No file chunk uploaded'], 400);
            }

            $file = $request->file('file');

            // Generate or retrieve the unique filename for this session
            $cacheKeyFile = 'rec_file_' . $sessionId;
            $fileName = cache()->get($cacheKeyFile);

            if ($chunkIndex === 0 || !$fileName) {
                // If it's chunk 0 or filename is not in cache, create/register it
                $fileName = time() . "_" . preg_replace('/[^a-zA-Z0-9_.-]/', '', $originalFileName);
                cache()->forever($cacheKeyFile, $fileName);
                
                // Get username
                $username = Auth::check() ? Auth::user()->username : ($request->input('username') ?? 'Guest');
                if (Auth::check()) { 
                    $username = Auth::user()->username; 
                }

                // Check if the recording already exists in DB (to handle potential retries of chunk 0)
                $recording = Recording::where('file_name', $fileName)->first();
                if (!$recording) {
                    Recording::create([
                        'username' => $username,
                        'file_name' => $fileName,
                        'file_path' => 'videos/' . $fileName,
                        'recorded_at' => now(),
                    ]);
                }
            }

            $finalPath = public_path('videos/' . $fileName);

            // Deduplication logic using last appended chunk index
            $cacheKeyLastChunk = 'rec_last_chunk_' . $sessionId;
            $lastChunk = (int) cache()->get($cacheKeyLastChunk, -1);

            if ($chunkIndex <= $lastChunk) {
                // Duplicate chunk from client retry, ignore but return success
                return response()->json(['status' => 'ok', 'chunk' => $chunkIndex, 'message' => 'Duplicate ignored'], 200);
            }

            // Append chunk data to the final file
            $out = fopen($finalPath, $chunkIndex === 0 ? 'wb' : 'ab');
            if (!$out) {
                throw new \Exception("Could not open video file for writing");
            }
            fwrite($out, file_get_contents($file->getRealPath()));
            fclose($out);

            // Update the last successfully written chunk index
            cache()->put($cacheKeyLastChunk, $chunkIndex, 86400); // cache for 24 hours

            return response()->json(['status' => 'ok', 'chunk' => $chunkIndex], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Finalize chunked uploads (simply clean up session cache and return success)
    public function finalizeVideoUpload(Request $request)
    {
        try {
            $request->validate([
                'session_id' => 'required|string',
                'total_chunks' => 'required|integer',
                'filename' => 'required|string',
            ]);
    
            $sessionId = $request->input('session_id');

            // Sanitize session_id
            $sessionId = preg_replace('/[^a-zA-Z0-9_-]/', '', $sessionId);
            
            // Clean up cache keys
            $fileName = cache()->get('rec_file_' . $sessionId);
            cache()->forget('rec_file_' . $sessionId);
            cache()->forget('rec_last_chunk_' . $sessionId);

            return response()->json([
                'message' => 'Uploaded OK',
                'file' => $fileName
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Finalize failed: ' . $e->getMessage()], 500);
        }
    }


    // Delete a recording (file + DB row)
    public function destroy($id)
    {   
        $recording = \App\Models\Recording::find($id);
        if (!$recording) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Optional: check that the recording belongs to the current user
        if (\Illuminate\Support\Facades\Auth::check()) {
            $username = \Illuminate\Support\Facades\Auth::user()->username;
            if ($recording->username !== $username) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }
        // Delete the physical file
        $filePath = public_path($recording->file_path);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Delete the DB row
        $recording->delete();

        return response()->json(['success' => true]);
    }
}