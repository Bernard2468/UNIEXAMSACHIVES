<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Detail;

class DetailsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'logo_image' => 'nullable',
        ]);

        if($data = Detail::first()){
            if($request->hasFile('logo_image') && !empty($request->title)){
                $logo_image = $request->file('logo_image');
                $logo_image_name = time() . '.' . $logo_image->getClientOriginalExtension();
                $logo_image->move(public_path('logo'), $logo_image_name);

                $data->update([
                    'title' => $request->title,
                    'logo_image' => $logo_image_name,
                ]);
            }else if($request->hasFile('logo_image')){
                $logo_image = $request->file('logo_image');
                $logo_image_name = time() . '.' . $logo_image->getClientOriginalExtension();
                $logo_image->move(public_path('logo'), $logo_image_name);
                $data->update([
                    'logo_image' => $logo_image_name,
                ]);
            }else{

                $data->update([
                    'title' => $request->title,
                ]);
            }
        }else{
            if($request->hasFile('logo_image') && !empty($request->title)){
                $logo_image = $request->file('logo_image');
                $logo_image_name = time() . '.' . $logo_image->getClientOriginalExtension();
                $logo_image->move(public_path('logo'), $logo_image_name);

                Detail::create([
                    'title' => $request->title,
                    'logo_image' => $logo_image_name
                ]);
            }else if($request->hasFile('logo_image')){
                $logo_image = $request->file('logo_image');
                $logo_image_name = time() . '.' . $logo_image->getClientOriginalExtension();
                $logo_image->move(public_path('logo'), $logo_image_name);

                Detail::create(
                    ['logo_image' => $logo_image_name]
                );
            }else{

                Detail::create(
                    ['title' => $request->title],
                );
            }
        }


        return redirect()->route('dashboard')->with('success', 'Title or Logo Set successfully.');
    }

}
