<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Boleta de Calificaciones</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
    }
    h1, h2 {
      text-align: center;
      margin: 0;
    }
    .info {
      margin: 20px 0;
    }
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

  <h1>Boleta de Calificaciones</h1>
  <h2>Trimestre {{ $periodo }}</h2>

  <div class="info">
    <strong>Nombre:</strong> {{ $estudiante->nombre_completo }}<br>
    <strong>Curso:</strong> {{ $estudiante->curso }}<br>
    <strong>Nivel:</strong> {{ $estudiante->nivel }}
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Materia</th>
        <th>1er Trimestre</th>
        <th>2do Trimestre</th>
        <th>3er Trimestre</th>
        <th>Promedio Final</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($materias as $i => $m)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td style="text-align: left;">{{ $m['nombre'] }}</td>
          <td>{{ $m['t1'] }}</td>
          <td>{{ $m['t2'] }}</td>
          <td>{{ $m['t3'] }}</td>
          <td><strong>{{ $m['promedio'] }}</strong></td>
        </tr>
      @endforeach
    </tbody>
  </table>

</body>
</html>
