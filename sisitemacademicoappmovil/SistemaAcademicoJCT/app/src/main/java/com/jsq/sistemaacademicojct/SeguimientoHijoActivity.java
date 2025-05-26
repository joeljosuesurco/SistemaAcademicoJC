package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.os.Bundle;
import android.view.Gravity;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.SeguimientoEstudianteResponse;

import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class SeguimientoHijoActivity extends AppCompatActivity {

    LinearLayout contenedor;
    int idEstudiante;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        getSupportActionBar().hide();
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_seguimiento_estudiante); // 🟢 Reutiliza layout existente

        contenedor = findViewById(R.id.contenedorSeguimientos);
        idEstudiante = getIntent().getIntExtra("id_estudiante", -1);

        if (idEstudiante != -1) {
            cargarSeguimientos();
        } else {
            Toast.makeText(this, "No se recibió ID del hijo", Toast.LENGTH_SHORT).show();
            finish();
        }
    }

    private void cargarSeguimientos() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getSeguimientosHijo(idEstudiante).enqueue(new Callback<SeguimientoEstudianteResponse>() {
            @Override
            public void onResponse(Call<SeguimientoEstudianteResponse> call, Response<SeguimientoEstudianteResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<SeguimientoEstudianteResponse.Seguimiento> lista = response.body().data;

                    if (lista.isEmpty()) {
                        Toast.makeText(SeguimientoHijoActivity.this, "No hay seguimientos disponibles.", Toast.LENGTH_SHORT).show();
                        return;
                    }

                    for (SeguimientoEstudianteResponse.Seguimiento seg : lista) {
                        contenedor.addView(crearCard(seg));
                    }

                } else {
                    Toast.makeText(SeguimientoHijoActivity.this, "Error al obtener datos", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<SeguimientoEstudianteResponse> call, Throwable t) {
                Toast.makeText(SeguimientoHijoActivity.this, "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private View crearCard(SeguimientoEstudianteResponse.Seguimiento seg) {
        TextView tv = new TextView(this);
        tv.setText("📘 " + seg.materia.area_materia + " (" + seg.materia.sigla_materia + ")\n🗓 " + seg.fecha_reg_seg);
        tv.setTextSize(16);
        tv.setPadding(24, 24, 24, 24);
        tv.setBackgroundResource(android.R.drawable.dialog_holo_light_frame);
        tv.setTextColor(getResources().getColor(android.R.color.black));
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        params.setMargins(0, 16, 0, 0);
        tv.setLayoutParams(params);
        tv.setGravity(Gravity.CENTER_VERTICAL);
        tv.setOnClickListener(v -> {
            Intent intent = new Intent(this, DetalleSeguimientoActivity.class);
            intent.putExtra("materia", seg.materia.area_materia + " (" + seg.materia.sigla_materia + ")");
            intent.putExtra("fecha", seg.fecha_reg_seg);
            intent.putExtra("observacion", seg.observaciones_seguimiento);
            intent.putExtra("asistencia", seg.asistencia);
            intent.putExtra("participacion", seg.participacion);
            intent.putExtra("disciplina", seg.disciplina);
            intent.putExtra("puntualidad", seg.puntualidad);
            intent.putExtra("respeto", seg.respeto);
            intent.putExtra("tolerancia", seg.tolerancia);
            intent.putExtra("estado_animo", seg.estado_animo);
            startActivity(intent);
        });

        return tv;
    }
}
