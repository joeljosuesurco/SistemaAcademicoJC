package com.jsq.sistemaacademicojct;

import android.os.Bundle;
import android.view.Gravity;
import android.widget.Button;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.NotasEstudianteResponse;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import com.jsq.sistemaacademicojct.utils.NotasCache;

public class BoletaEstudianteActivity extends AppCompatActivity {

    TableLayout tablaResumen;
    Button btnActualizar;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        getSupportActionBar().hide();
        setContentView(R.layout.activity_boleta_estudiante);

        tablaResumen = findViewById(R.id.tablaResumen);

        Map<Integer, List<NotasEstudianteResponse.Nota>> cache = NotasCache.obtenerNotas();

        if (!cache.isEmpty()) {
            Map<String, Map<Integer, Integer>> notasPorMateria = transformarNotas(cache);
            llenarTabla(notasPorMateria);
        } else {
            Toast.makeText(this, "Las notas no fueron cargadas previamente", Toast.LENGTH_LONG).show();
            finish(); // Opcional: volver si no hay datos
        }
    }


    private void cargarBoleta() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        Map<String, Map<Integer, Integer>> notasPorMateria = new HashMap<>();

        for (int t = 1; t <= 3; t++) {
            final int trimestre = t;

            service.getNotasEstudiante("trimestre", trimestre).enqueue(new Callback<NotasEstudianteResponse>() {
                @Override
                public void onResponse(Call<NotasEstudianteResponse> call, Response<NotasEstudianteResponse> response) {
                    if (response.isSuccessful() && response.body() != null) {
                        List<NotasEstudianteResponse.Nota> notas = response.body().getData().notas;

                        for (NotasEstudianteResponse.Nota nota : notas) {
                            String claveMateria = nota.materia.area_materia + " (" + nota.materia.sigla_materia + ")";

                            Map<String, Integer> mapa = new HashMap<>();
                            for (NotasEstudianteResponse.Dimension d : nota.dimensiones) {
                                mapa.put(d.nombre_dimension.toLowerCase(), d.valor_obtenido);
                            }

                            String[] claves = {
                                    "ser_docente", "saber_docente", "hacer_docente", "decidir_docente",
                                    "ser_autoeval", "decidir_autoeval"
                            };

                            int[] valores = new int[6];
                            for (int i = 0; i < claves.length; i++) {
                                valores[i] = mapa.containsKey(claves[i]) ? mapa.get(claves[i]) : 0;
                            }

                            double promedioCalc =
                                    valores[0] * 0.05 +
                                            valores[1] * 0.45 +
                                            valores[2] * 0.40 +
                                            valores[3] * 0.05 +
                                            valores[4] * 0.05 +
                                            valores[5] * 0.05;

                            int promedio = (int) Math.round(Math.min(promedioCalc, 100));

                            if (!notasPorMateria.containsKey(claveMateria)) {
                                notasPorMateria.put(claveMateria, new HashMap<>());
                            }

                            notasPorMateria.get(claveMateria).put(trimestre, promedio);
                        }

                        if (trimestre == 3) {
                            llenarTabla(notasPorMateria);
                        }

                    } else {
                        Toast.makeText(BoletaEstudianteActivity.this, "Error al cargar trimestre " + trimestre, Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onFailure(Call<NotasEstudianteResponse> call, Throwable t) {
                    Toast.makeText(BoletaEstudianteActivity.this, "Sin conexión al trimestre " + trimestre, Toast.LENGTH_SHORT).show();
                }
            });
        }
    }
    private Map<String, Map<Integer, Integer>> transformarNotas(Map<Integer, List<NotasEstudianteResponse.Nota>> cache) {
        Map<String, Map<Integer, Integer>> resultado = new HashMap<>();

        for (Map.Entry<Integer, List<NotasEstudianteResponse.Nota>> entry : cache.entrySet()) {
            int trimestre = entry.getKey();
            List<NotasEstudianteResponse.Nota> listaNotas = entry.getValue();

            for (NotasEstudianteResponse.Nota nota : listaNotas) {
                String claveMateria = nota.materia.area_materia + " (" + nota.materia.sigla_materia + ")";

                Map<String, Integer> mapa = new HashMap<>();
                for (NotasEstudianteResponse.Dimension d : nota.dimensiones) {
                    mapa.put(d.nombre_dimension.toLowerCase(), d.valor_obtenido);
                }

                String[] claves = {
                        "ser_docente", "saber_docente", "hacer_docente", "decidir_docente",
                        "ser_autoeval", "decidir_autoeval"
                };

                int[] valores = new int[6];
                for (int i = 0; i < claves.length; i++) {
                    valores[i] = mapa.containsKey(claves[i]) ? mapa.get(claves[i]) : 0;
                }

                double promedioCalc =
                        valores[0] * 0.05 +
                                valores[1] * 0.45 +
                                valores[2] * 0.40 +
                                valores[3] * 0.05 +
                                valores[4] * 0.05 +
                                valores[5] * 0.05;

                int promedio = (int) Math.round(Math.min(promedioCalc, 100));

                if (!resultado.containsKey(claveMateria)) {
                    resultado.put(claveMateria, new HashMap<>());
                }
                resultado.get(claveMateria).put(trimestre, promedio);
            }
        }

        return resultado;
    }


    private void llenarTabla(Map<String, Map<Integer, Integer>> datos) {
        for (String materia : datos.keySet()) {
            TableRow fila = new TableRow(this);
            fila.addView(crearCelda(materia));

            int suma = 0;
            for (int t = 1; t <= 3; t++) {
                int valor = datos.get(materia).containsKey(t) ? datos.get(materia).get(t) : 0;
                fila.addView(crearCelda(String.valueOf(valor)));
                suma += valor;
            }

            int promedioFinal = Math.round(suma / 3f);

            TextView celdaProm = crearCelda(String.valueOf(promedioFinal));
            celdaProm.setTypeface(null, android.graphics.Typeface.BOLD);
            celdaProm.setTextColor(getResources().getColor(android.R.color.holo_blue_dark));
            fila.addView(celdaProm);

            tablaResumen.addView(fila);
        }
    }

    private TextView crearCelda(String texto) {
        TextView tv = new TextView(this);
        tv.setText(texto);
        tv.setPadding(24, 16, 24, 16);
        tv.setGravity(Gravity.CENTER);
        return tv;
    }
}
