package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.EstudianteDatosResponse;
import com.jsq.sistemaacademicojct.model.NotificacionUsuarioResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class MainEstudianteActivity extends AppCompatActivity {

    TextView txtBienvenida, txtCurso, badgeTextView;
    Button btnHorario, btnNotas, btnSeguimiento, btnPerfil, btnCerrarSesion, btnComunicados;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        getSupportActionBar().hide();
        setContentView(R.layout.activity_main_estudiante);

        txtBienvenida = findViewById(R.id.txtBienvenida);
        txtCurso = findViewById(R.id.txtCurso);
        badgeTextView = findViewById(R.id.badgeTextView);
        btnHorario = findViewById(R.id.btnHorario);
        btnNotas = findViewById(R.id.btnNotas);
        btnSeguimiento = findViewById(R.id.btnSeguimiento);
        btnComunicados = findViewById(R.id.btnComunicados);
        btnPerfil = findViewById(R.id.btnPerfil);
        btnCerrarSesion = findViewById(R.id.btnCerrarSesion);

        cargarDatosEstudiante();
        verificarNotificacionesNoLeidas();

        btnHorario.setOnClickListener(v -> {
            startActivity(new Intent(MainEstudianteActivity.this, HorarioEstudianteActivity.class));
        });

        btnNotas.setOnClickListener(v -> {
            startActivity(new Intent(MainEstudianteActivity.this, NotasEstudianteActivity.class));
        });

        btnSeguimiento.setOnClickListener(v -> {
            startActivity(new Intent(MainEstudianteActivity.this, SeguimientoEstudianteActivity.class));
        });

        btnPerfil.setOnClickListener(v -> {
            startActivity(new Intent(MainEstudianteActivity.this, PerfilEstudianteActivity.class));
        });

        btnComunicados.setOnClickListener(v -> {
            startActivity(new Intent(MainEstudianteActivity.this, MisComunicadosActivity.class));
        });

        btnCerrarSesion.setOnClickListener(v -> {
            SharedPreferences.Editor editor = getSharedPreferences("datos_usuario", MODE_PRIVATE).edit();
            editor.clear();
            editor.apply();
            Intent intent = new Intent(MainEstudianteActivity.this, LoginActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        verificarNotificacionesNoLeidas(); // Se actualiza al volver
    }

    private void cargarDatosEstudiante() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getDatosEstudiante().enqueue(new Callback<EstudianteDatosResponse>() {
            @Override
            public void onResponse(Call<EstudianteDatosResponse> call, Response<EstudianteDatosResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    EstudianteDatosResponse.Datos datos = response.body().getData();
                    txtBienvenida.setText("Bienvenido, " + datos.getNombreCompleto());
                    txtCurso.setText("Curso actual: " + datos.getCursoNombre());
                } else {
                    txtBienvenida.setText("Bienvenido");
                    txtCurso.setText("Curso: no disponible");
                }
            }

            @Override
            public void onFailure(Call<EstudianteDatosResponse> call, Throwable t) {
                txtBienvenida.setText("Bienvenido");
                txtCurso.setText("Error al cargar curso");
            }
        });
    }

    private void verificarNotificacionesNoLeidas() {
        Log.d("BADGE", "Entró a verificarNotificacionesNoLeidas()");

        SharedPreferences prefs = getSharedPreferences("datos_usuario", MODE_PRIVATE);
        if (!prefs.contains("userId")) return;

        int userId = prefs.getInt("userId", -1);
        Log.d("BADGE", "UserID obtenido: " + userId);
        if (userId == -1) return;

        try {
            UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
            service.getNotificacionesNoLeidas(userId).enqueue(new Callback<NotificacionUsuarioResponse>() {
                @Override
                public void onResponse(Call<NotificacionUsuarioResponse> call, Response<NotificacionUsuarioResponse> response) {
                    Log.d("BADGE", "onResponse() llamado");

                    if (response.isSuccessful()) {
                        if (response.body() != null) {
                            int cantidad = response.body().data.size();
                            Log.d("BADGE", "Cantidad de no leídas: " + cantidad);

                            if (cantidad > 0) {
                                badgeTextView.setText(String.valueOf(cantidad));
                                badgeTextView.setVisibility(View.VISIBLE);
                            } else {
                                badgeTextView.setVisibility(View.GONE);
                            }
                        } else {
                            Log.e("BADGE", "El body es null");
                        }
                    } else {
                        Log.e("BADGE", "No exitoso - Código HTTP: " + response.code());
                        try {
                            Log.e("BADGE", "Error body: " + response.errorBody().string());
                        } catch (Exception e) {
                            Log.e("BADGE", "Excepción al leer errorBody: " + e.getMessage());
                        }
                    }

                }

                @Override
                public void onFailure(Call<NotificacionUsuarioResponse> call, Throwable t) {
                    Log.e("BADGE", "onFailure: " + t.getMessage());
                    badgeTextView.setVisibility(View.GONE);
                }
            });
        } catch (Exception e) {
            Log.e("BADGE", "Excepción en Retrofit: " + e.getMessage());
        }
    }
}
