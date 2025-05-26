package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.NotificacionUsuarioResponse;
import com.jsq.sistemaacademicojct.model.PerfilResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class MainPadreActivity extends AppCompatActivity {

    TextView txtBienvenida, txtCurso, badgeTextView;
    Button btnHijos, btnNotas, btnSeguimiento, btnComunicados, btnPerfil, btnCerrarSesion;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO);
        super.onCreate(savedInstanceState);
        getSupportActionBar().hide();
        setContentView(R.layout.activity_main_padre);

        txtBienvenida = findViewById(R.id.txtBienvenida);

        badgeTextView = findViewById(R.id.badgeTextView);
        btnHijos = findViewById(R.id.btnHijos);
        btnNotas = findViewById(R.id.btnNotas);
        btnSeguimiento = findViewById(R.id.btnSeguimiento);
        btnComunicados = findViewById(R.id.btnComunicados);
        btnPerfil = findViewById(R.id.btnPerfil);
        btnCerrarSesion = findViewById(R.id.btnCerrarSesion);





        // ✅ ESTA LÍNEA FALTABA:
        cargarNombrePadre();

        btnHijos.setOnClickListener(v -> startActivity(new Intent(this, HijosPadreActivity.class)));
        btnNotas.setOnClickListener(v -> startActivity(new Intent(this, SeleccionarHijoParaNotasActivity.class)));
        btnComunicados.setOnClickListener(v -> {
            startActivity(new Intent(this, MisComunicadosActivity.class));
        });
        btnPerfil.setOnClickListener(v -> startActivity(new Intent(this, PerfilPadreActivity.class)));


        btnSeguimiento.setOnClickListener(v -> {
            Intent intent = new Intent(this, SeleccionarHijoParaSeguimientoActivity.class);
            startActivity(intent);
        });

        btnCerrarSesion.setOnClickListener(v -> {
            SharedPreferences.Editor editor = getSharedPreferences("datos_usuario", MODE_PRIVATE).edit();
            editor.clear();
            editor.apply();
            Intent intent = new Intent(MainPadreActivity.this, LoginActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
        });

        verificarNotificacionesNoLeidas();
    }


    @Override
    protected void onResume() {
        super.onResume();
        verificarNotificacionesNoLeidas();
    }

    private void verificarNotificacionesNoLeidas() {
        SharedPreferences prefs = getSharedPreferences("datos_usuario", MODE_PRIVATE);
        if (!prefs.contains("userId")) return;

        int userId = prefs.getInt("userId", -1);
        if (userId == -1) return;

        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getNotificacionesNoLeidas(userId).enqueue(new Callback<NotificacionUsuarioResponse>() {
            @Override
            public void onResponse(Call<NotificacionUsuarioResponse> call, Response<NotificacionUsuarioResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    int cantidad = response.body().data.size();
                    if (cantidad > 0) {
                        badgeTextView.setText(String.valueOf(cantidad));
                        badgeTextView.setVisibility(View.VISIBLE);
                    } else {
                        badgeTextView.setVisibility(View.GONE);
                    }
                }
            }

            @Override
            public void onFailure(Call<NotificacionUsuarioResponse> call, Throwable t) {
                badgeTextView.setVisibility(View.GONE);
            }
        });
    }
    private void cargarNombrePadre() {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        service.getPerfil().enqueue(new Callback<PerfilResponse>() {
            @Override
            public void onResponse(Call<PerfilResponse> call, Response<PerfilResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    PerfilResponse.PerfilData data = response.body().getData();
                    PerfilResponse.Persona persona = data.getPersonaRol().getPersona();

                    String nombreCompleto = persona.getNombres_persona() + " " +
                            persona.getApellidos_pat() + " " +
                            persona.getApellidos_mat();

                    txtBienvenida.setText("Bienvenido, " + nombreCompleto);
                } else {
                    txtBienvenida.setText("Bienvenido, Padre de Familia");
                }
            }

            @Override
            public void onFailure(Call<PerfilResponse> call, Throwable t) {
                txtBienvenida.setText("Bienvenido, Padre de Familia");
            }
        });
    }


}
