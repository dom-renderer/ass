<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Font;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();
        $fonts = Font::orderBy('title')->get();
        return view('settings.edit', compact('setting', 'fonts'));
    }

    public function update(Request $request)
    {
        $setting = Setting::first();

        $request->validate([
            'maintenance_mode' => 'required|boolean',
            'app_name' => 'required|string|max:255',
            'app_description' => 'required|string|max:2000',
            'logo' => ($setting && $setting->logo ? 'nullable' : 'required') . '|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'app_logo' => ($setting && $setting->app_logo ? 'nullable' : 'required') . '|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:width=1024,height=1024',
            'favicon' => ($setting && $setting->favicon ? 'nullable' : 'required') . '|file|mimes:ico,png,jpg,jpeg,svg,webp|max:1024',
            'primary_theme_colour' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'primary_font_colour' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'default_font_id' => 'nullable|exists:fonts,id',
        ]);

        $logoPath = $setting ? $setting->logo : null;
        $appLogoPath = $setting ? $setting->app_logo : null;
        $faviconPath = $setting ? $setting->favicon : null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('settings/branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('settings/branding', 'public');
        }

        if ($request->hasFile('app_logo')) {
            $appLogoPath = $request->file('app_logo')->store('settings/branding', 'public');
        }

        Setting::updateOrCreate(
            ['id' => 1],
            [
                'maintenance_mode' => $request->maintenance_mode ? 1 : 0,
                'app_name' => $request->app_name,
                'app_description' => $request->app_description,
                'logo' => $logoPath,
                'app_logo' => $appLogoPath,
                'favicon' => $faviconPath,
                'primary_theme_colour' => $request->primary_theme_colour,
                'primary_font_colour' => $request->primary_font_colour,
                'default_font_id' => $request->default_font_id,
            ]
        );

        Cache::forget('app_config');

        return back()->with('success', 'Settings updated successfully.');
    }

    public function storeFont(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:191|unique:fonts,title',
            'normal_file' => 'required|file|mimes:woff2|max:10240',
            'bold_file' => 'nullable|file|mimes:woff2|max:10240',
            'italic_file' => 'nullable|file|mimes:woff2|max:10240',
            'bold_italic_file' => 'nullable|file|mimes:woff2|max:10240',
        ]);

        Font::create([
            'title' => $request->title,
            'normal_file' => $request->file('normal_file')->store('settings/fonts', 'public'),
            'bold_file' => $request->hasFile('bold_file') ? $request->file('bold_file')->store('settings/fonts', 'public') : null,
            'italic_file' => $request->hasFile('italic_file') ? $request->file('italic_file')->store('settings/fonts', 'public') : null,
            'bold_italic_file' => $request->hasFile('bold_italic_file') ? $request->file('bold_italic_file')->store('settings/fonts', 'public') : null,
        ]);

        return back()->with('success', 'Font added successfully.');
    }

    public function updateFont(Request $request, Font $font)
    {
        $request->validate([
            'title' => 'required|string|max:191|unique:fonts,title,' . $font->id,
            'normal_file' => 'nullable|file|mimes:woff2|max:10240',
            'bold_file' => 'nullable|file|mimes:woff2|max:10240',
            'italic_file' => 'nullable|file|mimes:woff2|max:10240',
            'bold_italic_file' => 'nullable|file|mimes:woff2|max:10240',
        ]);

        $font->update([
            'title' => $request->title,
            'normal_file' => $request->hasFile('normal_file') ? $request->file('normal_file')->store('settings/fonts', 'public') : $font->normal_file,
            'bold_file' => $request->hasFile('bold_file') ? $request->file('bold_file')->store('settings/fonts', 'public') : $font->bold_file,
            'italic_file' => $request->hasFile('italic_file') ? $request->file('italic_file')->store('settings/fonts', 'public') : $font->italic_file,
            'bold_italic_file' => $request->hasFile('bold_italic_file') ? $request->file('bold_italic_file')->store('settings/fonts', 'public') : $font->bold_italic_file,
        ]);

        return back()->with('success', 'Font updated successfully.');
    }

    public function destroyFont(Font $font)
    {
        Setting::where('default_font_id', $font->id)->update(['default_font_id' => null]);
        $font->delete();

        return back()->with('success', 'Font deleted successfully.');
    }

    public function addWatermark(Request $request) {
        $imagePath = storage_path("app/public/workflow-task-uploads/SIGN-2025101312533168eca8f3d3cd2.png");

        if (file_exists($imagePath) && is_file($imagePath)) {
            try {

            $img = \Image::make($imagePath);

            $img->text(rand(1, 100000000000000000), $img->width() - 10, 10, function ($font) {
                $font->file(storage_path('fonts/Roboto-Regular.ttf'));
                $font->size(45);
                $font->color('#ffffff');
                $font->align('right');
                $font->valign('top');
            });

            $path = $imagePath;
            $filename = !empty($path) ? basename($path) : null;

            if ($filename) {
                $img->save("storage/workflow-task-uploads/{$filename}", 90);
            }                
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
    }
}
