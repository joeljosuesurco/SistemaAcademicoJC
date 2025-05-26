package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.os.Bundle;
import android.view.Gravity;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.Spinner;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.NotasEstudianteResponse;
import com.jsq.sistemaacademicojct.utils.NotasCache;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class NotasEstudianteActivity extends AppCompatActivity {

    Spinner spinnerTrimestre;
    TableLayout tablaNotas;
    Button btnVerBoletaFinal;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_notas_estudiante);
        getSupportActionBar().hide();

        spinnerTrimestre = findViewById(R.id.spinnerTrimestre);
        tablaNotas = findViewById(R.id.tablaNotas);
        btnVerBoletaFinal = findViewById(R.id.btnVerBoletaFinal);

        // Spinner con trimestres
        String[] opciones = {"Trimestre 1", "Trimestre 2", "Trimestre 3"};
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, opciones);
        spinnerTrimestre.setAdapter(adapter);

        spinnerTrimestre.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                int trimestreSeleccionado = position + 1;
                mostrarNotasDelTrimestre(trimestreSeleccionado);
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });

        btnVerBoletaFinal.setOnClickListener(v -> {
            Intent intent = new Intent(NotasEstudianteActivity.this, BoletaEstudianteActivity.class);
            startActivity(intent);
        });

        cargarTodasLasNotas();
    }

    private void cargarTodasLasNotas() {
        Map<Integer, List<NotasEstudianteResponse.Nota>> cacheTemp = new HashMap<>();

        for (int t = 1; t <= 3; t++) {
            final int trimestre = t;

            UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
            service.getNotasEstudiante("trimestre", trimestre).enqueue(new Callback<NotasEstudianteResponse>() {
                @Override
                public void onResponse(Call<NotasEstudianteResponse> call, Response<NotasEstudianteResponse> response) {
                    if (response.isSuccessful() && response.body() != null) {
                        cacheTemp.put(trimestre, response.body().getData().notas);

                        if (cacheTemp.size() == 3) {
                            NotasCache.guardarNotas(cacheTemp);
                            mostrarNotasDelTrimestre(1);
                        }
                    } else {
                        Toast.makeText(NotasEstudianteActivity.this, "Error al cargar trimestre " + trimestre, Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onFailure(Call<NotasEstudianteResponse> call, Throwable t) {
                    Toast.makeText(NotasEstudianteActivity.this, "Sin conexión para trimestre " + trimestre, Toast.LENGTH_SHORT).show();
                }
            });
        }
    }

    private void mostrarNotasDelTrimestre(int trimestre) {
        tablaNotas.removeViews(2, tablaNotas.getChildCount() - 2); // Limpia todas menos encabezado

        List<NotasEstudianteResponse.Nota> notas = NotasCache.obtenerNotas().get(trimestre);
        if (notas == null) return;

        for (NotasEstudianteResponse.Nota nota : notas) {
            TableRow fila = new TableRow(this);

            String nombreMateria = nota.materia.area_materia + " (" + nota.materia.sigla_materia + ")";
            fila.addView(crearCelda(nombreMateria));

            String[] claves = {
                    "ser_docente", "saber_docente", "hacer_docente", "decidir_docente",
                    "ser_autoeval", "decidir_autoeval"
            };

            Map<String, Integer> mapa = new HashMap<>();
            for (NotasEstudianteResponse.Dimension d : nota.dimensiones) {
                mapa.put(d.nombre_dimension.toLowerCase(), d.valor_obtenido);
            }

            int[] valores = new int[6];
            for (int i = 0; i < claves.length; i++) {
                valores[i] = mapa.containsKey(claves[i]) ? mapa.get(claves[i]) : 0;
                fila.addView(crearCelda(String.valueOf(valores[i])));
            }

            double promedioCalc =
                    valores[0] * 0.05 +
                            valores[1] * 0.45 +
                            valores[2] * 0.40 +
                            valores[3] * 0.05 +
                            valores[4] * 0.05 +
                            valores[5] * 0.05;

            int promedio = (int) Math.round(Math.min(promedioCalc, 100));
            TextView celdaProm = crearCelda(String.valueOf(promedio));
            celdaProm.setTypeface(null, android.graphics.Typeface.BOLD);
            celdaProm.setTextColor(getResources().getColor(android.R.color.holo_blue_dark));
            fila.addView(celdaProm);

            tablaNotas.addView(fila);
        }
    }

    private TextView crearCelda(String texto) {
        TextView tv = new TextView(this);
        tv.setText(texto);
        tv.setPadding(16, 8, 16, 8);
        tv.setGravity(Gravity.CENTER);
        return tv;
    }
}
