<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class AttendanceRecord extends Model {
 use HasUuids;
 protected $fillable = ['user_id', 'started_at', 'ended_at', 'start_latitude', 'start_longitude', 'start_accuracy', 'end_latitude', 'end_longitude', 'end_accuracy', 'origin', 'generated_by'];
 protected function casts(): array { return ['started_at'=>'datetime', 'ended_at'=>'datetime']; }
 public function user() { return $this->belongsTo(User::class); }
}
