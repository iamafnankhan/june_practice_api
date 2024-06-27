<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FlightCategories;
use Illuminate\Support\Facades\validator;


class FlightCategoriesController extends Controller
{

    public function index()
    {
        $data = FlightCategories::getAll();
        return $data;
    }

    public function show($id)
    {
        return FlightCategories::getById($id);
    }

    public function store(Request $request)
    {
        return FlightCategories::add($request);
    }

    public function update(Request $request, $id)
    {
        if ($id) {
            $obj = FlightCategories::find($id);
            return $obj->updateCategory($request);
        }
    }

    public function destroy($id)
    {
        $obj = new FlightCategories();
        return $obj->deleteCategory($id);
    }


}
