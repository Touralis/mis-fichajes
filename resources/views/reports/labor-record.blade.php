<!doctype html>
<html lang="es"><head><meta charset="utf-8"><style>
body { font-family: sans-serif; color: #000; font-size: 10pt; }
h1 { text-align: center; font-size: 17pt; margin: 0 0 5px; }
.subtitle { text-align: center; font-style: italic; font-size: 9pt; margin-bottom: 5px; }
table { width: 100%; border-collapse: collapse; }
.identification { margin-top: 6px; }
.identification td { border: 1px solid #000; width: 50%; height: 42px; padding: 4px; vertical-align: middle; font-size: 10pt; }
.identification strong { font-size: 11pt; }
h2 { text-align: center; font-size: 14pt; margin: 18px 0 10px; }
.entries th, .entries td { border: 1px solid #000; text-align: center; padding: 1px 2px; font-size: 8.5pt; }
.entries th { background: #ddd; font-weight: bold; }
.total { text-align: center; font-weight: bold; margin-top: 16px; font-size: 11pt; }
.summary { margin-top: 19px; border: 1px solid #ccc; }
.summary td { width: 50%; padding: 2px 10px; font-size: 9pt; }
.summary strong { font-size: 11pt; }
.signatures { margin-top: 23px; }
.signatures td { width: 50%; vertical-align: top; font-size: 10pt; }
.signatures .right { text-align: right; }
</style></head><body>
<h1>REGISTRO DIARIO DE JORNADA</h1>
<p class="subtitle">En cumplimiento a la obligación establecida en art. 34.9, 12.4.c) y 35.5 del Estatuto de los Trabajadores</p>
<table class="identification"><tr><td><strong>Empresa</strong><br>{{ $company->name ?? '' }}<br>CIF: {{ $company->tax_id ?? '' }}</td><td><strong>Trabajador</strong><br>Nombre: {{ $employee->name }}<br>NIF: {{ $employee->identity_number ?? '' }}<br>Nº Afiliación SS: {{ $employee->social_security_number ?? '' }}</td></tr></table>
<h2>{{ ucfirst(\Carbon\Carbon::parse($filters['from'])->locale('es')->translatedFormat('F Y')) }}</h2>
@php
$formatDuration = fn ($seconds) => sprintf('%02d:%02d', intdiv((int) $seconds, 3600), intdiv((int) $seconds % 3600, 60));
$recordsByDay = $records->groupBy(fn ($record) => $record->started_at->format('Y-m-d'));
@endphp
<table class="entries"><thead><tr><th width="8%">Día</th><th width="13%">Hora entrada</th><th width="13%">Hora salida</th><th width="17%">Horas ordinarias</th><th width="15%">Horas compl.</th><th width="15%">Horas extra.</th><th width="19%">Observaciones</th></tr></thead><tbody>
@foreach(\Carbon\CarbonPeriod::create($filters['from'], $filters['to']) as $day)
@php $dayRecords = $recordsByDay->get($day->format('Y-m-d'), collect()); $dayNotes = ($observations ?? collect())->get($day->format('Y-m-d'), collect())->pluck('description')->implode('; '); @endphp
@if($dayRecords->isEmpty())
<tr><td>{{ $day->format('d/m') }}</td><td></td><td></td><td></td><td></td><td></td><td>{{ $dayNotes ?: ($day->isWeekend() ? 'Descanso semanal' : '') }}</td></tr>
@else
@foreach($dayRecords as $record)
<tr><td>{{ $record->started_at->format('d/m') }}</td><td>{{ $record->started_at->format('H:i') }}</td><td>{{ $record->ended_at?->format('H:i') ?? 'En curso' }}</td><td>{{ $record->ended_at ? $formatDuration(max(0, $record->started_at->diffInSeconds($record->ended_at))) : '—' }}</td><td>—</td><td>—</td><td>{{ collect([$loop->first ? $dayNotes : '', $record->ended_at && !$record->started_at->isSameDay($record->ended_at) ? 'Salida '.$record->ended_at->format('d/m') : ''])->filter()->implode('; ') }}</td></tr>
@endforeach
@endif
@endforeach
</tbody></table>
<table class="summary"><tr><td colspan="2"><strong>Resumen mensual</strong></td></tr><tr><td>Horas ordinarias registradas<br><strong>{{ $formatDuration($totalSeconds) }}</strong></td><td>Horas complementarias<br><strong>—</strong></td></tr><tr><td>Horas extraordinarias<br><strong>—</strong></td><td><strong>Total trabajo efectivo<br>{{ $formatDuration($totalSeconds) }}</strong></td></tr></table>
<table class="signatures"><tr><td>Firma Empresa @if($signature)<br><img src="{{ $signature }}" style="max-width:160px;max-height:45px" alt="Firma de la empresa">@endif</td><td class="right">Firma Trabajador @if(!empty($employeeSignature))<br><img src="{{ $employeeSignature }}" style="max-width:160px;max-height:45px" alt="Firma del trabajador">@endif</td></tr></table>
</body></html>
