<?php

namespace App\Http\Controllers;

use App\Models\LandImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $landImages = LandImage::latest()->get();
        return view('land-images.index', compact('landImages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('land-images.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'land_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'land_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['land_name', 'notes']);

        if ($request->hasFile('land_image')) {
            $imagePath = $request->file('land_image')->store('land-images', 'public');
            $data['land_image'] = $imagePath;
        }

        LandImage::create($data);

        return redirect()->route('land-images.index')
            ->with('success', 'Land image created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LandImage $landImage)
    {
        return view('land-images.show', compact('landImage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandImage $landImage)
    {
        return view('land-images.edit', compact('landImage'));
    }

    public function update(Request $request, LandImage $landImage)
    {
        $request->validate([
            'land_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'land_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['land_name', 'notes']);

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image == '1') {
            if ($landImage->land_image && Storage::disk('public')->exists($landImage->land_image)) {
                Storage::disk('public')->delete($landImage->land_image);
            }
            $data['land_image'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('land_image')) {
            // Delete old image
            if ($landImage->land_image && Storage::disk('public')->exists($landImage->land_image)) {
                Storage::disk('public')->delete($landImage->land_image);
            }

            $imagePath = $request->file('land_image')->store('land-images', 'public');
            $data['land_image'] = $imagePath;
        }

        $landImage->update($data);

        return redirect()->route('land-images.index')
            ->with('success', 'Land image updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LandImage $landImage)
    {
        // Delete image file if exists
        if ($landImage->land_image && Storage::disk('public')->exists($landImage->land_image)) {
            Storage::disk('public')->delete($landImage->land_image);
        }

        $landImage->delete();

        return redirect()->route('land-images.index')
            ->with('success', 'Land image moved to trash.');
    }

    /**
     * Show trashed land images
     */
    public function trashed()
    {
        $landImages = LandImage::onlyTrashed()->latest()->get();
        return view('land-images.trashed', compact('landImages'));
    }

    /**
     * Restore from trash
     */
    public function restore($id)
    {
        $landImage = LandImage::onlyTrashed()->findOrFail($id);
        $landImage->restore();

        return redirect()->route('land-images.trashed')
            ->with('success', 'Land image restored successfully.');
    }

    /**
     * Permanently delete
     */
    public function forceDelete($id)
    {
        $landImage = LandImage::onlyTrashed()->findOrFail($id);

        // Delete image file if exists
        if ($landImage->land_image && Storage::disk('public')->exists($landImage->land_image)) {
            Storage::disk('public')->delete($landImage->land_image);
        }

        $landImage->forceDelete();

        return redirect()->route('land-images.trashed')
            ->with('success', 'Land image permanently deleted.');
    }
}