<?php

namespace App\Models;

use Illuminate\Http\Request;
use App\Models\FlightAttachments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class Flight extends Model
{

    protected $table = 'flights';
    protected $fillable = ['flight_category_id', 'procedure', 'description', 'created_by'];

    public static function getAll()
    {
        try {

            $data =  self::leftjoin('flight_category_id as fc', 'fc.id', 'flight.flight_category_id')
                ->select([
                    'fc.name as flight_category_name',
                    'flight.*'
                ])
                ->where('flight.status', 1)
                ->get();

            foreach ($data as $d) {
                $d->attachments = self::getAttachments($d->id);
            }

            $response = 'Flight listed successfully';
            return response()->json([
                'response' => [
                    'message' => $response,
                    'data' => $data,
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

    public static function getAttachments($flight_id)
    {
        $data = FlightAttachments::where('flight_id', $flight_id)->get();
        return $data;
    }

    public static function getFlightCategory($category_id)
    {

        try {

            $data =  self::leftjoin('flight_category as fc', 'fc.id', 'flight.flight_category_id')
                ->select([
                    'fc.name as flight_category_name',
                    'flight.*'
                ])
                ->where('flight_category_id', $category_id)
                ->where('flight.status', 1)
                ->get();

            foreach ($data as $d) {
                $d->attachments = self::getAttachments($d->id);
            }

            $response = 'Flight listed successfully';
            return response()->json([
                'response' => [
                    'message' => $response,
                    'data' => $data,
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


    public function formValidation($requestAll, $skipped = true, $id = false)
    {
        if ($id === false) {
            $validationArray = [
                'flight_category_id' => 'required',
                'procedure' => 'required',
                'description' => 'required',
                'attachments' => 'sometimes|required',
                'attachments.*' => 'sometimes|mimes:png,pdf,xlx,csv|max:2048',
            ];
        } elseif ($id !== false) {
            $validationArray = [
                'flight_category_id' => 'required',
                'procedure' => 'required',
                'description' => 'required',
                'status' => 'required',
                'attachments' => 'sometimes|required',
                'attachments.*' => 'sometimes|mimes:png,pdf,xlx,csv|max:2048',
            ];
        }

        if ($skipped !== true) {
            if (is_array($skipped)) {
                foreach ($skipped as $temp) {
                    unset($validationArray[$temp]);
                }
            } else {
                unset($validationArray[$skipped]);
            }
        }

        $v = Validator::make(
            $requestAll,
            $validationArray,
            [
                'flight_category_id.required' => 'Please Provide flight category',
                'procedure.required' => 'Please Provide procedure',
                'description.required' => 'Please Provide procedure',
            ]
        );

        if ($v->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $v->getMessageBag()->toArray(),
            ]);
        } else {
            return true;
        }
    }


    public function addForm($request = false)
    {
        if ($request === false) {
            $request = request();
        }

        $valid = $this->formValidation($request->all(), 'status');

        if ($valid === true) {

            try {

                $user = auth()->user();
                $obj = new Flight;
                $obj->flight_category_id = $request->flight_category_id;
                $obj->procedure = $request->procedure;
                $obj->description = $request->description;
                $obj->status = true;
                // $obj->created_by = $user->instance_id;
                $obj->save();


                $flight_id = $obj->id;

                $dir = FlightAttachments::attachmentDirectory();
                $path = public_path() . '/' . $dir;
                File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);

                if ($request->has('attachments')) {

                    $attachments = $request->attachments;
                    foreach ($attachments as $key => $file) {
                        $fileExt = $file->getClientOriginalExtension();
                        $file_name = time() . rand(1, 99) . '.' . $file->extension();
                        $file->move(public_path($dir), $file_name);
                        $filePath = $dir . $file_name;

                        $dataObj = new \stdClass();
                        $dataObj->flight_id = $flight_id;
                        $dataObj->file_name = $file_name;
                        $dataObj->file_ext = $fileExt;
                        $dataObj->file_path = $filePath;

                        $attachment = new FlightAttachments;
                        $attachment->updateAttachments($dataObj);
                    }
                }

                $response = 'Flight added successfully';
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
        } else {
            return $valid;
        }
    }

    public function updateForm($request = false)
    {
        if ($request === false) {
            $request = request();
        }

        $id = $request->id;
        $valid = $this->formValidation($request->all(), true, $id);

        if ($valid === true) {

            try {

                $user = auth()->user();
                $this->flight_category_id = $request->flight_category_id;
                $this->procedure = $request->procedure;
                $this->description = $request->description;
                $this->status = $request->status;
                // $this->updated_by = $user->instance_id;
                $this->update();
                $flight_id = $this->id;

                $dir = FlightAttachments::attachmentDirectory();
                $path = public_path() . '/' . $dir;
                File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);

                if ($request->has('attachments')) {

                    $attachments = $request->attachments;
                    foreach ($attachments as $key => $file) {
                        $fileExt = $file->getClientOriginalExtension();
                        $file_name = time() . rand(1, 99) . '.' . $file->extension();
                        $file->move(public_path($dir), $file_name);
                        $filePath = $dir . $file_name;

                        $dataObj = new \stdClass();
                        $dataObj->flight_id = $flight_id;
                        $dataObj->file_name = $file_name;
                        $dataObj->file_ext = $fileExt;
                        $dataObj->file_path = $filePath;

                        $attachment = new FlightAttachments;
                        $attachment->updateAttachments($dataObj);
                    }
                }

                $response = 'Flight updated successfully';
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


             return response()->json([
                'status' => 200,
                'message' => 'condition has been updated successfully.',
            ]);
        } else {
            return $valid;
        }
    }



    public static function getById($id)
    {

        try {

            $flight = self::find($id);

            if (!$flight) {
                return response()->json(['response' => ['message' => 'Flight not found', 'status' => 404]], 404);
            } else {

                $flight->attachments = self::getAttachments($id);

                $message = 'Flight category found';
                return response([
                    'response' => [
                        'message' => $message,
                        'status' => 200,
                        'data' => $flight,
                    ]
                ]);
            }
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

    public static function createFlight(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight_category_id' => 'required|exists:flight_categories,id',
            'procedure' => 'required|string',
            'description' => 'required|string',
            'file' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return [
                'errors' => $validator->getMessageBag()->toArray(),
                'status' => 400,
            ];
        }

        $lawsData = $request->except('file');
        $lawsData['created_by'] = auth()->user()->instance_id;
        $laws = self::create($lawsData);

        // Upload file and create Law Attachment record
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileExt = $file->getClientOriginalExtension();
        $filePath = FlightAttachments::attachmentDirectory() . $fileName;

        Storage::putFileAs(FlightAttachments::attachmentDirectory(), $file, $fileName);

        $lawAttachment = new FlightAttachments([
            'flight_id' => $laws->id,
            'file_name' => $fileName,
            'file_ext' => $fileExt,
            'file_path' => $filePath,
        ]);

        $lawAttachment->created_by = auth()->user()->instance_id;
        $lawAttachment->save();

        return [
            'message' => 'Flight Category and Attachment added successfully',
            'law' => $lawsData,
            'Attachment' => $lawAttachment,
            'status' => 201,
        ];
    }

    public static function updateLaw(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'flight_category_id' => 'required|exists:flight_categories,id',
            'procedure' => 'required|string',
            'description' => 'required|string',
            'file' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return [
                'errors' => $validator->getMessageBag()->toArray(),
                'status' => 400,
            ];
        }

        $laws = self::find($id);

        if (!$laws) {
            return [
                'message' => 'Flight not found',
                'status' => 404,
            ];
        }

        $laws->update($request->except('file'));

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileExt = $file->getClientOriginalExtension();
            $filePath = FlightAttachments::attachmentDirectory() . $fileName;

            Storage::putFileAs(FlightAttachments::attachmentDirectory(), $file, $fileName);

            $lawAttachment = FlightAttachments::updateOrCreate(
                ['flight_id' => $laws->id],
                [
                    'file_name' => $fileName,
                    'file_ext' => $fileExt,
                    'file_path' => $filePath,
                ]
            );
        }

        return [
            'message' => 'Flight updated successfully',
            'data' => [
                'laws' => $laws,
                'attachment' => $lawAttachment ?? null,
            ],
            'status' => 200,
        ];
    }

    public static function deleteLaw($id)
    {
        $law = self::find($id);

        if (!$law) {
            return [
                'error' => 'Flight not found',
                'status' => 404,
            ];
        }

        $law->delete();

        return [
            'message' => 'Flight and Attachment deleted',
            'status' => 200,
        ];
    }
}
