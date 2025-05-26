package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.graphics.Typeface;
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
import com.jsq.sistemaacademicojct.model.NotaHijoResponse;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class NotasDetalleHijoActivity extends AppCompatActivity {

    TableLayout tablaNotas;
    Spinner spinnerTrimestre;
    Button btnVerBoletaFinal;
    int idEstudiante = -1;
    int trimestreSeleccionado = 1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_notas_detalle_hijo);

        tablaNotas = findViewById(R.id.tablaNotas);
        spinnerTrimestre = findViewById(R.id.spinnerTrimestre);
        btnVerBoletaFinal = findViewById(R.id.btnVerBoletaFinal);

        idEstudiante = getIntent().getIntExtra("id_estudiante", -1);
        if (idEstudiante == -1) {
            Toast.makeText(this, "No se recibió ID del hijo", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        configurarSpinner();
        btnVerBoletaFinal.setOnClickListener(v -> {
            Intent intent = new Intent(this, BoletaHijoActivity.class);
            intent.putExtra("id_estudiante", idEstudiante);
            startActivity(intent);
        });
    }

    private void configurarSpinner() {
        String[] opciones = {"Trimestre 1", "Trimestre 2", "Trimestre 3"};
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, opciones);
        spinnerTrimestre.setAdapter(adapter);

        spinnerTrimestre.setSelection(0);
        spinnerTrimestre.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int pos, long id) {
                trimestreSeleccionado = pos + 1;
                cargarNotasTrimestre();
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
    }

    private void cargarNotasTrimestre() {
        tablaNotas.removeViews(2, Math.max(0, tablaNotas.getChildCount() - 2)); // deja cabecera y subcabecera

        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getNotasHijo(idEstudiante, "trimestre", trimestreSeleccionado).enqueue(new Callback<NotaHijoResponse>() {
            @Override
            public void onResponse(Call<NotaHijoResponse> call, Response<NotaHijoResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<NotaHijoResponse.Nota> notas = response.body().data.notas;
                    for (NotaHijoResponse.Nota nota : notas) {
                        agregarFila(nota);
                    }
                } else {
                    Toast.makeText(NotasDetalleHijoActivity.this, "No hay notas en este trimestre", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<NotaHijoResponse> call, Throwable t) {
                Toast.makeText(NotasDetalleHijoActivity.this, "Error al cargar notas", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void agregarFila(NotaHijoResponse.Nota nota) {
        TableRow fila = new TableRow(this);

        Map<String, Integer> valores = new HashMap<>();
        for (NotaHijoResponse.Dimension d : nota.dimensiones) {
            valores.put(d.nombre.toLowerCase(), d.valor);
        }

        int ser = valores.getOrDefault("ser_docente", 0);
        int saber = valores.getOrDefault("saber_docente", 0);
        int hacer = valores.getOrDefault("hacer_docente", 0);
        int decidir = valores.getOrDefault("decidir_docente", 0);
        int autoSer = valores.getOrDefault("ser_autoeval", 0);
        int autoDecidir = valores.getOrDefault("decidir_autoeval", 0);

        double promedioCalc = ser * 0.05 + saber * 0.45 + hacer * 0.40 +
                decidir * 0.05 + autoSer * 0.05 + autoDecidir * 0.05;
        int promedio = (int) Math.round(Math.min(promedioCalc, 100));

        String nombreMateria = nota.materia.area + " (" + nota.materia.sigla + ")";

        fila.addView(crearCelda(nombreMateria));
        fila.addView(crearCelda(String.valueOf(ser)));
        fila.addView(crearCelda(String.valueOf(saber)));
        fila.addView(crearCelda(String.valueOf(hacer)));
        fila.addView(crearCelda(String.valueOf(decidir)));
        fila.addView(crearCelda(String.valueOf(autoSer)));
        fila.addView(crearCelda(String.valueOf(autoDecidir)));

        TextView celdaProm = crearCelda(String.valueOf(promedio));
        celdaProm.setTypeface(null, Typeface.BOLD);
        celdaProm.setTextColor(getResources().getColor(android.R.color.holo_blue_dark));
        fila.addView(celdaProm);

        tablaNotas.addView(fila);
    }

    private TextView crearCelda(String texto) {
        TextView tv = new TextView(this);
        tv.setText(texto);
        tv.setPadding(16, 10, 16, 10);
        tv.setGravity(Gravity.CENTER);
        return tv;
    }
}
