<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Registro Laboral</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            font-weight: bold;
        }

        #frase_cumplimiento {
            font-size: small;
            font-style: italic;
            display: block;
            text-align: center;
            margin-top: 10px;
        }

        table.cuadrados {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
        }

        table.cuadrados td {
            width: fit-content;
            padding: 10px;
            height: 100px;
            border: 1px solid black;
        }

        #fichajes {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid black;
        }

        #fichajes th {
            background-color: #ddd;
            border: 1px solid black;
            text-align: center;
        }

        #fichajes td {
            padding: 10px;
            text-align: center;
            border: 1px solid black;
        }

        table.firmas {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
        }

        table.firmas td {
            width: fit-content;
            padding: 10px;
            border: 1px solid black;
            padding-bottom: 100px;
        }

        table.firmas td p {
            text-align: center;
            margin: 0;
        }
    </style>
</head>

<body>
    <h1>Registro Laboral</h1>
    <span id="frase_cumplimiento">En el cumplimiento de la obligación establecida en el Art. 12.5h del Estatuto de los
        Trabajadores</span>

    <table class="cuadrados">
        <tr>
            <td>
                <h2>Empresa</h2>
                <p>Alvesa SIGLO XXI</p>
                <p>CIF: B05401385</p>
            </td>
            <td>
                <h2>Trabajador</h2>
                <p>Nombre: {{ $employer->nombre }}</p>
                <p>NIF: {{ $employer->dni }}</p>
                <p>Nº Afiliación SS: {{ $employer->numero_afiliacion_ss }}</p>
            </td>
        </tr>
    </table>

    @php
        $mes = $month ? \Carbon\Carbon::create()->month((int) $month) : \Carbon\Carbon::now();
    @endphp

    <h2 style="text-align: center;">{{ ucfirst($mes->locale('es')->isoFormat('MMMM')) }}</h2>
    <table id="fichajes">
        <tr>
            <th>Día</th>
            <th>Hora entrada</th>
            <th>Hora salida</th>
            <th>Horas ordinarias</th>
            <th>Horas compl.</th>
            <th>Observaciones</th>
        </tr>
        @forelse($fichajesPorDia as $fecha => $fichajesDelDia)
            @php
                $primeraEntrada = \Carbon\Carbon::parse($fichajesDelDia->first()->dia_entrada);
                $ultimaSalida = $fichajesDelDia->whereNotNull('dia_salida')->isNotEmpty()
                    ? \Carbon\Carbon::parse($fichajesDelDia->whereNotNull('dia_salida')->last()->dia_salida)
                    : null;
                $horasDiarias = 0;
                $horasComplementarias = 0;
                foreach ($fichajesDelDia as $fichaje) {
                    if ($fichaje->dia_salida) {
                        $entrada = \Carbon\Carbon::parse($fichaje->dia_entrada);
                        $salida = \Carbon\Carbon::parse($fichaje->dia_salida);
                        $horasDiarias += $salida->diffInMinutes($entrada) / 60;
                    }
                }
                // Ordinarias y complementarias
                if (abs($horasDiarias) > $employer->horas_semanales) {
                    $horasComplementarias = abs($horasDiarias) - $employer->horas_semanales;
                    $horasDiarias = $employer->horas_semanales;
                }
                if ($horasComplementarias < 0) {
                    $horasComplementarias = 0;
                }
            @endphp
            <tr>
                <td>{{ $primeraEntrada->format('d/m') }}</td>
                <td>{{ $primeraEntrada->format('H:i') }}</td>
                <td>{{ $ultimaSalida ? $ultimaSalida->format('H:i') : 'Sin salida' }}</td>

                <td>{{ number_format(abs($horasDiarias), 2, ',', '.') }}</td>
                <td>{{ number_format($horasComplementarias, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center;">No hay registros para los filtros aplicados.</td>
            </tr>
        @endforelse
    </table>

    @php
        $horasTotalesMensuales = 0;
        foreach ($fichajesPorDia as $fecha => $fichajesDelDia) {
            foreach ($fichajesDelDia as $fichaje) {
                if ($fichaje->dia_salida) {
                    $entrada = \Carbon\Carbon::parse($fichaje->dia_entrada);
                    $salida = \Carbon\Carbon::parse($fichaje->dia_salida);
                    $horasTotalesMensuales += $salida->diffInMinutes($entrada) / 60;
                }
            }
        }
        $horasTotalesMensuales = $horasTotalesMensuales;
    @endphp

    <p style="text-align: center;">
        Total de horas mensuales: {{ number_format(abs($horasTotalesMensuales), 2, ',', '.') }}<br>
    </p>

    <table class="firmas">
        <tr>
            <td>
                <p>Firma Empresa</p>

            </td>
            <td>
                <p>Firma Trabajador</p>
            </td>
        </tr>
    </table>

</body>

</html>
