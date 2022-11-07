<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $medias = Media::sortable()->paginate(25);

        $values = collect();

        foreach (Media::$filtrable as $property) {
            $values->put($property, Media::select($property)->distinct()->pluck($property));
        }

        return view('medias')->with('medias', $medias)->with('filters', $values);
    }
}
