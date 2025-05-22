<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Estudiantes</title>
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
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #999;
      padding: 6px;
      text-align: left;
    }
    th {
      background-color: #f0f0f0;
    }
    .info-curso {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <h1>Reporte de Estudiantes Inscritos</h1>

  <div class="info-curso">
    <strong>Curso:</strong> {{ $curso->grado_curso }} {{ $curso->paralelo_curso }}<br>
    <strong>Nivel:</strong> {{ $curso->nivel_educativo->nombre }}<br>
    <strong>Aula:</strong> {{ $curso->aula_curso }}<br>
    <strong>Turno:</strong> {{ $curso->turno_curso }}<br>
    <strong>Descripción:</strong> {{ $curso->descripcion }}
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Nombre completo</th>
        <th>CI</th>
        <th>RUDE</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($estudiantes as $i => $inscripcion)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>
            {{ $inscripcion->estudiante->persona_rol->persona->apellidos_pat }}
            {{ $inscripcion->estudiante->persona_rol->persona->apellidos_mat }},
            {{ $inscripcion->estudiante->persona_rol->persona->nombres_persona }}
          </td>
          <td>{{ $inscripcion->estudiante->persona_rol->persona->documento->carnet_identidad ?? '—' }}</td>
          <td>{{ $inscripcion->estudiante->rude }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
