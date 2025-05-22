<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Estudiantes del Curso</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #999;
      padding: 6px;
      text-align: left;
    }
    th {
      background-color: #f0f0f0;
    }
    .info {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <h1>Lista de Estudiantes</h1>

  <div class="info">
    <strong>Curso:</strong> {{ $curso->grado_curso }} {{ $curso->paralelo_curso }}<br>
    <strong>Turno:</strong> {{ $curso->turno_curso }}<br>
    <strong>Nivel:</strong> {{ $curso->nivel_educativo->nombre ?? $curso->nivel ?? '—' }}<br>
    <strong>Aula:</strong> {{ $curso->aula_curso }}
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Apellido Paterno</th>
        <th>Apellido Materno</th>
        <th>Nombres</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($estudiantes as $i => $e)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $e['apellidos_pat'] }}</td>
          <td>{{ $e['apellidos_mat'] }}</td>
          <td>{{ $e['nombres'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
