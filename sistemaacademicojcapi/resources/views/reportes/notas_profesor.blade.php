<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Notas</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    h1, h2, h3 { text-align: center; margin: 0; }
    .info { margin: 15px 0; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #999;
      padding: 6px;
      text-align: center;
    }
    th {
      background-color: #f0f0f0;
    }
  </style>
</head>
<body>

  <h1>REPORTE DE NOTAS</h1>
  <h2>Trimestre {{ $periodo }}</h2>

  <div class="info">
    <strong>Curso:</strong> {{ $curso->nombre }} |
    <strong>Nivel:</strong> {{ $curso->nivel }} |
    <strong>Gestión:</strong> {{ $curso->gestion }}<br>
    <strong>Materia:</strong> {{ $materia->area }} ({{ $materia->sigla }}) –
    <em>{{ $materia->campo }}</em>
  </div>

  <table>
    <thead>
      <tr>
        <th rowspan="3">#</th>
        <th rowspan="3">Estudiante</th>
        <th colspan="4">Evaluación Maestro(a)</th>
        <th colspan="2">Autoevaluación</th>
        <th rowspan="3">Promedio</th>
      </tr>
      <tr>
        <th colspan="4">Dimensiones</th>
        <th colspan="2">Dimensiones</th>
      </tr>
      <tr>
        <th>Ser</th>
        <th>Saber</th>
        <th>Hacer</th>
        <th>Decidir</th>
        <th>Ser</th>
        <th>Decidir</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($notas as $i => $n)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td style="text-align: left;">{{ $n['estudiante'] }}</td>
          <td>{{ $n['ser'] }}</td>
          <td>{{ $n['saber'] }}</td>
          <td>{{ $n['hacer'] }}</td>
          <td>{{ $n['decidir'] }}</td>
          <td>{{ $n['ser_auto'] }}</td>
          <td>{{ $n['decidir_auto'] }}</td>
          <td><strong>{{ $n['promedio'] }}</strong></td>
        </tr>
      @endforeach
    </tbody>
  </table>

</body>
</html>
