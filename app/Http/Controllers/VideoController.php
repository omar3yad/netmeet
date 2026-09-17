<?php

namespace App\Http\Controllers;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class VideoController extends Controller
{
    public function upload(Request $request)
    {
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
        $username = Auth::check()
            ? Auth::user()->name
            : ($request->input('username') ?? 'Guest');

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
}