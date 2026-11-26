<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mpdf\Mpdf;

class LaborRecordController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from', 'employee_id' => 'nullable|integer|exists:users,id']);
    }
    private function query(Request $request, array $filters)
    {
        $query = AttendanceRecord::with('user:id,name,position')->when($request->user()->role !== 'admin', fn($q) => $q->where('user_id', $request->user()->id));
        if ($request->user()->role === 'admin' && !empty($filters['employee_id'])) $query->where('user_id', $filters['employee_id']);
        return $query->when($filters['from'] ?? null, fn($q, $v) => $q->where('started_at', '>=', Carbon::parse($v)->startOfDay()))->when($filters['to'] ?? null, fn($q, $v) => $q->where('started_at', '<', Carbon::parse($v)->addDay()->startOfDay()));
    }
    public function index(Request $request)
    {
        return $this->query($request, $this->filters($request))->orderByDesc('started_at')->orderByDesc('id')->paginate(25);
    }
    private function checkCorrectionsEnabled(Request $request): void
    {
        abort_unless($request->user()->role === 'admin' && DB::table('company_settings')->where('id', 1)->value('allow_attendance_correction'), 403);
    }
    public function update(Request $request, AttendanceRecord $record)
    {
        $this->checkCorrectionsEnabled($request);
        $data = $request->validate(['started_at' => 'required|date_format:Y-m-d\TH:i', 'ended_at' => 'nullable|date_format:Y-m-d\TH:i|after:started_at', 'reason' => 'required|string|min:5|max:1000']);
        DB::transaction(function () use ($request, $record, $data) {
            User::whereKey($record->user_id)->lockForUpdate()->firstOrFail();
            $record = AttendanceRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $start = Carbon::createFromFormat('Y-m-d\TH:i', $data['started_at'])->seconds(0);
            $end = empty($data['ended_at']) ? null : Carbon::createFromFormat('Y-m-d\TH:i', $data['ended_at'])->seconds(0);
            $overlap = AttendanceRecord::where('user_id', $record->user_id)->whereKeyNot($record->id)->where(function ($q) use ($end) {
                if ($end) $q->where('started_at', '<', $end);
            })->where(fn($q) => $q->whereNull('ended_at')->orWhere('ended_at', '>', $start))->exists();
            if ($overlap) throw ValidationException::withMessages(['started_at' => 'El intervalo se solapa con otro fichaje de esta persona.']);
            DB::table('attendance_corrections')->insert(['attendance_record_id' => $record->id, 'actor_id' => $request->user()->id, 'previous_started_at' => $record->started_at, 'previous_ended_at' => $record->ended_at, 'new_started_at' => $start, 'new_ended_at' => $end, 'reason' => $data['reason'], 'created_at' => now(), 'updated_at' => now()]);
            $record->update(['started_at' => $start, 'ended_at' => $end]);
        });
        return response()->json(['ok' => true]);
    }
    public function history(Request $request, AttendanceRecord $record)
    {
        $this->checkCorrectionsEnabled($request);
        return DB::table('attendance_corrections')->join('users', 'users.id', '=', 'attendance_corrections.actor_id')->where('attendance_record_id', $record->id)->orderByDesc('attendance_corrections.id')->get(['attendance_corrections.*', 'users.name as actor_name']);
    }
    public function pdf(Request $request)
    {
        $filters = $request->validate(['from' => 'required|date_format:Y-m-d', 'to' => 'required|date_format:Y-m-d|after_or_equal:from', 'employee_id' => 'nullable|integer|exists:users,id']);
        if ($request->user()->role === 'admin' && empty($filters['employee_id'])) throw ValidationException::withMessages(['employee_id' => 'Selecciona una persona para generar el informe.']);
        $employee = $request->user()->role === 'admin' ? User::findOrFail($filters['employee_id']) : $request->user();
        $filters['employee_id'] = $employee->id;
        $records = $this->query($request, $filters)->orderBy('started_at')->orderBy('id')->get();
        $observations = DB::table('day_observations')->whereBetween('date', [$filters['from'], $filters['to']])->where(fn($q) => $q->whereNull('user_id')->orWhere('user_id', $employee->id))->orderBy('id')->get()->groupBy('date');
        $totalSeconds = $records->sum(fn($r) => $r->ended_at ? max(0, $r->started_at->diffInSeconds($r->ended_at)) : 0);
        \Illuminate\Support\Facades\File::ensureDirectoryExists(storage_path('app/mpdf-temp'));
        $company = DB::table('company_settings')->where('id', 1)->first();
        $signature = $company?->signature_data ? 'data:'.$company->signature_mime.';base64,'.$company->signature_data : null;
        $employeeSignature = $employee->signature_data ? 'data:image/png;base64,'.$employee->signature_data : null;
        $pdf = new Mpdf(['tempDir' => storage_path('app/mpdf-temp'), 'margin_top' => 10, 'margin_bottom' => 10]);
        $pdf->WriteHTML(view('reports.labor-record', compact('employee', 'filters', 'records', 'totalSeconds', 'company', 'signature', 'employeeSignature', 'observations'))->render());
        return response($pdf->Output('', 'S'), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="registro-laboral-' . $employee->id . '-' . $filters['from'] . '-' . $filters['to'] . '.pdf"']);
    }
}
