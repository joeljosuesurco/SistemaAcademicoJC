package com.jsq.sistemaacademicojct;

import android.graphics.Typeface;
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
import com.jsq.sistemaacademicojct.model.NotaHijoResponse;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class BoletaHijoActivity extends AppCompatActivity {

    TableLayout tablaResumen;
    Button btnActualizarBoleta;
    int idEstudiante;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_boleta_hijo);

        tablaResumen = findViewById(R.id.tablaResumen);
        btnActualizarBoleta = findViewById(R.id.btnActualizarBoleta);

        idEstudiante = getIntent().getIntExtra("id_estudiante", -1);
        if (idEstudiante == -1) {
            Toast.makeText(this, "No se recibió ID del estudiante", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        btnActualizarBoleta.setOnClickListener(v -> cargarBoleta());
        cargarBoleta();
    }

    private void cargarBoleta() {
        tablaResumen.removeViews(1, Math.max(0, tablaResumen.getChildCount() - 1)); // limpia filas anteriores

        Map<String, Map<Integer, Integer>> notasPorMateria = new HashMap<>();

        for (int t = 1; t <= 3; t++) {
            final int trimestre = t;
            UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
            service.getNotasHijo(idEstudiante, "trimestre", trimestre).enqueue(new Callback<NotaHijoResponse>() {
                @Override
                public void onResponse(Call<NotaHijoResponse> call, Response<NotaHijoResponse> response) {
                    if (response.isSuccessful() && response.body() != null) {
                        List<NotaHijoResponse.Nota> notas = response.body().data.notas;

                        for (NotaHijoResponse.Nota nota : notas) {
                            String claveMateria = nota.materia.area + " (" + nota.materia.sigla + ")";

                            Map<String, Integer> mapa = new HashMap<>();
                            for (NotaHijoResponse.Dimension d : nota.dimensiones) {
                                mapa.put(d.nombre.toLowerCase(), d.valor);
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
                        Toast.makeText(BoletaHijoActivity.this, "Error al cargar trimestre " + trimestre, Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onFailure(Call<NotaHijoResponse> call, Throwable t) {
                    Toast.makeText(BoletaHijoActivity.this, "Sin conexión al trimestre " + trimestre, Toast.LENGTH_SHORT).show();
                }
            });
        }
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
            celdaProm.setTypeface(null, Typeface.BOLD);
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
