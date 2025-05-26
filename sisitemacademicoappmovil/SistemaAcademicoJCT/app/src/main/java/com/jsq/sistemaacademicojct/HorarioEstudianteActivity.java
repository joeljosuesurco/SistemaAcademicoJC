package com.jsq.sistemaacademicojct;

import android.graphics.Color;
import android.graphics.Typeface;
import android.os.Bundle;
import android.view.Gravity;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.HorarioEstudianteResponse;
import com.jsq.sistemaacademicojct.model.HorarioEstudianteWrapper;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.TreeMap;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class HorarioEstudianteActivity extends AppCompatActivity {

    private TableLayout tablaHorario;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_horario_estudiante);
        getSupportActionBar().hide();

        tablaHorario = findViewById(R.id.tablaHorario);

        cargarHorario();
    }

    private void cargarHorario() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getHorarioEstudiante().enqueue(new Callback<HorarioEstudianteWrapper>() {
            @Override
                        public void onResponse(Call<HorarioEstudianteWrapper> call, Response<HorarioEstudianteWrapper> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<HorarioEstudianteResponse> lista = response.body().getData();
                    if (lista != null) {
                        llenarTablaHorario(lista);
                    } else {
                        Toast.makeText(HorarioEstudianteActivity.this, "Lista vacía", Toast.LENGTH_SHORT).show();
                        android.util.Log.e("API_DEBUG", "Lista data es null.");
                    }
                } else {
                    Toast.makeText(HorarioEstudianteActivity.this, "Error al obtener datos", Toast.LENGTH_SHORT).show();
                    android.util.Log.e("API_ERROR", "Código: " + response.code());

                    try {
                        String errorBody = response.errorBody() != null ? response.errorBody().string() : "Sin cuerpo";
                        android.util.Log.e("API_ERROR", "Body: " + errorBody);
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                }
            }


            @Override
            public void onFailure(Call<HorarioEstudianteWrapper> call, Throwable t) {
                Toast.makeText(HorarioEstudianteActivity.this, "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        });
    }


    private void llenarTablaHorario(List<HorarioEstudianteResponse> lista) {
        if (tablaHorario.getChildCount() > 0) {
            tablaHorario.removeViews(0, tablaHorario.getChildCount());
        }

        String[] dias = {"Lunes", "Martes", "Miércoles", "Jueves", "Viernes"};

        // 🟦 Encabezado con días de la semana
        TableRow header = new TableRow(this);
        header.setBackgroundColor(Color.parseColor("#BBDEFB"));

        // Primera columna vacía (para "Hora")
        header.addView(crearCelda("Hora", true));

        // Encabezados: días
        for (String dia : dias) {
            header.addView(crearCelda(dia, true));
        }

        tablaHorario.addView(header);

        // Construcción del horario [hora][día]
        TreeMap<String, Map<String, String>> mapa = new TreeMap<>();

        for (HorarioEstudianteResponse h : lista) {
            String hora = h.hora_inicio + " - " + h.hora_fin;
            String dia = h.dia;
            String materia = h.materia.area_materia + " (" + h.materia.sigla_materia + ")";

            if (!mapa.containsKey(hora)) {
                mapa.put(hora, new HashMap<>());
            }
            mapa.get(hora).put(dia, materia);
        }

        for (String hora : mapa.keySet()) {
            TableRow fila = new TableRow(this);
            fila.setLayoutParams(new TableRow.LayoutParams(
                    TableRow.LayoutParams.MATCH_PARENT,
                    TableRow.LayoutParams.WRAP_CONTENT));

            fila.addView(crearCelda(hora, true));

            for (String dia : dias) {
                String contenido = mapa.get(hora).containsKey(dia) ? mapa.get(hora).get(dia) : "";
                fila.addView(crearCelda(contenido, false));
            }

            tablaHorario.addView(fila);
        }
    }


    private TextView crearCelda(String texto, boolean esHora) {
        TextView tv = new TextView(this);
        tv.setText(texto);
        tv.setPadding(16, 12, 16, 12);
        tv.setGravity(Gravity.CENTER);
        tv.setBackgroundResource(R.drawable.celda_borde); // asegúrate de tener este drawable
        tv.setTextColor(Color.BLACK);
        tv.setTextSize(13);
        if (esHora) {
            tv.setTypeface(null, Typeface.BOLD);
            tv.setBackgroundColor(Color.parseColor("#E3F2FD"));
        }
        return tv;
    }

}
