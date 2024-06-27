<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightAttachments extends Model
{
    protected $table = 'flight_attachments';
    protected $fillable = ['flight_id', 'file_name', 'file_ext', 'file_path'];

    public function law()
    {
        return $this->belongsTo(Flight::class, 'flight_id');
    }

    public static function attachmentDirectory()
    {
        return 'assets/media/flight_attachments/';
    }

    public function updateAttachments($obj) {

        $attachment = new FlightAttachments;
        $attachment->flight_id = $obj->flight_id;
        $attachment->file_name = $obj->file_name;
        $attachment->file_ext = $obj->file_ext;
        $attachment->file_path = $obj->file_path;
        $attachment->status = true;
        // $attachment->created_by = auth()->user()->instance_id;
        $attachment->save();
    }

    public function deleteFile () {
        $this->deleted_by = auth()->user()->id;
        $this->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Attachment has been deleted successfully.',
        ]);
    }
}
