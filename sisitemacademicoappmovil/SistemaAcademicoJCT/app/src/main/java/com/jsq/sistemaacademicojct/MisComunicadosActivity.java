package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.Gravity;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.NotificacionUsuarioResponse;

import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class MisComunicadosActivity extends AppCompatActivity {

    LinearLayout contenedorComunicados;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_mis_comunicados);
        getSupportActionBar().hide();

        contenedorComunicados = findViewById(R.id.contenedorComunicados);
        cargarComunicados();
    }

    private void cargarComunicados() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getMisNotificaciones().enqueue(new Callback<NotificacionUsuarioResponse>() {
            @Override
            public void onResponse(Call<NotificacionUsuarioResponse> call, Response<NotificacionUsuarioResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<NotificacionUsuarioResponse.Item> lista = response.body().data;

                    for (NotificacionUsuarioResponse.Item item : lista) {
                        View card = crearCard(item);
                        contenedorComunicados.addView(card);
                    }

                    if (lista.isEmpty()) {
                        Toast.makeText(MisComunicadosActivity.this, "No hay comunicados asignados.", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(MisComunicadosActivity.this, "Error al cargar comunicados", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<NotificacionUsuarioResponse> call, Throwable t) {
                Toast.makeText(MisComunicadosActivity.this, "Sin conexión al servidor", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private View crearCard(NotificacionUsuarioResponse.Item item) {
        TextView tv = new TextView(this);
        NotificacionUsuarioResponse.Notificacion notif = item.notificacion;

        tv.setText("📌 " + notif.titulo_notificacion +
                "\n🗓 " + notif.fecha_notificacion +
                "\n📩 " + notif.mensaje_notificacion);
        tv.setTextSize(16);
        tv.setPadding(24, 24, 24, 24);
        tv.setBackgroundResource(android.R.drawable.dialog_holo_light_frame);
        tv.setTextColor(getResources().getColor(android.R.color.black));

        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        params.setMargins(0, 16, 0, 0);
        tv.setLayoutParams(params);
        tv.setGravity(Gravity.START);

        tv.setOnClickListener(v -> {
            UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
            service.marcarNotificacionLeida(item.notificaciones_id_notificacion).enqueue(new Callback<Void>() {
                @Override
                public void onResponse(Call<Void> call, Response<Void> response) {
                    if (response.isSuccessful()) {
                        Intent intent = new Intent(MisComunicadosActivity.this, DetalleComunicadoActivity.class);
                        intent.putExtra("titulo", notif.titulo_notificacion);
                        intent.putExtra("mensaje", notif.mensaje_notificacion);
                        intent.putExtra("fecha", notif.fecha_notificacion);
                        startActivity(intent);
                    } else {
                        Toast.makeText(MisComunicadosActivity.this, "No se pudo marcar como leído", Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onFailure(Call<Void> call, Throwable t) {
                    Toast.makeText(MisComunicadosActivity.this, "Error al conectar con el servidor", Toast.LENGTH_SHORT).show();
                }
            });
        });

        return tv;
    }
}
