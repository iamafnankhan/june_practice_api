<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use App\Models\FlightAttachments;
use App\Http\Controllers\Controller;

class FlightController extends Controller
{

    public function index()
    {
        $data = Flight::getAll();
        return $data;
    }

    public function show($id)
    {
        return Flight::getById($id);
    }

    public function store(Request $request)
    {
        $obj = new Flight;
        return $obj->addForm($request);
    }

    public function update(Request $request, $id)
    {
        if ($id) {
            $obj = Flight::find($id);
            return $obj->updateForm($request);
        }
    }

    public function destroy($id)
    {
        $obj = new Flight();
        return $obj->deleteLaw($id);
    }


    public function deleteAttachment ($id) {
        $obj = FlightAttachments::find($id);
        return $obj->deleteFile();
    }




}
