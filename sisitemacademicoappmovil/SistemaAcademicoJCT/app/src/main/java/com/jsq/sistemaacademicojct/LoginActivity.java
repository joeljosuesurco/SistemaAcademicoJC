package com.jsq.sistemaacademicojct;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;

import com.jsq.sistemaacademicojct.api.RetrofitClient;
import com.jsq.sistemaacademicojct.api.UsuarioService;
import com.jsq.sistemaacademicojct.model.LoginRequest;
import com.jsq.sistemaacademicojct.model.LoginResponse;
import com.jsq.sistemaacademicojct.model.PerfilResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity {

    EditText usernameInput, passwordInput;
    Button loginBtn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        usernameInput = findViewById(R.id.editTextUsername);
        passwordInput = findViewById(R.id.editTextPassword);
        loginBtn = findViewById(R.id.buttonLogin);

        loginBtn.setOnClickListener(v -> {
            String user = usernameInput.getText().toString().trim();
            String pass = passwordInput.getText().toString().trim();

            if (user.isEmpty() || pass.isEmpty()) {
                Toast.makeText(this, "Ingrese usuario y contraseña", Toast.LENGTH_SHORT).show();
                return;
            }

            login(user, pass);
        });
    }

    private void login(String name_user, String password) {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);
        LoginRequest request = new LoginRequest(name_user, password);

        service.login(request).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    String token = response.body().getToken();
                    RetrofitClient.setToken(token);

                    obtenerPerfil(token); // nueva llamada para obtener datos y rol
                } else {
                    Toast.makeText(LoginActivity.this, "Credenciales incorrectas", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                Toast.makeText(LoginActivity.this, "Error de red: " + t.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    private void obtenerPerfil(String token) {
        UsuarioService service = RetrofitClient.getClient(this).create(UsuarioService.class);

        service.getPerfil().enqueue(new Callback<PerfilResponse>() {
            @Override
            public void onResponse(Call<PerfilResponse> call, Response<PerfilResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    PerfilResponse.PerfilData data = response.body().getData();

                    if (data != null && data.getPersonaRol() != null && data.getPersonaRol().getRol() != null) {
                        String rol = data.getPersonaRol().getRol().getNombre().toLowerCase();
                        int userId = data.getId_user();
                        String nombreUsuario = data.getName_user();

                        // 🚫 Bloquear ingreso si es profesor o administrativo
                        if (rol.equals("profesor") || rol.equals("administrativo") || rol.equals("admin")) {
                            new androidx.appcompat.app.AlertDialog.Builder(LoginActivity.this)
                                    .setTitle("Acceso restringido")
                                    .setMessage("Administrativo o Profesor, por favor utilice la aplicación web. Esta app momentaneamente es solo para estudiantes y padres.")
                                    .setCancelable(false)
                                    .setPositiveButton("Cerrar", (dialog, which) -> dialog.dismiss())
                                    .show();
                            return;
                        }

                        // Guardar en preferencias
                        SharedPreferences.Editor editor = getSharedPreferences("datos_usuario", MODE_PRIVATE).edit();
                        editor.putString("token", token);
                        editor.putInt("userId", userId);
                        editor.putString("rol", rol);
                        editor.putString("usuario", nombreUsuario);
                        editor.apply();

                        // Redirigir según rol
                        Intent intent;
                        if (rol.equals("estudiante")) {
                            intent = new Intent(LoginActivity.this, MainEstudianteActivity.class);
                        } else if (rol.equals("padre")) {
                            intent = new Intent(LoginActivity.this, MainPadreActivity.class);
                        } else {
                            Toast.makeText(LoginActivity.this, "Rol no reconocido", Toast.LENGTH_LONG).show();
                            return;
                        }

                        startActivity(intent);
                        finish();
                    } else {
                        Toast.makeText(LoginActivity.this, "Perfil incompleto", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(LoginActivity.this, "Error al obtener perfil", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<PerfilResponse> call, Throwable t) {
                Toast.makeText(LoginActivity.this, "Error de red al obtener perfil", Toast.LENGTH_LONG).show();
            }
        });
    }

}
