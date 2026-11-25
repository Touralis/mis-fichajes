<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceRecord extends Model {
 protected $fillable = ['user_id', 'started_at', 'ended_at', 'start_latitude', 'start_longitude', 'start_accuracy', 'end_latitude', 'end_longitude', 'end_accuracy'];
 protected function casts(): array { return ['started_at'=>'datetime', 'ended_at'=>'datetime']; }
 public function user() { return $this->belongsTo(User::class); }
}
