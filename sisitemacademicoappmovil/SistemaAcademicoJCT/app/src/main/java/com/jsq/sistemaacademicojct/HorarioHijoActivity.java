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

public class HorarioHijoActivity extends AppCompatActivity {

    private TableLayout tablaHorario;
    private int idEstudiante;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_horario_estudiante);

        tablaHorario = findViewById(R.id.tablaHorario);
        idEstudiante = getIntent().getIntExtra("id_estudiante", -1);

        if (idEstudiante != -1) {
            cargarHorario();
        } else {
            Toast.makeText(this, "ID del hijo no disponible", Toast.LENGTH_SHORT).show();
            finish();
        }
    }

    private void cargarHorario() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getHorarioHijo(idEstudiante).enqueue(new Callback<HorarioEstudianteWrapper>() {
            @Override
            public void onResponse(Call<HorarioEstudianteWrapper> call, Response<HorarioEstudianteWrapper> response) {
                if (response.isSuccessful() && response.body() != null && response.body().getData() != null) {
                    llenarTablaHorario(response.body().getData());
                } else {
                    Toast.makeText(HorarioHijoActivity.this, "Error al obtener el horario", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<HorarioEstudianteWrapper> call, Throwable t) {
                Toast.makeText(HorarioHijoActivity.this, "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void llenarTablaHorario(List<HorarioEstudianteResponse> lista) {
        tablaHorario.removeAllViews();

        String[] dias = {"Lunes", "Martes", "Miércoles", "Jueves", "Viernes"};

        // Header
        TableRow header = new TableRow(this);
        header.setBackgroundColor(Color.parseColor("#BBDEFB"));
        header.addView(crearCelda("Hora", true));
        for (String dia : dias) {
            header.addView(crearCelda(dia, true));
        }
        tablaHorario.addView(header);

        TreeMap<String, Map<String, String>> mapa = new TreeMap<>();
        for (HorarioEstudianteResponse h : lista) {
            String hora = h.hora_inicio + " - " + h.hora_fin;
            String dia = h.dia;
            String materia = h.materia.area_materia + " (" + h.materia.sigla_materia + ")";
            mapa.computeIfAbsent(hora, k -> new HashMap<>()).put(dia, materia);
        }

        for (String hora : mapa.keySet()) {
            TableRow fila = new TableRow(this);
            fila.addView(crearCelda(hora, true));
            for (String dia : dias) {
                String contenido = mapa.get(hora).getOrDefault(dia, "");
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
        tv.setBackgroundResource(R.drawable.celda_borde);
        tv.setTextColor(Color.BLACK);
        tv.setTextSize(13);
        if (esHora) {
            tv.setTypeface(null, Typeface.BOLD);
            tv.setBackgroundColor(Color.parseColor("#E3F2FD"));
        }
        return tv;
    }
}
