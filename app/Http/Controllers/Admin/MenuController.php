<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('category');

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $menuItems = $query->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Admin/Menu/Index', [
            'menuItems' => $menuItems,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'image_file'   => 'nullable|image|max:2048',
            'is_available' => 'boolean',
        ]);

        $data = $request->only(['category_id', 'name', 'description', 'price', 'is_available']);
        $data['is_available'] = $request->boolean('is_available', true);

        if ($request->hasFile('image_file')) {
            $path = Storage::disk('supabase')->put('menu', $request->file('image_file'));
            $data['image'] = Storage::disk('supabase')->url($path);
        }

        MenuItem::create($data);

        return redirect()->route('menu.index')->with('success', 'Menu item created successfully.');
    }

    public function update(Request $request, MenuItem $menu)
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'image_file'   => 'nullable|image|max:2048',
            'is_available' => 'boolean',
        ]);

        $data = $request->only(['category_id', 'name', 'description', 'price', 'is_available']);
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image_file')) {
            if ($menu->image) {
                // Delete old image
                $oldPath = str_replace(Storage::disk('supabase')->url(''), '', $menu->image);
                Storage::disk('supabase')->delete($oldPath);
            }
            $file = $request->file('image_file');
            $path = Storage::disk('supabase')->putFileAs(
                'menu',
                $file,
                $file->hashName(),
                [
                    'ContentType' => $file->getMimeType(),
                    'visibility' => 'public',
                ]
            );
            $data['image'] = Storage::disk('supabase')->url($path);
        }

        $menu->update($data);

        return redirect()->route('menu.index')->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menu)
    {
        if ($menu->image) {
            $oldPath = str_replace(Storage::disk('supabase')->url(''), '', $menu->image);
            Storage::disk('supabase')->delete($oldPath);
        }
        $menu->delete();

        return redirect()->route('menu.index')->with('success', 'Menu item deleted successfully.');
    }
}
