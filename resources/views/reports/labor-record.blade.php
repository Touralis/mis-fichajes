<!doctype html>
<html lang="es"><head><meta charset="utf-8"><style>
body { font-family: sans-serif; color: #000; font-size: 10pt; }
h1 { text-align: center; font-size: 23pt; margin: 0 0 18px; }
.subtitle { text-align: center; font-style: italic; font-size: 10pt; margin-bottom: 18px; }
table { width: 100%; border-collapse: collapse; }
.identification { margin-top: 12px; }
.identification td { border: 1px solid #000; width: 50%; height: 78px; padding: 8px; vertical-align: middle; font-size: 10pt; }
.identification strong { font-size: 14pt; }
h2 { text-align: center; font-size: 18pt; margin: 18px 0 28px; }
.entries th, .entries td { border: 1px solid #000; text-align: center; padding: 2px 2px; font-size: 9pt; }
.entries th { background: #ddd; font-weight: bold; }
.total { text-align: center; font-weight: bold; margin-top: 16px; font-size: 11pt; }
.signatures { margin-top: 34px; }
.signatures td { width: 50%; vertical-align: top; font-size: 10pt; }
.signatures .right { text-align: right; }
</style></head><body>
<h1>REGISTRO DIARIO DE JORNADA</h1>
<p class="subtitle">En cumplimiento a la obligación establecida en los art 12.5 h y 35.5 del Estatuto de los Trabajadores</p>
<table class="identification"><tr><td><strong>Empresa</strong><br>{{ $company->name ?? '' }}<br>CIF: {{ $company->tax_id ?? '' }}</td><td><strong>Trabajador</strong><br>Nombre: {{ $employee->name }}<br>NIF: {{ $employee->identity_number ?? '' }}<br>Nº Afiliación SS: {{ $employee->social_security_number ?? '' }}</td></tr></table>
<h2>{{ ucfirst(\Carbon\Carbon::parse($filters['from'])->locale('es')->translatedFormat('F Y')) }}</h2>
<table class="entries"><thead><tr><th width="8%">Día</th><th width="13%">Hora entrada</th><th width="13%">Hora salida</th><th width="16%">Horas ordinarias</th><th width="39%">Horas compl.</th><th width="14%">Observaciones</th></tr></thead><tbody>
@forelse($records as $record)
<tr><td>{{ $record->started_at->format('d/m') }}</td><td>{{ $record->started_at->format('H:i') }}</td><td>{{ $record->ended_at?->format('H:i') ?? 'En curso' }}</td><td>{{ $record->ended_at ? number_format($record->started_at->diffInSeconds($record->ended_at)/3600, 2, ',', '.') : '—' }}</td><td>-</td><td>{{ $record->ended_at && !$record->started_at->isSameDay($record->ended_at) ? 'Salida '.$record->ended_at->format('d/m') : '' }}</td></tr>
@empty<tr><td colspan="6">No hay registros para el mes seleccionado.</td></tr>@endforelse
</tbody></table>
<p class="total">Total de horas mensuales: {{ number_format($totalSeconds/3600, 2, ',', '.') }}</p>
<table class="signatures"><tr><td>Firma Empresa @if($signature)<br><img src="{{ $signature }}" style="max-width:180px;max-height:85px" alt="Firma de la empresa">@endif</td><td class="right">Firma Trabajador</td></tr></table>
</body></html>
