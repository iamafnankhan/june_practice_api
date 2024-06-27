<?php

namespace App\Models;

use Illuminate\Http\Request;
// use App\Models\LawCategories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlightCategories extends Model
{
    use HasFactory;

    protected $table = 'flight_categories';
    protected $fillable = [
        'name',
        'status',
        'parent_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    public static function getAll()
    {

        try {

            $lawCategories = self::all()->toArray();

            if (empty($lawCategories)) {
                return response()->json(['response' => ['message' => 'No data to show', 'status' => 404]], 404);
            }

            return response()->json(['response' => ['message' => 'Flights listed successfully', 'status' => 200, 'data' => $lawCategories]], 200);
        } catch (\Exception $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        } catch (\Throwable $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        }
    }


    public static function getById($id)
    {
        $lawCategory = self::find($id);

        if (!$lawCategory) {
            return response()->json(['response' => ['message' => 'Flight category not found']], 404);
        } else {

            $message = 'Law category found';
            return response([
                'response' => [
                    'message' => $message,
                    'status' => 200,
                    'data' => $lawCategory,
                ]
            ]);
        }
    }


    public static function add(Request $request = null)
    {
        if ($request === null) {
            $request = request();
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('flight_categories')->using(function ($q) {
                    $q->whereNull('deleted_at');
                }),
            ],
            'parent_id' => 'integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response([
                'response' => [
                    'message' => $errors,
                    'status' => 400,
                ]
            ]);
        }

        try {

            $user = auth()->user();
            $obj = new FlightCategories;
            $obj->name = $request->name;

            if ($request->has('parent_id')) {
                $obj->parent_id = $request->parent_id;
            } else {
                $obj->parent_id = 0;
            }
            $obj->status = true;
            // $obj->created_by = $user->instance_id;
            $obj->save();

            $response = 'Flight Category added successfully';
            return response()->json([
                'response' => [
                    'message' => $response,
                    'data' => $obj,
                    'status' => 200,
                ],
            ]);
        } catch (\Exception $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        } catch (\Throwable $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        }
    }


    public function updateCategory($request = false)
    {
        if ($request  === false) {
            $request = request();
        }

        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('flight-categories')->ignore($id)->using(function ($q) {
                    $q->whereNull('deleted_at');
                }),
            ],
            'parent_id' => 'integer|nullable',
            'status' => 'integer|required',
        ]);


        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response([
                'response' => [
                    'message' => $errors,
                    'status' => 400,
                ]
            ]);
        }

        try {

            $user = auth()->user();
            $this->name = $request->name;
            if ($request->has('parent_id')) {
                $this->parent_id = $request->parent_id;
            } else {
                $this->parent_id = 0;
            }
            $this->status = $request->status;
            // $this->updated_by = $user->instance_id;
            $this->update();

            $response = 'Flight Category updated successfully';
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 200,
                ],
            ]);
        } catch (\Exception $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        } catch (\Throwable $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        }
    }


    public function deleteCategory($id)
    {

        try {

            $lawCategory = self::find($id);

            if (!$lawCategory) {
                return response()->json(['response' => ['error' => 'Flight category not found']], 404);
            }

            $lawCategory->delete();
            return response()->json(['response' => ['message' => 'Flight category deleted', 'status' => 200]]);

        } catch (\Exception $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        } catch (\Throwable $e) {
            $response =  $e->getMessage();
            return response()->json([
                'response' => [
                    'message' => $response,
                    'status' => 400,
                ],
            ]);
        }
    }
}
